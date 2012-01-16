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
$this->Advindex->setTemplateVariables($scaffold, $structure, $scaffoldFields, $modelClass);
?>
<div class="<?php echo $pluralVar;?> index main">
<h2><?php echo $pluralHumanName;?></h2>
<?php echo $this->Advindex->create($modelClass); ?>
<div id="filler"></div>
<table cellpadding="0" cellspacing="0">
<thead>
<tr>
<?php foreach ($structure as $k => $v):?>
    <th>
        <?php
            if(!is_array($v) && strtolower($v) == 'actions'){
                echo '&nbsp;';
            }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
                if(in_array($v,$scaffoldFields)){   // Normal field
                    $_field = $v;
                }elseif(in_array($k,$scaffoldFields)){   // Field with options
                    $_field = $k;
                }
                echo $this->Paginator->sort($_field);
            }else{  // Group of fields
                echo Inflector::humanize($k);
            }
        ?>
    </th>
<?php endforeach;?>
</tr>
<tr class="filter">
<?php foreach ($structure as $k => $v):?>
    <th>
        <?php
            if(!is_array($v) && strtolower($v) == 'actions'){
                echo $this->Advindex->search();
            }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
                if(in_array($v,$scaffoldFields)){   // Normal field
                    $_field = $v;
                    echo $this->Advindex->filter($_field,array('fromTo'=>false));
                }elseif(in_array($k,$scaffoldFields)){   // Field with options
                    $_field = $k;
                    $options = array('fromTo'=>false);
                    if(!empty($structure[$k]['filter'])){
                        if(!empty($structure[$k]['filter']['select'])){
                            $options['type'] = 'select';
                            $options['options'] = $structure[$k]['filter']['select'];
                        }
                    }
                    echo $this->Advindex->filter($_field,$options);
                }
            }else{  // Group of fields
                echo '&nbsp;';
            }
        ?>
    </th>
<?php endforeach;?>
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
        foreach ($structure as $k => $v) {
            if(substr($k,0,strlen('_special_')) == '_special_'){
                echo "\t\t<td>\n\t\t\t";
                echo $this->Advindex->getOutputSpecial($structure[$k], ${$singularVar}[$modelClass]);
                echo " \n\t\t</td>\n";
            }elseif(!is_array($v) && strtolower($v) == 'actions'){
                echo "\t\t<td class=\"actions\">\n";
                if ( 'cataloguePages' == $pluralVar )
                {
                    echo "\t\t\t" . $this->Html->link(__('Draw Products', true), array('action' => 'draw', ${$singularVar}[$modelClass][$primaryKey])) . "\n";
                }
                echo "\t\t\t" . $this->Html->link(__('View', true), array('action' => 'view', ${$singularVar}[$modelClass][$primaryKey]), array('class' => 'view')) . "\n";
                echo "\t\t\t" . $this->Html->link(__('Edit', true), array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey]), array('class' => 'edit')) . "\n";
                if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('delete',$scaffold[$modelClass]['restrict']['actions'])){echo "\t\t\t" . $this->Html->link(__('Delete', true), array('action' => 'delete', ${$singularVar}[$modelClass][$primaryKey]), array('class' => 'delete'), __('Are you sure you want to delete', true).' #' . ${$singularVar}[$modelClass][$primaryKey]) . "\n";}
                echo "\t\t</td>\n";
            }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
                if(in_array($v,$scaffoldFields)){   // Normal field
                    $_field = $v;
                }elseif(in_array($k,$scaffoldFields)){   // Field with options
                    $_field = $k;
                }
                $isKey = false;
                echo "\t\t<td>\n\t\t\t";
                if (!empty($associations['belongsTo'])) {
                    foreach ($associations['belongsTo'] as $_alias => $_details) {
                        if ($_field === $_details['foreignKey']) {
                            $isKey = true;
                            echo $this->Html->link(
                                ${$singularVar}[$_alias][$_details['displayField']],
                                array(
                                    'controller' => $_details['controller'],
                                    'action' => 'view',
                                    ${$singularVar}[$_alias][$_details['primaryKey']]
                                )
                            );
                            break;
                        }
                    }
                }
                if ($isKey !== true) {
                    echo $this->Advindex->getOutput($structure[$k], ${$singularVar}[$modelClass], $_field);
                }
                echo " \n\t\t</td>\n";
            }else{  // Group of fields
                echo "\t\t<td>\n\t\t\t";
                foreach($structure[$k] as $gk => $gv){
                    if(is_integer($gk) || in_array($gk,$scaffoldFields)){ // Normal field OR Field with options
                        if(in_array($gv,$scaffoldFields)){   // Normal field
                            $_field = $gv;
                        }elseif(in_array($gk,$scaffoldFields)){   // Field with options
                            $_field = $gk;
                        }
                        $isKey = false;
                        echo "\t\t\n\t\t\t";
                        if (!empty($associations['belongsTo'])) {
                            foreach ($associations['belongsTo'] as $_alias => $_details) {
                                if ($_field === $_details['foreignKey']) {
                                    $isKey = true;
                                    echo $this->Html->link(
                                        ${$singularVar}[$_alias][$_details['displayField']],
                                        array(
                                            'controller' => $_details['controller'],
                                            'action' => 'view',
                                            ${$singularVar}[$_alias][$_details['primaryKey']]
                                        )
                                    );
                                    break;
                                }
                            }
                        }
                        if ($isKey !== true) {
                            echo $this->Advindex->getOutput($structure[$k][$gk], ${$singularVar}[$modelClass], $_field);
                        }
                        echo " \n\t\t<br/>\n";
                    }
                }
                echo "\t\t</td>\n\t\t\t";
            }
        }

    echo "\t</tr>\n";

endforeach;
echo "\n";
?>
</table>
    <div id="tableOptions" class="clear">
        <div id="pagination">
            <span class="page"><?php echo $this->Paginator->counter(array('format' => __('Page %page% of %pages%', true))); ?></span>
            <?php echo $this->Paginator->prev(__('Previous', true), array(), null, array('class' => 'disabled')); ?>
            <?php echo $this->Paginator->numbers(array('separator'=>' ')); ?>
            <?php echo $this->Paginator->next(__('Next', true), array(), null, array('class' => 'disabled')); ?>
        </div>
        <div id="csv"><?php echo $this->Advindex->export('Export as CSV'); ?></div>
    </div>
    <div class="clear"></div>
</div>