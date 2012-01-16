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
$this->Advindex->setTemplateVariables($scaffold, $structure, $scaffoldFields, $modelClass);
?>
<div class="<?php echo $pluralVar;?> form main">
    <?php if ($this->action != 'add'):?>
        <h2>Edit an Entry</h2>
    <?php else: ?>
        <h2>Create a New Entry</h2>
    <?php endif; ?>
<?php
/*$this->setEntity('featured');
$modelName = $this->model();
$model =& ClassRegistry::getObject($modelName);
$columnType = $model->getColumnType($this->field());
debug($columnType);*/
    $model =& ClassRegistry::getObject($modelClass);
    echo $this->Form->create(array('type' => 'file'));
    echo '<div id="filler"></div>';
    foreach ($structure as $k => $v) {
        if(substr($k,0,strlen('_special_')) == '_special_'){
            $label = Inflector::humanize(substr($k,strlen('_special_')));
            $value = $this->Advindex->getOutputSpecial($structure[$k], $this->Form->data[$modelClass]);
            echo '<div class="input text"><label>'.$label.'</label>'.$value.'</div>';
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
                                    echo '<div class="input checkbox"><label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>'.$this->Form->checkbox($field, array('hiddenField' => true, 'label' => false)).'</div>';
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
                        echo '<div class="input checkbox"><label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>'.$this->Form->checkbox($field, array('hiddenField' => true, 'label' => false)).'</div>';
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
                            echo $this->Form->checkbox($field, array('hiddenField' => true, 'label' => false)).'<label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>';
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
                    echo $this->Form->checkbox($field, array('hiddenField' => true, 'label' => false)).'<label for="'.ucwords($modelClass).ucwords($field).'">'.Inflector::humanize($field).'</label>';
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
    <div class="clear"></div>
</div>