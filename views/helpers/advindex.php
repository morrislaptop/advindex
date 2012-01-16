<?php
class AdvindexHelper extends AppHelper {

	var $helpers = array('Html', 'Paginator', 'Form', 'Session', 'Text', 'Time');

	/**
	* @var FormHelper
	*/
	var $Form;
	/**
	* @var AdvformHelper
	*/
	var $AdvformHelper;
    var $pluginFolder;

	function create($model) {
		// we have to set the url manually as the id in the form data causes Router errors.
		return $this->Form->create($model, array('url' => array('action' => 'index')));
	}

	function end() {
		return $this->Form->end();
	}

	function search() {
		return $this->Form->submit('Search', array('class' => 'submit tiny'));
	}

	function filter($field, $options = array()) {
        $options = array_merge(array(
            'fromTo' => true
        ),$options);
		$this->setEntity($field);
		$modelName = $this->model();
		$model =& ClassRegistry::getObject($modelName);
		$columnType = $model->getColumnType($this->field());

		// override column type if in the options
		if ( !empty($options['type']) ) {
			$columnType = $options['type'];
			unset($options['type']);
		}
		else if ( 'datetime' == $columnType ) {
			// pretty safe to assume we want to turn date times into dates
			$columnType = 'date';
		}
		else if ( '_id' === substr($field, -strlen('_id')) ) {
			$columnType = 'foreign';
		}

		// dont escape by defualt.
		if ( !isset($options['escape']) ) {
			$options['escape'] = false;
		}

		// qualify model.
		if ( strpos($field, '.') === false ) {
			$field = $modelName . '.' . $field;
		}

		// text types just get a textbox.
		switch ($columnType)
		{
			case 'boolean':
				$selOptions = array(
					'False',
					'True'
				);
				$options = array_merge(array('type' => 'select', 'label' => false, 'div' => false, 'empty' => true, 'options' => $selOptions), $options);
				return $this->Form->input($field, $options);
			break;

			case 'integer':
			case 'float':
                if ( $options['fromTo'] ) {
				    $from = $this->Form->input($field . '.from', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				    $to = $this->Form->input($field . '.to', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				    return $from . $to;
                } else {
                    return $this->Form->input($field . '.from', array_merge(array('label' => false, 'type' => 'text', 'class' => 'range'), $options));
                }
			break;

			case 'date':
			case 'datetime':
			case 'timestamp':
                if ( $options['fromTo'] ) {
				    $from = $this->Form->input($field . '.from', array('label' => 'From', 'class' => 'text date_picker', 'type' => 'text'));
				    $to = $this->Form->input($field . '.to', array('label' => 'To', 'class' => 'text date_picker', 'type' => 'text'));
				    return $from . $to;
                } else {
                    return $this->Form->input($field . '.from', array('label' => false, 'class' => 'text date_picker', 'type' => 'text'));
                }
			break;

			case 'time':
                if ( $options['fromTo'] ) {
				    $options = array_merge(array('empty' => true, 'type' => 'time'), $options);
				    $from = $this->Form->input($field . '.from', $options);
				    $to = $this->Form->input($field . '.to', $options);
				    return $from . $to;
                } else {
                    $options = array_merge(array('label' => false, 'empty' => true, 'type' => 'time'), $options);
                    return $this->Form->input($field . '.from', $options);
                }
			break;

			case 'text':
			case 'string':
			case 'foreign':
			default:
				$options = array_merge(array('label' => false, 'empty' => true), $options);
				return $this->Form->input($field, $options);
			break;
		}


		return $this->Form->input($field, $options);
	}

	function export($label) {
		$url = array(
			'action' => 'export',
		);
		if ( !empty($this->params['named']['sort']) ) {
			$url['sort'] = $this->params['named']['sort'];
			$url['direction'] = $this->params['named']['direction'];
		}

		return $this->Html->link($label, $url);
	}

	function perPage() {
		$opts = array(10, 20, 50, 100, 'All');
		$opts = array_combine($opts, $opts);
		$paging = $this->params['paging'];
		$paging = array_pop($paging);
		$limit = $paging['options']['limit'];
		if ( $limit == PHP_INT_MAX ) {
			$limit = 'All';
		}
		return $this->Form->select('perPage', $opts, $limit, array('onchange' => "this.form.submit();"), false);
	}

	/**
	* Returns the columns for the current model.
	*/
	function cols() {
		$model = $this->model();
		$var = Inflector::pluralize(Inflector::variable($model));
		$view = ClassRegistry::getObject('view');
		$rows = $view->viewVars[$var];
		if ( $rows ) {
			$cols = array_keys($rows[0][$model]);
		}
		else {
			// what do we do, no keys!
			$model = ClassRegistry::getObject($model);
			$cols = array_keys($model->schema());
		}
		return $cols;
	}
    
    /**
    * AdminWorks specific functions
    */
    
    /**
    * Set template variables
    * 
    * @param array $scaffold
    * @param array $structure
    * @param array $scaffoldFields
    * @param string $modelClass
    */
    function setTemplateVariables(&$scaffold, &$structure, &$scaffoldFields, $modelClass){
        $scaffold = Configure::read('scaffold');
        if(!empty($scaffold[$modelClass][$this->params['action']])){
            $structure = $scaffold[$modelClass][$this->params['action']];
            $errors = array();
            foreach($structure as $k => $v){
                if(substr($k,0,strlen('_special_')) == '_special_'){ // Special field
                    
                }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
                    if(!in_array($v,$scaffoldFields) && !in_array($k,$scaffoldFields) && $v != 'actions'){  // Doesn't exist
                        unset($structure[$k]);
                        $errors[] = 'Field "'.$v.'" doesn\'t exist so it was removed';
                    }
                }else{ // Group of fields
                    foreach($structure[$k] as $gk => $gv){
                        if(is_integer($gk) || in_array($gk,$scaffoldFields)){ // Normal field OR Field with options
                            if(!in_array($gv,$scaffoldFields) && !in_array($gk,$scaffoldFields)){  // Doesn't exist
                                unset($structure[$k][$gk]);
                                debug('Field "'.$k.'->'.$gv.'" doesn\'t exist so it was removed');
                            }
                        }else{  // Doesn't exist
                            unset($structure[$k][$gk]);
                            $errors[] = 'Field "'.$k.'->'.$gk.'" doesn\'t exist so it was removed';
                        }
                    }
                }
            }
            if(count($errors) > 0){
                debug(implode('<br/>',$errors).'<br/><br/>These are the fields available for $structure[\''.$modelClass.'\']:<br/>'.print_r($scaffoldFields,true));
            }
            if(!count($structure) > 0){
                $structure = $scaffoldFields;
                $structure[] = 'actions'; // Add actions in so they show up
            }
        }else{
            $structure = $scaffoldFields;
            $structure[] = 'actions'; // Add actions in so they show up
        }
    }
    
    /**
    * Return output for templates
    * 
    * @param array $array
    * @param array $data
    * @param string $field
    */
    function getOutput(&$array, &$data, &$field){
        if(is_array($array) && isset($array['call_user_func'])){
            return call_user_func($array['call_user_func'],$data[$field]);
        }elseif(is_array($array) && !empty($array['switch'])){
            if(isset($array['switch'][$data[$field]])){
                return $array['switch'][$data[$field]];
            }else{
                return $data[$field];
            }
        }elseif(isset($array['format'])){
            switch($array['format']){
                case 'dateTime':
                    return $this->Time->niceShort($data[$field]);
                break;
                case 'json':
                    return '<pre>'.print_r(json_decode($data[$field]),true).'</pre>';
                break;
            }
        }elseif(isset($array['yesno']) && $array['yesno'] === true){
            return ($data[$field] == 0?'No':($data[$field] == 1?'Yes':$data[$field]));
        }elseif(!empty($array['html']) && is_array($array['html'])){
            $output = '';
            foreach($array['html'] as $part){
                if(!empty($data[$part])){
                    $output .= $data[$part];
                }else{
                    $output .= $part;
                }
            }
            return $output;
        }
        return $this->Text->autoLink($data[$field]);
    }
    
    /**
    * Return output for special fields
    * 
    * @param array $array
    * @param array $data
    */
    function getOutputSpecial(&$array, &$data){
        if(!empty($array['image'])){
            $path = '';
            foreach($array['image'] as $part){
                if(!empty($data[$part])){
                    $path .= $data[$part];
                }else{
                    $path .= $part;
                }
            }
            return '<img src="'.$path.'" />';
        }elseif(!empty($array['html'])){
            $output = '';
            foreach($array['html'] as $part){
                if(!empty($data[$part])){
                    $output .= $data[$part];
                }else{
                    $output .= $part;
                }
            }
            return $output;
        }
    }
    
}
?>