<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initDb()
	{
	    $db = $this->getPluginResource('db');
	    Zend_Registry::set('DB',$db);
	}

}

