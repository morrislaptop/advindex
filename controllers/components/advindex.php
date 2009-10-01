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
		$this->sessionKey = 'Advindex.' . $this->modelName;
		$default = array(
			'fields' => $modelName ? $this->_getDefaultFields($this->controller->$modelName) : array(),
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

	function _getDefaultFields($model) {
		$model_fields = array($model->alias => array_keys($model->schema()));
		foreach (array('belongsTo', 'hasOne') as $assoc) {
			foreach ($model->$assoc as $alias => $arr) {
				$model_fields[$alias] = array_keys($model->$alias->schema());
			}
		}
		$flat = array();
		foreach ($model_fields as $model => $fields) {
			foreach ($fields as $field) {
				$flat[] = $model . '.' . $field;
			}
		}
		return $flat;
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
				call_user_func_array(array($this, 'export'), $this->controller->params['pass']);
				return;
			}
			if ( in_array($action, array('save_order', 'admin_save_order')) ) {
				$this->save_order();
				return;
			}
		}
	}

	function index() {

		if ( !empty($this->controller->data) ) {
			$perPage = $this->controller->data[$this->modelName]['perPage'];
			unset($this->controller->data[$this->modelName]['perPage']);
			$this->controller->Session->write($this->sessionKey . '.conditions', $this->controller->data);
			$this->controller->Session->write($this->sessionKey . '.perPage', $perPage);
		}

		// Filtering
		$conditions = $this->_getConditions();
		$this->controller->data = $this->controller->Session->read($this->sessionKey . '.conditions'); // put data to controller so it appears in filter form
		$this->controller->paginate['conditions'] = $conditions;

		// Per Page
		$perPageKey = $this->sessionKey . '.perPage';
		$perPage = $this->controller->Session->read($perPageKey);
		if ( $perPage ) {
			$this->controller->paginate['limit'] = 'All' == $perPage ? PHP_INT_MAX : $perPage;
		}

		// Current Page
		$currPageKey = $this->sessionKey . '.currPage';
		if ( !empty($this->controller->passedArgs['page']) ) {
			$currPage = $this->controller->passedArgs['page'];
			$this->controller->Session->write($currPageKey, $currPage);
		}
		else {
			$currPage = $this->controller->Session->read($currPageKey);
		}
		if ( $currPage ) {
			$this->controller->paginate['page'] = $currPage;
		}

		// Sorting
		$sortKey = $this->sessionKey . '.sort';
		if ( !empty($this->controller->passedArgs['sort']) ) {
			$sort = $this->controller->passedArgs['sort'];
			$this->controller->Session->write($sortKey, $sort);
		}
		else {
			$sort = $this->controller->Session->read($sortKey);
		}
		# controller set below

		// Sort Direction
		$directionKey = $this->sessionKey . '.direction';
		if ( !empty($this->controller->passedArgs['direction']) ) {
			$direction = $this->controller->passedArgs['direction'];
			$this->controller->Session->write($directionKey, $direction);
		}
		else {
			$direction = $this->controller->Session->read($directionKey);
		}
		# controller set below

		// Set order from the sort field and direciton.
		if ( $sort ) {
			$this->controller->paginate['order'] = $sort . ' ' . $direction;
		}
	}

	function export($text = false) {

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

		App::import('Vendor', 'advindex.parseCSV', array('file' => 'parsecsv-0.3.2' . DS . 'parsecsv.lib.php'));
		$csv = new parseCSV();
		$return = $csv->output(!$text, $this->controller->name . '.csv', $rows, $this->settings['fields']);
		if ( $text ) {
			header('Content-type: text/plain');
			echo $return;
		}
		else {
			Configure::write('debug', 0); // get rid of sql log at the end
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

			// get row
			$row2 = array();
			foreach ($row as $field => $val) {
				$alias = $model->alias;
				if ( strpos($field, '.') !== false ) {
					list($alias, $field) = explode('.', $field);
				}
				$row2[$alias][$field] = $val;
			}
			$row = $row2;
			unset($row2);

			// format row
			if ( $hasBeforeCallback ) {
				$modelData = $model->beforeImport($row);
			}
			else {
				$modelData = $this->genericBeforeImport($row);
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
	
	function genericBeforeImport($row) 
	{
		// Trim
		foreach ($row as $model => &$fields) {
			$fields = array_map('trim', $fields);
		}
		unset($model, $fields);
		
		// Foreign keys
		foreach ($row as $model => $fields) {
			$modelObj = ClassRegistry::getObject($model);
			foreach ($fields as $field => $value) {
				$lookIn = array_merge($modelObj->belongsTo, $modelObj->hasOne);
				foreach ($lookIn as $alias => $props) {
					$aliasObj = ClassRegistry::getObject($alias);
					if ( $props['foreignKey'] == $field ) {
						#$row[$model][$field] = $this->findForeignKey($aliasObj, $value);
					}
				}
			}
		}
		
		return $row;
	}
	
	function findForeignKey($modelObj, $value)
	{
		$conditions = array(
			$modelObj->displayField => $value
		);
		$id = $modelObj->field($modelObj->primaryKey, $conditions);
		if ( $id ) {
			return $id;	
		}
		return $value;
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
		$conditions = array();

		if ( $models = $this->controller->Session->read($sessionKey . '.conditions') ) 
		{
			foreach ($models as $modelName => $filter)
			{
				$modelObj = ClassRegistry::getObject($modelName);
				foreach ($filter as $field => $keyword) 
				{
					if ( empty($keyword) && $keyword !== '0' ) {
						continue;
					}

					if ( isset($this->settings['types'][$field]) ) {
						$columnType = $this->settings['types'][$field];
					}
					else {
						$columnType = $modelObj->getColumnType($field);
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
								$from = $modelObj->deconstruct($field, $keyword['from']);
								$to = $modelObj->deconstruct($field, $keyword['to']);
								$to = str_replace('00:00:00', '23:59:59', $to); // more intuitive to put it at the end of the day
								$this->_addRange($conditions, $field, compact('from', 'to'));
							}
							else {
								$keyword = $modelObj->deconstruct($field, $keyword);
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