Advanced Index
====

Advanced scaffolding which allows searching and exporting / importing of your models.. For CakePHP 2

Installation
----

Add the repo to your working directory

	git clone https://github.com/morrislaptop/advindex app/Plugin/Advindex
	
Add the following to app/Config/bootstrap.php

	CakePlugin::load('Advindex');
  
Add the following to the top of AppController.php

	App::uses('Scaffold', 'Advindex.Controller');
	App::uses('ScaffoldView', 'Advindex.View');
	
Include the following in your controller:

	public $helpers = array('Advindex.Advindex');
	public $components = array('Advindex.Advindex');
	public $scaffold = 'admin';
	
Finally, check you have your routing prefix enabled in app/Config/core.php

	Configure::write('Routing.prefixes', array('admin'));
  
Configuration
----

See the bootstrap.php.example file to configure the scaffolding for each of your models

Credits
----

For migration to CakePHP 2 - http://www.pronique.com/blog/how-to-create-your-own-scaffolding-plugin-for-cakephp2