<?php
/**
 * @type Class
 * @desc Core Packages/Files Autoloader
 * @required none
 * @package core
 * @author Hani Gamal
 */ 
class Autoload
{
	public function __construct()
	{
		$this->__autoload();
	}
	
	public function __autoload()
	{
		// load core packages/files
		require_once 'class.auth.php';
		require_once 'class.config.php';
		require_once 'class.database.php';
		require_once 'class.dbloop.php';
		require_once 'class.dbobject.php';
		require_once 'class.dbsession.php';
		require_once 'class.encryption.php';
		require_once 'class.error.php';
		require_once 'class.gd.php'; 
		require_once 'class.loop.php';
		require_once 'class.objects.php';
		require_once 'class.pagepref.php';
		require_once 'class.pager.php';
		require_once 'class.rss.php';
		require_once 'class.stats.php';
		require_once 'class.tag.php';
		require_once 'class.urlcache.php';
	
	}
}
