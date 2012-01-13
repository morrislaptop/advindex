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
    }
}else{
    $structure = $scaffoldFields;
}
//debug($structure);debug($this->Form->data[$modelClass]);
?>
<div class="<?php echo $pluralVar;?> form">
<?php
/*$this->setEntity('featured');
$modelName = $this->model();
$model =& ClassRegistry::getObject($modelName);
$columnType = $model->getColumnType($this->field());
debug($columnType);*/
    $model =& ClassRegistry::getObject($modelClass);
    echo $this->Form->create(array('type' => 'file'));
    foreach ($structure as $k => $v) {
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
                echo '<div class="input text"><label>'.Inflector::humanize(substr($k,strlen('_special_'))).'</label><img src="'.$path.'" /></div>';
            }elseif(!empty($structure[$k]['html'])){
                $output = '';
                foreach($structure[$k]['html'] as $part){
                    if(!empty($this->Form->data[$modelClass][$part])){
                        $output .= $this->Form->data[$modelClass][$part];
                    }else{
                        $output .= $part;
                    }
                }
                echo '<div class="input text"><label>'.Inflector::humanize(substr($k,strlen('_special_'))).'</label>'.$output.'</div>';
            }
        }elseif(is_integer($k) || in_array($k,$scaffoldFields)){ // Normal field OR Field with options
            if(in_array($v,$scaffoldFields)){   // Normal field
                $field = $v;
            }elseif(in_array($k,$scaffoldFields)){   // Field with options
                $field = $k;
            }
            
            if(!empty($structure[$k]['display']) && $structure[$k]['display'] == 'nextToSubmit'){
                $displayNextToSubmit = $k;
                continue;
            }
            
            // Meta fields
            if ( in_array($field, array('created', 'modified', 'updated')) ) {
                continue;
            }
            
            // File upload
            if ( isset($upload_fields) && in_array($field, $upload_fields) ) 
            {
                if ( !empty($this->Form->data[$modelClass][$field]) ) {
                    echo $this->Html->image($this->Form->data[$modelClass][$field], array('url' => $this->Form->data[$modelClass][$field], 'height' => 100));
                }
                echo $this->Form->input($field, array('type' => 'file'));
                continue;
            }
            
            // Begins or ends - allow empty
            if ( in_array($field, array('begins', 'ends', 'left_player_id', 'right_player_id')) ) {
                echo $this->Form->input($field, array('empty' => ''));
                continue;
            }
        
            // The rest
            if(is_array($structure[$k])){ // has options
                if(!empty($structure[$k]['disabled'])){
                    $options = array();
                    $options['disabled'] = true;
                    if($structure[$k]['disabled'] !== true && $structure[$k]['disabled'] == 'text'){
                        $options['type'] = 'text';
                    }
                    echo $this->Form->input($field,$options);
                }elseif(!empty($structure[$k]['select'])){
                    echo '<div class="input text required"><label for"'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>'.$this->Form->select($field,$structure[$k]['select'],$this->Form->data[$modelClass][$field]).'</div>';
                }elseif(!empty($structure[$k]['showIf'])){
                    $keys = array_keys($structure[$k]['showIf']);
                    if(!empty($keys[0])){
                        if($structure[$k]['showIf'][$keys[0]] == $this->Form->data[$modelClass][$keys[0]]){
                            switch($model->getColumnType($field)){
                                case 'boolean':
                                    echo '<div class="input checkbox"><label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>'.$this->Form->checkbox($field, array('hiddenField' => false, 'label' => false)).'</div>';
                                break;
                                default:
                                    echo $this->Form->input($field);
                                break;
                            }
                        }
                    }
                }else{
                    echo $this->Form->input($field);
                }
            }else{
                switch($model->getColumnType($field)){
                    case 'boolean':
                        echo '<div class="input checkbox"><label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>'.$this->Form->checkbox($field, array('hiddenField' => false, 'label' => false)).'</div>';
                    break;
                    default:
                        echo $this->Form->input($field);
                    break;
                }
            }
        }else{  // Group of fields - not implemented yet
        
        }
    }
    if(!empty($displayNextToSubmit)){
        echo '<div class="submit">';
        echo $this->Form->submit('Submit',array('div'=>false));
        $k = $displayNextToSubmit;
        if(in_array($structure[$k],$scaffoldFields)){   // Normal field
            $field = $structure[$k];
        }elseif(in_array($k,$scaffoldFields)){   // Field with options
            $field = $k;
        }
        if(!empty($structure[$k]['showIf'])){
            $keys = array_keys($structure[$k]['showIf']);
            if(!empty($keys[0])){
                if($structure[$k]['showIf'][$keys[0]] == $this->Form->data[$modelClass][$keys[0]]){
                    switch($model->getColumnType($field)){
                        case 'boolean':
                            echo $this->Form->checkbox($field, array('hiddenField' => false, 'label' => false)).'<label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>';
                        break;
                        default:
                            echo $this->Form->input($field);
                        break;
                    }
                }
            }
        }else{
            switch($model->getColumnType($field)){
                case 'boolean':
                    echo $this->Form->checkbox($field, array('hiddenField' => false, 'label' => false)).'<label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>';
                break;
                default:
                    echo $this->Form->input($field);
                break;
            }
        }
        echo '</div>';
    }else{
        echo $this->Form->submit();
    }
    echo $this->Form->end();
?>
</div>
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
<?php if ($this->action != 'add'):?>
        <?php if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('delete',$scaffold[$modelClass]['restrict']['actions'])){ ?><li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value($modelClass.'.'.$primaryKey)), null, __('Are you sure you want to delete', true).' #' . $this->Form->value($modelClass.'.'.$primaryKey)); ?></li><?php } ?>
<?php endif;?>
        <li><?php echo $this->Html->link(__('List', true).' '.$pluralHumanName, array('action' => 'index'));?></li>
<?php
        $done = array();
        foreach ($associations as $_type => $_data) {
            foreach ($_data as $_alias => $_details) {
                if ($_details['controller'] != $this->name && !in_array($_details['controller'], $done)) {
                    echo "\t\t<li>" . $this->Html->link(sprintf(__('List %s', true), Inflector::humanize($_details['controller'])), array('controller' => $_details['controller'], 'action' =>'index')) . "</li>\n";
                    if(empty($scaffold[$_alias]['restrict']['actions']) || !in_array('add',$scaffold[$_alias]['restrict']['actions'])){echo "\t\t<li>" . $this->Html->link(sprintf(__('New %s', true), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' =>'add')) . "</li>\n";}
                    $done[] = $_details['controller'];
                }
            }
        }
?>
    </ul>
</div>