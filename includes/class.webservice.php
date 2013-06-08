<?
/*******************************************************************************
 *
 ******************************************************************************/
class WebService
{
  
  //
  // WebService Constructor
  //
  // Either constructs the JavaScript proxy or invokes the requested method
  // depending on what was sent through $_GET and $_POST
  //
  function __construct()
  {
    //Get the real (descendant) class' name:
    $class = get_class($this);
    
    //If they are requesting the javascript file, produce the javascript:
    if(getenv(QUERY_STRING) == 'js')
    {
      //NOTE: I don't add any whitespace in the JavaScript to keep file size small
      
      $classBody = '';
      $r = new ReflectionClass($class);
      
      //Build the javascript methods that map to the PHP ones:
      foreach($r->getMethods()as $m => $method)
      {
        if($method->isPublic() && $method->name != '__construct')
        {
          $args = '';
          $argSetter = '';
          
          foreach($method->getParameters() as $i => $parameter)
          {            
            $args .= $parameter->name . ',';
            $argSetter .= "__drm_args[$i]=" . $parameter->name . ';';
          }
          
          echo 'function ' . $class . "_" . $method->name . "(${args}onSuccess, onFailure, context)" . "{var __drm_args=[];${argSetter}__digifiss__remoteMethodCall('" . $_SERVER['SCRIPT_NAME']. "','$method->name',__drm_args,onSuccess,onFailure,context);};";
          
          //Class body contains what appear to be redundant function definitions,
          //but this is to simulate the "static" method interface that .NET 
          //uses; now we don't have to instantiate anything in JavaScript to
          //call our functions:
          if($classBody != '') $classBody .= ',';
          $classBody .= $method->name . ":function(${args}onSuccess, onFailure, context) {" . $class . "_". $method->name . "(${args}onSuccess, onFailure, context)" . ";}";
        }
      }
      
      //Dump out the actual javascript fake class definition:
      echo "var $class = { $classBody }";
    }
    else
    {
      if(isset($_POST['method']))
      {
        $methodName = $_POST['method'];
        $argCount = $_POST['argCount'];
        $status = 0;
        $args = array();
        for($x = 0; $x < $argCount; $x++)
        {
          array_push($args, $_POST["arg$x"]);
        }
        
        $r = new ReflectionClass($class);
        
        $method = $r->getMethod($methodName);
        
        if(!$method)
        {
          $result = "The method '$methodName' was not found.";
        }
        else
        {
          try
          {
            $result = $method->invokeArgs($this, $args);
            $status = 1;
          }
          catch(Exception $ex)
          {
            $result = $ex->getMessage() . "<br />" . $ex->getTraceAsString();
          }
        }
        
        $doc = new DOMDocument('1.0', 'iso-8859-1');
        
        $container = $doc->createElement('digifiss');
        
        $doc->appendChild($container);
        
        $statusel = $doc->createElement('status', $status);
        
        $container->appendChild($statusel);
        
        $resultel = $doc->createElement('result', $result);
        
        $container->appendChild($resultel);
        
        echo $doc->saveXML();
      }
      else
      {
        echo "This page is not designed to be called directly";
      }
    }
  }
  
  //
  // mysql_query_to_xsl
  //
  //Handy function that converts a MySQL SQL statement to an XML DOMDocument
  // and runs it through the XSLTProcessor for the given stylesheet
  //
  // PARAMETERS:
  // $connection: the link identifier of the MySQL connection instance
  // $sql: the SQL to run
  // $containerNode: the nodeName for the containing node
  // $childNode: the nodeName for each row's container
  // $xslfile: the local path of the XSL Stylesheet to apply to the XML
  //
  // RETURNS: The result of the XSL transform
  //
  protected function mysql_query_to_xsl($connection, $sql, $containerNode, $childNode, $xslfile)
  {
    $doc = $this->mysql_query_to_xml($connection, $sql, $containerNode, $childNode);
    
    $xslt = new XSLTProcessor();
    
    $xsldoc = new DOMDocument();
    
    $xsldoc->load($xslfile);
    
    $xslt->importStylesheet($xsldoc);
    
    return $xslt->transformToXML($doc);
  }
  
  //
  // mysql_query_to_xml
  //
  //Handy function that converts a MySQL SQL statement to a DOMDocument of the
  // following form:
  //      <containerNode>
  //        <childNode>
  //          <fieldName>value</fieldName>
  //          <fieldName>value</fieldName>
  //          ...
  //        </childNode>
  //        ...
  //      </containerNode>
  //
  // PARAMETERS:
  // $connection: the link identifier of the MySQL connection instance
  // $sql: the SQL to run
  // $containerNode: the nodeName for the containing node
  // $childNode: the nodeName for each row's container
  //
  // RETURNS: An XML DOMDocument in the above format
  //
  protected function mysql_query_to_xml($connection, $sql, $containerNode, $childNode)
  {
    $doc = new DOMDocument('1.0', 'iso-8859-1');
    
    $container = $doc->createElement($containerNode);
    
    $doc->appendChild($container);
    
    $result = mysql_query($sql, $connection) or die(mysql_error());
    $num = mysql_num_rows($result);
    
    for($x = 0; $x < $num; $x++)
    {
      $child = $doc->createElement($childNode);
      
      for($f = 0; $f < mysql_num_fields($result); $f++)
      {
        $field = $doc->createElement(mysql_field_name($result, $f), mysql_result($result, $x, $f));
        $child->appendChild($field);
      }
      
      $container->appendChild($child);
    }
    
    return $doc;
  }
 
  //This method adds the <SCRIPT> tag to the page in order to obtain
  //the JavaScript: 
  public static function add_service($path)
  {
    echo '<script type="text/javascript" src="' . $path . '?js"></script>';
  }
}
?>