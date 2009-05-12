<?php
class AdvindexHelper extends AppHelper {

	var $helpers = array('Uniform.Uniform', 'Html', 'Paginator');

	/**
	* @var FormHelper
	*/
	var $Form;
	/**
	* @var UniformHelper
	*/
	var $UniformHelper;

	function create($model) {
		return $this->Uniform->create($model, array('action' => 'index'));
	}

	function end() {
		return $this->Uniform->end();
	}

	function search() {
		return $this->Uniform->submit('Search');
	}

	function filter($field, $options = array()) {

		$this->setEntity($field);
		$model =& ClassRegistry::getObject($this->model());
		$columnType = $model->getColumnType($this->field());

		// override column type if in the options
		if ( !empty($options['type']) ) {
			$columnType = $options['type'];
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
				return $this->Uniform->input($field, $options);
			break;

			case 'integer':
				$from = $this->Uniform->input($field . '.from');
				$to = $this->Uniform->input($field . '.to');
				return $from . $to;
			break;

			case 'date':
				$from = $this->Uniform->input($field . '.from', array('label' => 'From', 'type' => 'calendar'));
				$to = $this->Uniform->input($field . '.to', array('label' => 'To', 'type' => 'calendar'));
				return $from . $to;
			break;

			case 'datetime':
			case 'timestamp':
				$from = $this->Uniform->input($field . '.from', array('label' => 'From', 'type' => 'calendar'));
				$fromTime = $this->Uniform->input($field . '.from', array('type' => 'time', 'empty' => true, 'label' => false));
				$to = $this->Uniform->input($field . '.to', array('label' => 'To', 'type' => 'calendar'));
				$toTime = $this->Uniform->input($field .'.to', array('type' => 'time', 'empty' => true, 'label' => false));
				return $from .$fromTime . $to . $toTime;
			break;

			case 'time':
				$options = array_merge(array('empty' => true, 'type' => 'time'), $options);
				$from = $this->Uniform->input($field . '.from', $options);
				$to = $this->Uniform->input($field . '.to', $options);
				return $from . $to;
			break;

			case 'text':
			case 'string':
			default:
				$options = array_merge(array('type' => 'text', 'label' => false, 'empty' => true), $options);
				return $this->Uniform->input($field, $options);
			break;
		}


		return $this->Uniform->input($field, $options);
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
		return $this->Uniform->select('perPage', $opts, $limit, array('onchange' => "this.form.submit();"), false);
	}
}
?>