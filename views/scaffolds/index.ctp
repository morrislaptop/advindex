<?php
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.console.libs.templates.views
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
 
$include = array(
	'players' => array('id', 'name', 'team', 'created'),
	#'products' => array('id', 'winning_tag_id', 'catalogue_page_id', 'name', 'status', 'points'),
	#'cataloguePages' => array('id', 'section_id', 'name', 'jpeg')
);
$exclude_filters = array(
	'tags' => array('product_id')
);

if ( !empty($include[$pluralVar]) ) {
	foreach ($scaffoldFields as $key => $field) {
		if ( !in_array($field, $include[$pluralVar]) ) {
			unset($scaffoldFields[$key]);
		}
	}	
}
?>
<div class="<?php echo $pluralVar;?> index">
<h2><?php echo $pluralHumanName;?></h2>
<?php echo $this->Advindex->create($modelClass); ?>
<table cellpadding="0" cellspacing="0">
<thead>
<tr>
<?php foreach ($scaffoldFields as $_field):?>
	<th><?php echo $this->Paginator->sort($_field);?></th>
<?php endforeach;?>
	<th><?php __('Actions');?></th>
</tr>
<tr class="filter">
<?php foreach ($scaffoldFields as $_field):?>
	<th><?php echo empty($exclude_filters[$pluralVar]) || !in_array($_field, $exclude_filters[$pluralVar]) ? $this->Advindex->filter($_field) : '&nbsp;'; ?></th>
<?php endforeach;?>
	<th><?php echo $this->Advindex->search(); ?></th>
</tr>
</thead>
<?php
$i = 0;
foreach (${$pluralVar} as ${$singularVar}):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
echo "\n";
	echo "\t<tr{$class}>\n";
		foreach ($scaffoldFields as $_field) {
			$isKey = false;
			if (!empty($associations['belongsTo'])) {
				foreach ($associations['belongsTo'] as $_alias => $_details) {
					if ($_field === $_details['foreignKey']) {
						$isKey = true;
						echo "\t\t<td>\n\t\t\t" . $this->Html->link(${$singularVar}[$_alias][$_details['displayField']], array('controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']])) . "\n\t\t</td>\n";
						break;
					}
				}
			}
			if ($isKey !== true) {
				echo "\t\t<td>\n\t\t\t" . ${$singularVar}[$modelClass][$_field] . " \n\t\t</td>\n";
			}
		}

		echo "\t\t<td class=\"actions\">\n";
		if ( 'cataloguePages' == $pluralVar )
		{
			echo "\t\t\t" . $this->Html->link(__('Draw Products', true), array('action' => 'draw', ${$singularVar}[$modelClass][$primaryKey])) . "\n";
		}
		echo "\t\t\t" . $this->Html->link(__('View', true), array('action' => 'view', ${$singularVar}[$modelClass][$primaryKey])) . "\n";
		echo "\t\t\t" . $this->Html->link(__('Edit', true), array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey])) . "\n";
		echo "\t\t\t" . $this->Html->link(__('Delete', true), array('action' => 'delete', ${$singularVar}[$modelClass][$primaryKey]), null, __('Are you sure you want to delete', true).' #' . ${$singularVar}[$modelClass][$primaryKey]) . "\n";
		echo "\t\t</td>\n";
	echo "\t</tr>\n";

endforeach;
echo "\n";
?>
</table>
	<p>
		<?php echo $this->Paginator->counter(array('format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true))); ?>
		|
		<?php echo $this->Advindex->export('Export as CSV'); ?>
	</p>
	<div class="paging">
	<?php echo "\t" . $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class' => 'disabled')) . "\n";?>
	 | <?php echo $this->Paginator->numbers() . "\n"?>
	<?php echo "\t ". $this->Paginator->next(__('next', true) .' >>', array(), null, array('class' => 'disabled')) . "\n";?>
	</div>
</div>
<div class="actions">
	<h3><?php __('Menu'); ?></h3>
	<ul>
		<li>
			<?php
				$controllers = App::objects('controller');
				$links = array();
				foreach ($controllers as $c)
				{
					if ( in_array($c, array('App', 'Pages', 'Bake', 'Import', 'Tab', 'Tasks', 'Home', 'Cuts')) ) {
						continue;
					}
					$links[] = $this->Html->link(Inflector::humanize(Inflector::underscore($c)), array('controller' => Inflector::underscore($c), 'action' => 'index'));
				}
				echo implode('</li><li>', $links);
			?>
		</li>
	</ul>
	<hr />
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), $singularHumanName), array('action' => 'add')); ?></li>
<?php
		$done = array();
		foreach ($associations as $_type => $_data) {
			foreach ($_data as $_alias => $_details) {
				if ($_details['controller'] != $this->name && !in_array($_details['controller'], $done)) {
					echo "\t\t<li>" . $this->Html->link(sprintf(__('List %s', true), Inflector::humanize($_details['controller'])), array('controller' => $_details['controller'], 'action' => 'index')) . "</li>\n";
					echo "\t\t<li>" . $this->Html->link(sprintf(__('New %s', true), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' => 'add')) . "</li>\n";
					$done[] = $_details['controller'];
				}
			}
		}
?>
	</ul>
</div>