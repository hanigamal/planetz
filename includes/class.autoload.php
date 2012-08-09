<?php

/**
 * class autoload files 
 * 
 */ 
class Autoload
{
	public function __construct()
	{
		$this->__autoload($classname);
	}
	
	public function __autoload($classname)
	{
		$file = $classname. 'php';
		if(file_exists($file))
		{
			require_once $file;
		}
		
	}
}
