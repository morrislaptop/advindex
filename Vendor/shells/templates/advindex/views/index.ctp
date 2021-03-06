<?php
/* SVN FILE: $Id$ */
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.console.libs.templates.views
 * @since         CakePHP(tm) v 1.2.0.5234
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
?>
<?php
	if ( $hasOrder = in_array('order', $fields) ) {
		echo "<?php echo \$this->element('js_ordering', array('plugin' => 'advindex', 'model' => '{$modelClass}')); ?>\n";
	}
?>
<div class="<?php echo $pluralVar;?> index">
<h2><?php echo "<?php __('{$pluralHumanName}');?>";?></h2>
<p><?php echo "<?php echo \$this->Advindex->export('Export as CSV'); ?> | <?php echo \$this->Html->link('Import from CSV', '#', array('onclick' => \"\$('#{$modelClass}ImportForm').toggle();\")); ?></p>\n"; ?>
<?php echo "<?php echo \$this->element('import_form', array('plugin' => 'advindex', 'model' => '{$modelClass}')); ?>\n"; ?>
<?php echo "<?php echo \$this->Advindex->create('{$singularHumanName}'); ?>\n"; ?>
<table cellpadding="0" cellspacing="0">
<thead>
	<tr>
<?php
	$first = true;
	foreach ($fields as $field) {
		echo "\t\t<th" . ($first ? ' class="headerLeft"' : '') . "><?php echo \$this->Paginator->sort('{$field}'); ?></th>\n";
		$first = false;
	}
?>
		<th<?php echo $hasOrder ? ' colspan="2"' : ''; ?> class="headerRight actions"><?php echo "<?php __('Actions'); ?>"; ?></th>
	</tr>
	<tr class="filter">
<?php
	foreach ($fields as $field) {
		echo "\t\t<td><?php echo \$this->Advindex->filter('{$field}'); ?></td>\n";
	}
?>
		<td<?php echo $hasOrder ? ' colspan="2"' : ''; ?>><?php echo "<?php echo \$this->Advindex->search(); ?>"; ?></td>
	</tr>
</thead>
<tbody>
<?php
echo "<?php
\$i = 0;
foreach (\${$pluralVar} as \${$singularVar}):
	\$class = null;
	if (\$i++ % 2 == 0) {
		\$class = ' class=\"altrow\"';
	}
	\$id = \${$singularVar}['{$modelClass}']['id'];
?>\n";
	echo "\t<tr<?php echo \$class;?> id=\"<?php echo \$id; ?>\">\n";
		foreach ($fields as $field) {
			$isKey = false;
			if (!empty($associations['belongsTo'])) {
				foreach ($associations['belongsTo'] as $alias => $details) {
					if ($field === $details['foreignKey']) {
						$isKey = true;
						echo "\t\t<td>\n\t\t\t<?php echo \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], array('controller'=> '{$details['controller']}', 'action'=>'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}'])); ?>\n\t\t</td>\n";
						break;
					}
				}
			}
			if ($isKey !== true) {
				echo "\t\t<td>\n\t\t\t<?php echo \${$singularVar}['{$modelClass}']['{$field}']; ?>\n\t\t</td>\n";
			}
		}

		if ( $hasOrder ) {
			echo "\t\t<td class=\"dragHandle\"><?php echo \$this->Html->image('/advindex/img/drag_handle.gif', array('alt' => 'Drag', 'style' => 'cursor: move;')); ?></td>\n";
		}

		echo "\t\t<td class=\"actions\">\n";
		echo "\t\t\t<?php echo \$this->Html->link(__('View', true), array('action'=>'view', \${$singularVar}['{$modelClass}']['{$primaryKey}'])); ?>\n";
	 	echo "\t\t\t<?php echo \$this->Html->link(__('Edit', true), array('action'=>'edit', \${$singularVar}['{$modelClass}']['{$primaryKey}'])); ?>\n";
	 	echo "\t\t\t<?php echo \$this->Html->link(__('Delete', true), array('action'=>'delete', \${$singularVar}['{$modelClass}']['{$primaryKey}']), null, sprintf(__('Are you sure you want to delete # %s?', true), \${$singularVar}['{$modelClass}']['{$primaryKey}'])); ?>\n";
		echo "\t\t</td>\n";
	echo "\t</tr>\n";

echo "<?php endforeach; ?>\n";
?>
</tbody>
<tfoot>
	<?php echo "<?php echo \$this->element('tfoot', array('plugin' => 'advindex')); ?>\n"; ?>
</tfoot>
</table>
</div>
<div class="actions">
	<ul>
		<li><?php echo "<?php echo \$this->Html->link(__('New {$singularHumanName}', true), array('action'=>'add')); ?>";?></li>
	</ul>
</div>