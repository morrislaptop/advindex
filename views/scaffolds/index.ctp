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
//debug($this->data);
//debug($types);
?>
<div class="<?php echo $pluralVar;?> index">
<h2><?php echo $pluralHumanName;?></h2>
<?php echo $this->Advindex->create($modelClass); ?>
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
                if(!empty($structure[$k]['image'])){
                    $path = '';
                    foreach($structure[$k]['image'] as $part){
                        if(!empty(${$singularVar}[$modelClass][$part])){
                            $path .= ${$singularVar}[$modelClass][$part];
                        }else{
                            $path .= $part;
                        }
                    }
                    echo '<img src="'.$path.'" />';
                }elseif(!empty($structure[$k]['html'])){
                    $output = '';
                    foreach($structure[$k]['html'] as $part){
                        if(!empty(${$singularVar}[$modelClass][$part])){
                            $output .= ${$singularVar}[$modelClass][$part];
                        }else{
                            $output .= $part;
                        }
                    }
                    echo $output;
                }
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
                    if(is_array($structure[$k]) && isset($structure[$k]['call_user_func'])){
                        echo call_user_func($structure[$k]['call_user_func'],${$singularVar}[$modelClass][$_field]);
                    }elseif(is_array($structure[$k]) && !empty($structure[$k]['switch'])){
                        if(isset($structure[$k]['switch'][${$singularVar}[$modelClass][$_field]])){
                            echo $structure[$k]['switch'][${$singularVar}[$modelClass][$_field]];
                        }else{
                            echo ${$singularVar}[$modelClass][$_field];
                        }
                    }elseif(isset($structure[$k]['formatDateTime']) && $structure[$k]['formatDateTime'] === true){
                        echo $this->Time->niceShort(${$singularVar}[$modelClass][$_field]);
                    }elseif(isset($structure[$k]['yesno']) && $structure[$k]['yesno'] === true){
                        echo (${$singularVar}[$modelClass][$_field] == 0?'No':(${$singularVar}[$modelClass][$_field] == 1?'Yes':${$singularVar}[$modelClass][$_field]));
                    }elseif(!empty($structure[$k]['html']) && is_array($structure[$k]['html'])){
                        $output = '';
                        foreach($structure[$k]['html'] as $part){
                            if(!empty(${$singularVar}[$modelClass][$part])){
                                $output .= ${$singularVar}[$modelClass][$part];
                            }else{
                                $output .= $part;
                            }
                        }
                        echo $output;
                    }else{
                        echo $this->Text->autoLink(${$singularVar}[$modelClass][$_field]);
                    }
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
                            if(is_array($structure[$k][$gk]) && isset($structure[$k][$gk]['call_user_func'])){
                                echo call_user_func($structure[$k][$gk]['call_user_func'],${$singularVar}[$modelClass][$_field]);
                            }elseif(is_array($structure[$k][$gk]) && !empty($structure[$k][$gk]['switch'])){
                                if(isset($structure[$k][$gk]['switch'][${$singularVar}[$modelClass][$_field]])){
                                    echo $structure[$k][$gk]['switch'][${$singularVar}[$modelClass][$_field]];
                                }else{
                                    echo ${$singularVar}[$modelClass][$_field];
                                }
                            }else{
                                echo $this->Text->autoLink(${$singularVar}[$modelClass][$_field]);
                            }
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
</div>
<div class="actions">
    <?php /*<h3><?php __('Menu'); ?></h3>
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
                $links[] = $this->Html->link('Settings', array('plugin' => 'settings', 'controller' => 'configs', 'action' => 'index'));
                echo implode('</li><li>', $links);
            ?>
        </li>
    </ul>
    <hr /> */ ?>
    <h3><?php __('Actions'); ?></h3>
    <ul>
        <?php if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('add',$scaffold[$modelClass]['restrict']['actions'])){ ?><li><?php echo $this->Html->link(sprintf(__('New %s', true), $singularHumanName), array('action' => 'add')); ?></li><?php } ?>
<?php
        $done = array();
        foreach ($associations as $_type => $_data) {
            foreach ($_data as $_alias => $_details) {
                if ($_details['controller'] != $this->name && !in_array($_details['controller'], $done)) {
                    echo "\t\t<li>" . $this->Html->link(sprintf(__('List %s', true), Inflector::humanize($_details['controller'])), array('controller' => $_details['controller'], 'action' => 'index')) . "</li>\n";
                    if(empty($scaffold[$_alias]['restrict']['actions']) || !in_array('add',$scaffold[$_alias]['restrict']['actions'])){echo "\t\t<li>" . $this->Html->link(sprintf(__('New %s', true), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' => 'add')) . "</li>\n";}
                    $done[] = $_details['controller'];
                }
            }
        }
?>
    </ul>
</div>