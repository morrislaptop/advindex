<?php
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.view.templates.scaffolds
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
//debug($structure);debug($this->Form->data[$modelClass]);
?>
<div class="<?php echo $pluralVar;?> view">
<h2><?php printf(__("View %s", true), $singularHumanName); ?></h2>
    <table cellspacing="0" cellpadding="0" border="0">
        <tbody>
<?php
$i = 0;
foreach ($structure as $k => $v) {
    if(in_array($v,$scaffoldFields)){   // Normal field
        $field = $v;
    }elseif(in_array($k,$scaffoldFields)){   // Field with options
        $field = $k;
    }else{
        $field = null;
    }
    $label = '';
    $value = '';
    $class = null;
    if ($i++ % 2 == 0) {
        $class = ' class="altrow"';
    }
    $isKey = false;
    if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $_alias => $_details) {
            if ($field === $_details['foreignKey']) {
                $isKey = true;
                $label = Inflector::humanize($_alias);
                $value = $this->Html->link(${$singularVar}[$_alias][$_details['displayField']], array('controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']]));
                break;
            }
        }
    }
    if ($isKey !== true) {
        if(substr($k,0,strlen('_special_')) == '_special_'){
            if(!empty($structure[$k]['image'])){
                $path = '';
                foreach($structure[$k]['image'] as $part){
                    if(!empty($this->Form->data[$modelClass][$part])){
                        $path .= $this->Form->data[$modelClass][$part];
                    }else{
                        $path .= $part;
                    }
                }
                $label = Inflector::humanize(substr($k,strlen('_special_')));
                $value = '<img src="'.$path.'" />';
            }elseif(!empty($structure[$k]['html'])){
                $output = '';
                foreach($structure[$k]['html'] as $part){
                    if(!empty($this->Form->data[$modelClass][$part])){
                        $output .= $this->Form->data[$modelClass][$part];
                    }else{
                        $output .= $part;
                    }
                }
                $label = Inflector::humanize(substr($k,strlen('_special_')));
                $value = $output;
            }
        }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
            // Meta fields
            /*if ( in_array($field, array('created', 'modified', 'updated')) ) {
                continue;
            }*/
            
            // File upload
            /*if ( isset($upload_fields) && in_array($field, $upload_fields) ) 
            {
                if ( !empty($this->Form->data[$modelClass][$field]) ) {
                    echo $this->Html->image($this->Form->data[$modelClass][$field], array('url' => $this->Form->data[$modelClass][$field], 'height' => 100));
                }
                echo $this->Form->input($field, array('type' => 'file'));
                continue;
            }*/
            
            // Begins or ends - allow empty
            /*if ( in_array($field, array('begins', 'ends', 'left_player_id', 'right_player_id')) ) {
                echo $this->Form->input($field, array('empty' => ''));
                continue;
            }*/
        
            // The rest
            if(!empty($field)){
                $label = Inflector::humanize($field);
                if(is_array($structure[$k])){ // has options
                    if(is_array($structure[$k]) && isset($structure[$k]['call_user_func'])){
                        $value =  call_user_func($structure[$k]['call_user_func'],${$singularVar}[$modelClass][$field]);
                    }elseif(is_array($structure[$k]) && !empty($structure[$k]['switch'])){
                        if(isset($structure[$k]['switch'][${$singularVar}[$modelClass][$field]])){
                            $value =  $structure[$k]['switch'][${$singularVar}[$modelClass][$field]];
                        }else{
                            $value =  ${$singularVar}[$modelClass][$field];
                        }
                    }elseif(isset($structure[$k]['format']) && $structure[$k]['format'] === 'json'){
                        $value =  '<pre>'.print_r(json_decode(${$singularVar}[$modelClass][$field]),true).'</pre>';
                    }elseif(isset($structure[$k]['formatDateTime']) && $structure[$k]['formatDateTime'] === true){
                        $value =  $this->Time->niceShort(${$singularVar}[$modelClass][$field]);
                    }elseif(isset($structure[$k]['yesno']) && $structure[$k]['yesno'] === true){
                        $value =  (${$singularVar}[$modelClass][$field] == 0?'No':(${$singularVar}[$modelClass][$field] == 1?'Yes':${$singularVar}[$modelClass][$field]));
                    }elseif(!empty($structure[$k]['html'])){
                        $output = '';
                        foreach($structure[$k]['html'] as $part){
                            if(!empty($this->Form->data[$modelClass][$part])){
                                $output .= $this->Form->data[$modelClass][$part];
                            }else{
                                $output .= $part;
                            }
                        }
                        $value = $output;
                    }    
                    else{
                        $value = $this->Text->autoLink(${$singularVar}[$modelClass][$field]);
                    }
                }else{
                    $value = $this->Text->autoLink(${$singularVar}[$modelClass][$field]);
                }
            }
        }else{  // Group of fields - not implemented yet
        
        }
    }
    if(!empty($label)){
        echo "\t<tr>\n";
        echo "\t\t<td{$class} width=\"150\">" . $label . "</td>\n";
        echo "\t\t<td{$class}>\n\t\t\t".$value."\n&nbsp;\t\t</td>\n";
        echo "\t</tr>\n";
    }
}
?>
        </tbody>
    </table>
