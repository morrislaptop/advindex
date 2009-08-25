<?php
class AdvindexComponent extends Object {

	var $modelName;
	var $sessionKey;
	var $controller;
	var $settings;

	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		// saving the controller reference for later use
		$this->controller =& $controller;
		$this->_initSettings($settings);
	}

	function _initSettings($settings) {
		$this->modelName = $modelName = reset($this->controller->modelNames);
		$this->sessionKey = 'advindex.' . $this->modelName;
		$default = array(
			'fields' => $modelName ? array_keys($this->controller->$modelName->schema()) : array(),
			'types' => array(),
			'update_if_fields' => $modelName ? array($this->controller->$modelName->primaryKey) : array()
		);
		$settings = array_merge($default, $settings);

		// If there is no alias for the export / import column, put the column name as the alias.
		$newFields = array();
		foreach ($settings['fields'] as $key => $val) {
			if ( is_numeric($key) ) {
				$newFields[$val] = $val;
			}
			else {
				$newFields[$key] = $val;
			}
		}
		$settings['fields'] = $newFields;

		$this->settings = $settings;
	}

	//called after Controller::beforeFilter()
	function startup(&$controller) {
		$action = $controller->params['action'];

		// Methods we assist with here - we dont need to take over
		if ( in_array($action, array('index', 'admin_index')) ) {
			$this->index();
			return;
		}
		if ( in_array($action, array('toggle', 'admin_toggle')) ) {
			$this->toggle();
			return;
		}

		// Methods we take over here - check the controller doesnt have their own method for it.
		if ( !method_exists($controller, $controller->params['action']) )
		{
			if ( in_array($action, array('import', 'admin_import')) ) {
				$this->import();
				return;
			}
			if ( in_array($action, array('export', 'admin_export')) ) {
				$this->export();
				return;
			}
			if ( in_array($action, array('save_order', 'admin_save_order')) ) {
				$this->save_order();
				return;
			}
		}
	}

	function index() {

		if ( isset($this->controller->data[$this->modelName]) ) {
			$this->controller->Session->write($this->sessionKey, $this->controller->data[$this->modelName]);
		}

		// Filtering
		$conditions = $this->_getConditions();
		$this->controller->data = array($this->modelName => $this->controller->Session->read($this->sessionKey)); // put data to controller so it appears in filter form
		$this->controller->paginate['conditions'] = $conditions;

		// Per Page
		 $perPageKey = $this->sessionKey . '.perPage';
		 $perPage = $this->controller->Session->read($perPageKey);
		 if ( $perPage ) {
			 $this->controller->paginate['limit'] = 'All' == $perPage ? PHP_INT_MAX : $perPage;
		 }
	}

	function export()
	{
		$text = false;
		if ( $this->controller->params['pass'] ) {
			$text = $this->controller->params['pass'][0];
		}

		// get the conditions
		$conditions = $this->_getConditions();
		$modelName = $this->modelName;
		$callbacks = false;
		$fields = array_keys($this->settings['fields']);

		$order = null;
		if ( !empty($this->controller->passedArgs['sort']) ) {
			$order = array($this->controller->passedArgs['sort'] => $this->controller->passedArgs['direction']);
		}

		$rows = $this->controller->$modelName->find('all', compact('conditions', 'order', 'callbacks', 'fields'));
		foreach ($rows as &$row) {
			$newRow = array();
			foreach ($row as $model => $values) {
				foreach ($values as $field => $value) {
					$newRow[$model . '.' . $field] = $value;
				}
			}
			$row = $newRow;
		}

		// if no fields specced and no results, we need to get headers from model class.
		if ( !$headers ) {
			$headers = array_keys($this->controller->$modelName->schema());
		}

		App::import('Vendor', 'advindex.parseCSV', array('file' => 'parsecsv-0.3.2' . DS . 'parsecsv.lib.php'));
		$csv = new parseCSV();
		$return = $csv->output(!$text, $this->controller->name . '.csv', $rows, $this->settings['fields']);
		Configure::write('debug', 0); // get rid of sql log at the end
		if ( $text ) {
			header('Content-type: text/plain');
			echo $return;
		}
		exit;
	}

	/**
	* @todo Implement truncate
	*
	*/
	function import()
	{
		set_time_limit(0);
		$file = $this->controller->data[$this->modelName]['csv']['tmp_name'];
		$truncate = $this->controller->data[$this->modelName]['truncate'];
		$model = $this->controller->{$this->modelName};

		// truncate the table.
		if ( $truncate ) {
			#$model->contain();
			$model->deleteAll(array(1 => 1));
		}

		// parseCSV does lots of magic to fill $csv->data
		App::import('Vendor', 'advindex.parseCSV', array('file' => 'parsecsv-0.3.2' . DS . 'parsecsv.lib.php'));
		$csv = new parseCSV();
		$csv->parse($file);

		// Process data.
		$fields = array_flip($this->settings['fields']);
		$errors = array();
		$created = $updated = 0;

		// Callbacks.
		$hasBeforeCallback = method_exists($model, 'beforeImport');
		$hasAfterCallback = method_exists($model, 'afterImport');

		foreach ($csv->data as $line => $row)
		{
			// start again
			$model->create();

			// format row
			$modelData = array();
			if ( $hasBeforeCallback ) {
				$modelData = $model->beforeImport($row);
			}
			else {
				foreach ($row as $field => $val) {
					$modelData[$fields[$field]] = trim($val);
				}
			}

			// try and find an existing row.
			$update = false;
			$conditions = array();
			$contain = array();
			foreach ($this->settings['update_if_fields'] as $field) {
				if ( !empty($modelData[$field]) ) {
					$conditions[$field] = $modelData[$field];
				}
			}
			if ( $conditions && $record = $model->find('first', compact('conditions', 'contain')) ) {
				$modelData[$model->primaryKey] = $record[$this->modelName][$model->primaryKey];
				$update = true;
			}

			if ( $saved = $model->saveAll($modelData) ) {
				($update ? $updated++ : $created++);

				if ( $hasAfterCallback ) {
					$model->afterImport($modelData);
				}
			}
			else {
				$errors[$line] = $model->validationErrors;
			}
		}

		$this->controller->Session->write('Advindex.' . $this->modelName . '.import', compact('created', 'updated', 'errors'));
		$this->controller->redirect(array('action' => 'index'));
	}

	function toggle()
	{
		// Vars
		$model = $this->controller->{$this->modelName};
		$id = $this->controller->params['pass'][0];
		$field = $this->controller->params['pass'][1];

		// Toogle change.
		$dbField = $model->escapeField($field);
		$model->updateAll(array($dbField => 'NOT ' . $dbField), array($model->escapeField() => $id));

		// Get new var
		$data = $model->read($field, $id);
		$value = $data[$model->alias][$field];

		// Set and render
		$this->controller->set(compact('value', 'field', 'id'));
		$this->controller->plugin = 'advindex'; // needed to set the correct paths to elements
		$this->controller->layout = 'ajax';
		Configure::write('debug', 1); // turn off db
		echo $this->controller->render('/elements/toggler');
		exit;
	}

	function save_order()
	{
		$id = $this->controller->params['pass'][0];
		$modelClass = $this->controller->modelClass;
		$data = $this->controller->data;
    	if (!$id || !is_numeric($id) || !isset($data[$modelClass]['order']) || !is_numeric($data[$modelClass]['order'])) {
      		echo 'Invalid Format';
      		debug($id);
      		exit;
    	}
    	$this->controller->$modelClass->id = $id;
    	die(json_encode($this->controller->$modelClass->save($data, true, array('order'))));
	}

	function _getConditions() {
		$sessionKey = $this->sessionKey;
		$modelName = $this->modelName;
		$conditions = array();

		if ( $filter = $this->controller->Session->read($sessionKey) ) {

			foreach ($filter as $field => $keyword) {
				if ( (empty($keyword) && $keyword !== '0') || !$this->controller->$modelName->hasField($field) ) {
					continue;
				}

				if ( isset($this->settings['types'][$field]) ) {
					$columnType = $this->settings['types'][$field];
				}
				else {
					$columnType = $this->controller->$modelName->getColumnType($field);
				}

				$field = $modelName . '.' . $field;

				switch ($columnType)
				{
					case 'boolean':
						if ( $keyword ) {
							$conditions[] = array(
								'and' => array(
									array($field . ' NOT' => 0),
									array($field . ' NOT' => null)
								)
							);
						}
						else {
							$conditions[] = array(
								'or' => array(
									array($field => 0),
									array($field => null)
								)
							);
						}
					break;

					case 'integer':
						if ( is_array($keyword) ) {
							$this->_addRange($conditions, $field, $keyword);
						}
						else {
							$conditions[$field] = $keyword;
						}
					break;

					case 'datetime':
					case 'date':
					case 'time':
					case 'timestamp':
						if ( isset($keyword['from']) || isset($keyword['to']) ) {
							if ( !empty($keyword['from']['date']) ) {
								$time = strtotime($keyword['from']['date']);
								$keyword['from']['year'] = date('Y', $time);
								$keyword['from']['month'] = date('m', $time);
								$keyword['from']['day'] = date('d', $time);
							}
							if ( !empty($keyword['to']['date']) ) {
								$time = strtotime($keyword['to']['date']);
								$keyword['to']['year'] = date('Y', $time);
								$keyword['to']['month'] = date('m', $time);
								$keyword['to']['day'] = date('d', $time);
							}
							$from = $this->controller->$modelName->deconstruct($field, $keyword['from']);
							$to = $this->controller->$modelName->deconstruct($field, $keyword['to']);
							$to = str_replace('00:00:00', '23:59:59', $to); // more intuitive to put it at the end of the day
							$this->_addRange($conditions, $field, compact('from', 'to'));
						}
						else {
							$keyword = $this->controller->$modelName->deconstruct($field, $keyword);
							// empty dates get through here because of the array so we do a check if the deconstruct
							// turned it into something useful
							if ( $keyword ) {
								$conditions[$field] = $keyword;
							}
						}
					break;

					case 'text':
					case 'string':
					default:
						$conditions[$field . ' LIKE'] = '%' . $keyword . '%';
					break;
				}
			}

		}
		return $conditions;
	}

	function _addRange(&$conditions, $field, $fromAndTo)
	{
		$hasMin = !empty($fromAndTo['from']) && $fromAndTo['from'] !== '0';
		$hasMax = !empty($fromAndTo['to']) && $fromAndTo['to'] !== '0';

		// Case 1, min and no max
		if ( $hasMin && !$hasMax ) {
			$conditions[$field . ' >='] = $fromAndTo['from'];
		}
		// Case 2, max and no min
		else if ( !$hasMin && $hasMax ) {
			$conditions[$field . ' <='] = $fromAndTo['to'];
		}
		// Case 3, max and min
		else if ( $hasMin && $hasMax ) {
			$conditions[$field . ' BETWEEN ? AND ?'] = array($fromAndTo['from'], $fromAndTo['to']);
		}
		// Case 4, no min and no max (dont add anything)
	}

	//called after Controller::beforeRender()
	function beforeRender(&$controller) {
	}

	//called after Controller::render()
	function shutdown(&$controller) {
	}

	//called before Controller::redirect()
	function beforeRedirect(&$controller, $url, $status=null, $exit=true) {
	}

	function redirectSomewhere($value) {
		// utilizing a controller method
		$this->controller->redirect($value);
	}
}
?>