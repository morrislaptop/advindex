<?php
class AdvindexHelper extends AppHelper {

	var $helpers = array('Html', 'Paginator', 'Form', 'Session', 'Text');

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
    
    function templateOutput($tpl){
        $view = str_replace(APP,'',$tpl);
        $plugin = str_replace(APP.'plugins'.DS,'',__FILE__);
        $plugin = substr($plugin,0,strpos($plugin,DS));
        echo $view.';'.$plugin;
        //echo __FILE__.APP.';'.$tpl;
        //echo __FILE__.APP.';'.$tpl;
    }
}
?>