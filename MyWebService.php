<?php
// Sample of using WebService Class

include_once 'includes/class.webservice.php';

class MyScriptService extends DigifiScriptService
{
	//This is a public function which will be turned into a ScriptMethod
	public function HelloWorld()
	{
		return "Hello World";
	}

	//A ScriptMethod with arguments:
	public function Add($x, $y)
	{
		return $x + $y;
	}

	//A private method, will be ignored by the JavaScript generator:
	private function DoSomethingPrivate()
	{
		return "Not Exposed";
	}
}

//This is the only line of initialization required:
new MyScriptService();
?>
