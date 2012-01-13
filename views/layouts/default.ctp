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

        echo $this->Html->css('cake.generic');
        echo $this->Html->css('http://www.ideaworks.com.au/shoulder/css/ideaworks.css');
        echo $this->Html->script('http://www.ideaworks.com.au/shoulder/js/init.js', array('id'=>'job-SCPO0534'));

        echo $scripts_for_layout;
    ?>
</head>
<body>
    <div id="container">
        <div id="header">
            <ul id="info"></ul>
            <ul id="menu">
                <li>
                    <?php
                        $controllers = App::objects('controller');
                        $links = array();
                        foreach ($controllers as $c)
                        {
                            if ( in_array($c, array('App', 'Cuts', 'Pages', 'Proxy', 'Tasks')) ) {
                                continue;
                            }
                            $class = '';
                            if(isset($pluralVar)){
                                if($pluralVar == strtolower($c)){
                                    $class = ' class="current"';
                                }
                            }
                            $links[] = '<li'.$class.'>'.$this->Html->link(Inflector::humanize(Inflector::underscore($c)), array('plugin' => null, 'controller' => Inflector::underscore($c), 'action' => 'index')).'</li>';
                        }
                        #$links[] = $this->Html->link('Settings', array('plugin' => 'settings', 'controller' => 'configs', 'action' => 'index'));
                        echo implode('</li><li>', $links);
                    ?>
                </li>
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
            ?>
            <?php echo $content_for_layout; ?>
        </div>
        <div id="footer">
            <?php echo $this->Html->link(
                    $this->Html->image('cake.power.gif', array('alt'=> __('CakePHP: the rapid development php framework', true), 'border' => '0')),
                    'http://www.cakephp.org/',
                    array('target' => '_blank', 'escape' => false)
                );
            ?>
        </div>
    </div>
    <?php echo $this->element('sql_dump'); ?>
</body>
</html>