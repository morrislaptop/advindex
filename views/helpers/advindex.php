<?php
class AdvindexHelper extends AppHelper {

	var $helpers = array('Uniform.Uniform', 'Html', 'Paginator', 'Form', 'Session');

	/**
	* @var FormHelper
	*/
	var $Form;
	/**
	* @var UniformHelper
	*/
	var $UniformHelper;

	function create($model) {
		// we have to set the url manually as the id in the form data causes Router errors.
		return $this->Uniform->create($model, array('url' => $this->url(array('action' => 'index'))));
	}

	function end() {
		return $this->Uniform->end();
	}

	function search() {
		return $this->Uniform->submit('Search');
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

		// change integer column types if they are some sort of id.
		$match = '_id';
		if ( $match === substr($field, -strlen($match)) ) {
			$columnType = 'select';
		}

		// qualify model.
		$field = $modelName . '.' . $field;

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
			case 'float':
				$from = $this->Uniform->input($field . '.from', array_merge(array('type' => 'text'), $options));
				$to = $this->Uniform->input($field . '.to', array_merge(array('type' => 'text'), $options));
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

	function import($model, $options = array())
	{
		$out = '';

		$sessionKey = 'Advindex.' . $model . '.import';
		$import = $this->Session->read($sessionKey);

		if ( $import ) {
			$out .= '<div id="flashMessage" class="message"><p>Results of your import:</p><ul>';
			$out .= '<li>' . $import['created'] . ' ' . __n('record', 'records', $import['created'], true) . ' created</li>';
			$out .= '<li>' . $import['updated'] . ' ' . __n('record', 'records', $import['updated'], true) . ' updated</li>';
			if ( $import['errors'] ) {
				$errorsCount = count($import['errors']);
				$out .= '<li>' . $errorsCount . ' ' . __n('error', 'errors', $errorsCount, true) . ' occured: </li>';
				$out .= '<ul>';
				foreach ($import['errors'] as $line => $whys) {
					$out .= '<li>Line ' . $line . '<ul>';
					foreach ($whys as $why) {
						$out .= '<li>' . $why . '</li>';
					}
					$out .= '</ul></li>';
				}
				$out .= '</ul></li>';
			}
			$out .= '</ul></div>';

			// clear session out.
			$this->Session->del($sessionKey);
		}

		$default_options = array(
			'action' => 'import',
			'type' => 'file'
		);
		$out .= $this->Form->create($model, array_merge($default_options, $options));
		$truncate = $this->Form->input('truncate', array('div' => false, 'label' => 'Empty table before import?', 'type' => 'checkbox'));
		$submit = $this->Form->submit('Upload', array('div' => false, 'label' => false));
		$out .= $this->Form->input('csv', array('type' => 'file', 'after' => $truncate . $submit, 'label' => false));
		$out .= $this->Form->end();
		return $out;
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