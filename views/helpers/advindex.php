<?php
class AdvindexHelper extends AppHelper {

	var $helpers = array('Advform.Advform', 'Html', 'Paginator', 'Form', 'Session');

	/**
	* @var FormHelper
	*/
	var $Form;
	/**
	* @var AdvformHelper
	*/
	var $AdvformHelper;

	function create($model) {
		// we have to set the url manually as the id in the form data causes Router errors.
		return $this->Form->create($model, array('url' => array('action' => 'index')));
	}

	function end() {
		return $this->Form->end();
	}

	function search() {
		return $this->Form->submit('Search');
	}

	function filter($field, $options = array()) {
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

		// dont escape by defualt.
		if ( !isset($options['escape']) ) {
			$options['escape'] = false;
		}

		// change integer column types if they are some sort of id.
		$match = '_id';
		if ( $match === substr($field, -strlen($match)) ) {
			$columnType = 'select';
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
				return $this->Advform->input($field, $options);
			break;

			case 'integer':
			case 'float':
				$from = $this->Advform->input($field . '.from', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				$to = $this->Advform->input($field . '.to', array_merge(array('type' => 'text', 'class' => 'range'), $options));
				return $from . $to;
			break;

			case 'date':
				$from = $this->Advform->input($field . '.from', array('label' => 'From', 'type' => 'calendar'));
				$to = $this->Advform->input($field . '.to', array('label' => 'To', 'type' => 'calendar'));
				return $from . $to;
			break;

			case 'datetime':
			case 'timestamp':
				$from = $this->Advform->input($field . '.from', array('label' => 'From', 'type' => 'calendar'));
				$fromTime = $this->Advform->input($field . '.from', array('type' => 'time', 'empty' => true, 'label' => false));
				$to = $this->Advform->input($field . '.to', array('label' => 'To', 'type' => 'calendar'));
				$toTime = $this->Advform->input($field .'.to', array('type' => 'time', 'empty' => true, 'label' => false));
				return $from .$fromTime . $to . $toTime;
			break;

			case 'time':
				$options = array_merge(array('empty' => true, 'type' => 'time'), $options);
				$from = $this->Advform->input($field . '.from', $options);
				$to = $this->Advform->input($field . '.to', $options);
				return $from . $to;
			break;

			case 'text':
			case 'string':
			default:
				$options = array_merge(array('type' => 'text', 'label' => false, 'empty' => true), $options);
				return $this->Advform->input($field, $options);
			break;
		}


		return $this->Advform->input($field, $options);
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
		$model = reset($this->params['models']);
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
}
?>