</div>
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
<?php
    echo "\t\t<li>" .$this->Html->link(sprintf(__('Edit %s', true), $singularHumanName),   array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey])). " </li>\n";
    if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('delete',$scaffold[$modelClass]['restrict']['actions'])){echo "\t\t<li>" .$this->Html->link(sprintf(__('Delete %s', true), $singularHumanName), array('action' => 'delete', ${$singularVar}[$modelClass][$primaryKey]), null, __('Are you sure you want to delete', true).' #' . ${$singularVar}[$modelClass][$primaryKey] . '?'). " </li>\n";}
    echo "\t\t<li>" .$this->Html->link(sprintf(__('List %s', true), $pluralHumanName), array('action' => 'index')). " </li>\n";
    if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('add',$scaffold[$modelClass]['restrict']['actions'])){echo "\t\t<li>" .$this->Html->link(sprintf(__('New %s', true), $singularHumanName), array('action' => 'add')). " </li>\n";}

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
<?php
if (!empty($associations['hasOne'])) :
foreach ($associations['hasOne'] as $_alias => $_details): ?>
<div class="related">
    <h3><?php printf(__("Related %s", true), Inflector::humanize($_details['controller'])); ?></h3>
<?php if (!empty(${$singularVar}[$_alias])):?>
    <dl>
<?php
        $i = 0;
        $otherFields = array_keys(${$singularVar}[$_alias]);
        foreach ($otherFields as $_field) {
            $class = null;
            if ($i++ % 2 == 0) {
                $class = ' class="altrow"';
            }
            echo "\t\t<dt{$class}>" . Inflector::humanize($_field) . "</dt>\n";
            echo "\t\t<dd{$class}>\n\t" . ${$singularVar}[$_alias][$_field] . "\n&nbsp;</dd>\n";
        }
?>
    </dl>
<?php endif; ?>
    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(sprintf(__('Edit %s', true), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' => 'edit', ${$singularVar}[$_alias][$_details['primaryKey']]))."</li>\n";?>
        </ul>
    </div>
</div>
<?php
endforeach;
endif;

if (empty($associations['hasMany'])) {
    $associations['hasMany'] = array();
}
if (empty($associations['hasAndBelongsToMany'])) {
    $associations['hasAndBelongsToMany'] = array();
}
$relations = array_merge($associations['hasMany'], $associations['hasAndBelongsToMany']);
$i = 0;
foreach ($relations as $_alias => $_details):
$otherSingularVar = Inflector::variable($_alias);
?>
<div class="related">
    <h3><?php printf(__("Related %s", true), Inflector::humanize($_details['controller'])); ?></h3>
<?php if (!empty(${$singularVar}[$_alias])):?>
    <table cellpadding="0" cellspacing="0">
    <tr>
<?php
        $otherFields = array_keys(${$singularVar}[$_alias][0]);
        if (isset($_details['with'])) {
            $index = array_search($_details['with'], $otherFields);
            unset($otherFields[$index]);
        }
        foreach ($otherFields as $_field) {
            echo "\t\t<th>" . Inflector::humanize($_field) . "</th>\n";
        }
?>
        <th class="actions">Actions</th>
    </tr>
<?php
        $i = 0;
        foreach (${$singularVar}[$_alias] as ${$otherSingularVar}):
            $class = null;
            if ($i++ % 2 == 0) {
                $class = ' class="altrow"';
            }
            echo "\t\t<tr{$class}>\n";

            foreach ($otherFields as $_field) {
                echo "\t\t\t<td>" . ${$otherSingularVar}[$_field] . "</td>\n";
            }

            echo "\t\t\t<td class=\"actions\">\n";
            echo "\t\t\t\t" . $this->Html->link(__('View', true), array('controller' => $_details['controller'], 'action' => 'view', ${$otherSingularVar}[$_details['primaryKey']])). "\n";
            echo "\t\t\t\t" . $this->Html->link(__('Edit', true), array('controller' => $_details['controller'], 'action' => 'edit', ${$otherSingularVar}[$_details['primaryKey']])). "\n";
            if(empty($scaffold[$_alias]['restrict']['actions']) || !in_array('delete',$scaffold[$_alias]['restrict']['actions'])){echo "\t\t\t\t" . $this->Html->link(__('Delete', true), array('controller' => $_details['controller'], 'action' => 'delete', ${$otherSingularVar}[$_details['primaryKey']]), null, __('Are you sure you want to delete', true).' #' . ${$otherSingularVar}[$_details['primaryKey']] . '?'). "\n";}
            echo "\t\t\t</td>\n";
        echo "\t\t</tr>\n";
        endforeach;
?>
    </table>
<?php endif;
if(empty($scaffold[$_alias]['restrict']['actions']) || !in_array('add',$scaffold[$_alias]['restrict']['actions'])){
    ?>
    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(sprintf(__("New %s", true), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' => 'add'));?> </li>
        </ul>
    </div>
</div>
<?php 
}
endforeach;
?>