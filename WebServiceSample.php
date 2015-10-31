<?php
include_once 'includes/class.webservice.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>DigifiScriptService Sample Page</title>
	<script type="text/javascript" src="webservice.js"></script>
	<? DigifiScriptService::add_service('MyWebService.php'); ?>
	</head>
	<body>

	<a href="javascript:DoHelloWorld();">Click for Hello World</a>
	<div id="HelloWorld"></div>
	<br />
	<br />
	<a href="javascript:DoAddition();">Add 2 + 2</a>
	<div id="Addition"></div>
	<br />
	<br />

	<script type="text/javascript">

	function DoHelloWorld()
	{
		document.getElementById('HelloWorld').innerHTML = 'Loading...';
		MyScriptService.HelloWorld(onSuccess, onFailure, 'HelloWorld');
	}

	function DoAddition()
	{
		document.getElementById('Addition').innerHTML = 'Loading...';
		MyScriptService.Add(2, 2, onSuccess, onFailure, 'Addition');
	}

	function onSuccess(result, context, method)
	{
		document.getElementById(context).innerHTML = result;
	}

	function onFailure(result, context, method)
	{
		document.getElementById(context).innerHTML = result;
	}
	</script>
	</body>
</html>
