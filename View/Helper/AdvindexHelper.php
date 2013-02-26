<?php



class AdvindexHelper extends FormHelper {

	var $helpers = array('Html', 'Paginator', 'Form', 'Session', 'Text', 'Time');


	/**
	* @var AdvformHelper
	*/
	var $AdvformHelper;
    var $pluginFolder;

	function create($model = null, $options = array()) {
		// we have to set the url manually as the id in the form data causes Router errors.
		return parent::create($model, array('url' => array('action' => 'index')));
	}

	function search() {
		return parent::submit('Search', array('class' => 'submit tiny'));
	}

	function filter($fieldName, $options = array()) 
	{
		$this->setEntity($fieldName);
		
        $options = array_merge(array(
            'fromTo' => true
        ),$options);
        
		$modelKey = $this->model();
		$fieldKey = $this->field();
		$fieldDef = $this->_introspectModel($modelKey, 'fields', $fieldKey);
		$columnType = $fieldDef['type'];

		// override column type if in the options
		if ( !empty($options['type']) ) {
			$columnType = $options['type'];
			unset($options['type']);
		}
		else if ( 'datetime' == $columnType ) {
			// pretty safe to assume we want to turn date times into dates
			$columnType = 'date';
		}
		else if ( '_id' === substr($fieldName, -strlen('_id')) ) {
			$columnType = 'foreign';
		}

		// dont escape by defualt.
		if ( !isset($options['escape']) ) {
			$options['escape'] = false;
		}

		// qualify model.
		if ( strpos($fieldName, '.') === false ) {
			$fieldName = $modelKey . '.' . $fieldName;
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
				return $this->Form->input($fieldName, $options);
			break;

			case 'integer':
			case 'float':
                if ( $options['fromTo'] ) {
				    $from = $this->Form->input($fieldName . '.from', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				    $to = $this->Form->input($fieldName . '.to', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				    return $from . $to;
                } else {
                    return $this->Form->input($fieldName . '.from', array_merge(array('label' => false, 'type' => 'text', 'class' => 'range'), $options));
                }
			break;

			case 'date':
			case 'datetime':
			case 'timestamp':
                if ( $options['fromTo'] ) {
				    $from = $this->Form->input($fieldName . '.from', array('label' => 'From', 'class' => 'text date_picker', 'type' => 'text'));
				    $to = $this->Form->input($fieldName . '.to', array('label' => 'To', 'class' => 'text date_picker', 'type' => 'text'));
				    return $from . $to;
                } else {
                    return $this->Form->input($fieldName . '.from', array('label' => false, 'class' => 'text date_picker', 'type' => 'text'));
                }
			break;

			case 'time':
                if ( $options['fromTo'] ) {
				    $options = array_merge(array('empty' => true, 'type' => 'time'), $options);
				    $from = $this->Form->input($fieldName . '.from', $options);
				    $to = $this->Form->input($fieldName . '.to', $options);
				    return $from . $to;
                } else {
                    $options = array_merge(array('label' => false, 'empty' => true, 'type' => 'time'), $options);
                    return $this->Form->input($fieldName . '.from', $options);
                }
			break;

			case 'text':
			case 'string':
			case 'foreign':
			default:
				$options = array_merge(array('label' => false, 'empty' => true), $options);
				return $this->Form->input($fieldName, $options);
			break;
		}


		return $this->Form->input($fieldName, $options);
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
    * Simple template parser for strings
    *
    * Enables you to use {field} in a string to inject the `fields`
    * data in to the string to create dynamic strings
    *
    * @param mixed $output
    * @param mixed $data
    */
    function parseTemplate($output,$data){
		foreach($data as $k => $v){
			$output = str_replace('{'.$k.'}',$v,$output);
		}
		return $output;
	}

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
            return $this->parseTemplate(call_user_func($array['call_user_func'],$data[$field]),$data);
        }elseif(is_array($array) && !empty($array['switch'])){
            if(isset($array['switch'][$data[$field]])){
                return $this->parseTemplate($array['switch'][$data[$field]],$data);
            }else{
                return $this->parseTemplate($data[$field],$data);
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
            return $this->parseTemplate($output,$data);
		}
		if($array != $field){
			return $this->parseTemplate($array,$data);
		}
        return $this->parseTemplate($this->Text->autoLink($data[$field]),$data);
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
            return $this->parseTemplate('<img src="'.$path.'" />',$data);
        }elseif(!empty($array['html'])){
            $output = '';
            foreach($array['html'] as $part){
                if(!empty($data[$part])){
                    $output .= $data[$part];
                }else{
                    $output .= $part;
                }
            }
            return $this->parseTemplate($output,$data);
        }
    }

}
?>
