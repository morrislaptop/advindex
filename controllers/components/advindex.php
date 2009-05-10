<?php
class AdvindexComponent extends Object {
	
	var $modelName;
	var $sessionKey;
	var $controller;
	
	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		// saving the controller reference for later use
		$this->controller =& $controller;
	}

	//called after Controller::beforeFilter()
	function startup(&$controller) {
		
		$this->modelName = reset($controller->modelNames);
		$this->sessionKey = 'advindex.' . $this->modelName;
		
		if ( in_array($controller->params['action'], array('index', 'admin_index')) ) {
			$this->index();
			return;
		}
		if ( in_array($controller->params['action'], array('export', 'admin_export')) ) {
			$this->export();
			return;
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
	
	function export() {
		// get the conditions
		$conditions = $this->_getConditions();
		$modelName = $this->modelName;
		
		$order = null;
		if ( !empty($this->controller->passedArgs['sort']) ) {
			$order = array($this->controller->passedArgs['sort'] => $this->controller->passedArgs['direction']);
		}
		
		$rows = $this->controller->$modelName->find('all', compact('conditions', 'order'));
		foreach ($rows as &$row) {
			$row = array_pop($row);
		}
		$fields = array_keys($rows[0]);
		$fields = array_map(array('Inflector', 'humanize'), $fields);
		
		App::import('Vendor', 'advindex.parseCSV', array('file' => 'parsecsv-0.3.2' . DS . 'parsecsv.lib.php'));
		$csv = new parseCSV();
		$csv->output(true, $this->controller->name . '.csv', $rows, $fields);
		exit;
	}
	
	function _getConditions() {
		$sessionKey = $this->sessionKey;
		$modelName = $this->modelName;
		$conditions = array();
		
		if ( $this->controller->Session->check($sessionKey) ) {
			$filter = $this->controller->Session->read($sessionKey);
			
			foreach ($filter as $field => $keyword) {
				if ( (empty($keyword) && $keyword !== '0') || !$this->controller->$modelName->hasField($field) ) {
					continue;
				}
				
				$columnType = $this->controller->$modelName->getColumnType($field);
				switch ($columnType) 
				{
					case 'boolean':
						$conditions[$field] = $keyword;
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