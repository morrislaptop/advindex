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
 * @subpackage    cake.cake.libs.view.templates.layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
if(empty($this->plugin)){
    $this->Advindex->setTemplateVariables($scaffold, $structure, $scaffoldFields, $modelClass);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php echo $this->Html->charset(); ?>
    <title>
        <?php __('CakePHP: the rapid development php framework:'); ?>
        <?php echo $title_for_layout; ?>
    </title>
    <?php
        echo $this->Html->meta('icon');

        echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
        echo $this->Html->css('cake.generic');
        echo $this->Html->css('/advindex/css/style.css');

        echo $scripts_for_layout;
    ?>
</head>
<body>
    <div id="container">
        <div id="header">
            <?php
                $config = Configure::read('scaffold.config');
                if(!empty($config)){
                    if(!empty($config['fb_id'])){
                        $image = '<a href="https://www.facebook.com/'.$config['fb_id'].'" target="_blank"><img src="https://graph.facebook.com/'.$config['fb_id'].'/picture" /></a>';
                    }else{
                        $image = '<a href="http://www.ideaworks.com.au" target="_blank"><img src="'.$config['fb_id'].'" /></a>';
                    }
                    ?>
                    <ul id="info">
                        <li class="image"><?php echo $image; ?></li>
                        <li class="name"><?php echo $config['company']; ?></li>
                        <li class="description"><?php echo $config['project']; ?></li>
                        <li class="jobcode"><?php echo $config['job_number']; ?></li>
                    </ul>
                    <?php
                }
            ?>
            <ul id="menu">
                <?php
                    $controllers = App::objects('controller');
                    $links = array();
                    foreach ($controllers as $c)
                    {
                        if ( in_array($c, array('AppController', 'PagesController')) ) {
                            continue;
                        }
                        $c = str_replace('Controller', '', $c);
                        $class = '';
                        if(isset($pluralVar)){
                            if($pluralVar == strtolower($c)){
                                $class = ' class="current"';
                            }
                        }
                        $links[] = '<li'.$class.'>'.$this->Html->link(Inflector::humanize(Inflector::underscore($c)), array('plugin' => null, 'controller' => Inflector::underscore($c), 'action' => 'index')).'</li>';
                    }
                    if(!empty($scaffold['admin'])){
                        if(!empty($scaffold['admin']['addLinks'])){
                            foreach($scaffold['admin']['addLinks'] as $k => $v){
                                $class = '';
                                if($v['controller'] == $this->params['controller'] && $v['action'] == $this->params['action']){
                                    $class = ' class="current"';
                                }
                                $links[] = '<li'.$class.'>'.$this->Html->link($k, array_merge(array('plugin' => null),$v)).'</li>';
                            }
                        }
                    }
                    $class = '';
                    if($this->params['plugin'] == 'settings'){
                        $class = ' class="current"';
                    }
                    $links[] = '<li'.$class.'>'.$this->Html->link('Settings', array('plugin' => 'settings', 'controller' => 'configs', 'action' => 'index')).'</li>';
                    $links[] = '<li'.$class.'>'.$this->Html->link('Routes', array('plugin' => 'settings', 'controller' => 'routes', 'action' => 'index')).'</li>';
                    $links[] = '<li'.$class.'>'.$this->Html->link('Translations', array('plugin' => 'settings', 'controller' => 'translations', 'action' => 'index')).'</li>';
                    echo implode('', $links);
                ?>
            </ul>
        </div>
        <div id="content">
            <?php
                $flash = $this->Session->flash();
                $class = '';
                if(strpos($flash,'error') !== false){
                    $class = 'error';
                }else if(strpos($flash,'saved') !== false){
                    $class = 'success';
                }
                if(strlen($class) > 0){
                    $flash = str_replace('<div id="flashMessage" class="message">','<div id="flashMessage" class="'.$class.' message"><span class="result">'.ucwords($class).' - </span>',$flash);
                }
                echo $flash;
                if(empty($this->plugin))
                {
                    ?>
                    <select id="actions" onchange="javascript:window.location.href=this.options[this.selectedIndex].value">
                        <option value="" selected="selected">Select an Action</option>
	                    <?php if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('add',$scaffold[$modelClass]['restrict']['actions'])): ?>
	                        <option value="<?php echo $this->Html->url(array('action' => 'add')); ?>"><?php echo sprintf(__('New %s'), $singularHumanName); ?></option>
	                    <?php endif; ?>
	                    <?php if (in_array($this->action,array('edit'))):?>
	                        <?php if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('view',$scaffold[$modelClass]['restrict']['actions'])): ?>
	                            <option value="<?php echo $this->Html->url(array('action' => 'view', $this->Form->value($modelClass.'.'.$primaryKey))); ?>"><?php echo sprintf(__('View %s'), $singularHumanName); ?></option>
	                        <?php endif; ?>
	                    <?php endif;?>
		                <?php if ($this->action == 'view'): ?>
		                    <option value="<?php echo $this->Html->url(array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey])); ?>"><?php echo sprintf(__('Edit %s'), $singularHumanName); ?></option>
		                <?php endif; ?>
		                <?php if (in_array($this->action,array('view','edit'))):?>
		                    <?php if(empty($scaffold[$modelClass]['restrict']['actions']) || !in_array('delete',$scaffold[$modelClass]['restrict']['actions'])): ?>
		                        <option value="<?php echo $this->Html->url(array('action' => 'delete', $this->Form->value($modelClass.'.'.$primaryKey))); ?>"><?php echo sprintf(__('Delete %s'), $singularHumanName); ?></option>
		                    <?php endif; ?>
		                <?php endif;?>
                        <option value="<?php echo $this->Html->url(array('action' => 'index'));?>"><?php echo sprintf(__('List %s'), $pluralHumanName);?></option>
                    	<?php
                            $done = array();
                            foreach ($associations as $_type => $_data) {
                                foreach ($_data as $_alias => $_details) {
                                    if ($_details['controller'] != $this->name && !in_array($_details['controller'], $done)) {
                                        echo "\t\t<li>" . $this->Html->link(sprintf(__('List %s', true), Inflector::humanize($_details['controller'])), array('controller' => $_details['controller'], 'action' =>'index')) . "</li>\n";
                                        if(empty($scaffold[$_alias]['restrict']['actions']) || !in_array('add',$scaffold[$_alias]['restrict']['actions'])){echo "\t\t<li>" . $this->Html->link(sprintf(__('New %s'), Inflector::humanize(Inflector::underscore($_alias))), array('controller' => $_details['controller'], 'action' =>'add')) . "</li>\n";}
                                        $done[] = $_details['controller'];
                                    }
                                }
                            }
                   		?>
                    </select>
                    <?php
                }
            ?>
            <?php echo $content_for_layout; ?>
        </div>
        <div id="footer">
            <p id="info"><strong>AdminWorks</strong><br/>Version 0.2</p>
            <a href="http://www.ideaworks.com.au/?utm_source=AdminWorks" target="_blank">IdeaWorks</a>
        </div>
    </div>
    <?php echo $this->element('sql_dump'); ?>
</body>
</html>