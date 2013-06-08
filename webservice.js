/*******************************************************************************
 * WebService Javascript File
 *  A reflection-based simulator of .NET's ScriptService attribute for PHP
 ******************************************************************************/

var __digifiss__maxConcurrent = 3;
var __digifiss__waitdelay = 300;
var __digifiss__connectors = [];

function __digifiss__remoteMethodCall(servicePath, methodName, args, onSuccess, onFailure, context)
{
  var connector = __digifiss__getavailableconnector();

  if(!connector)
  {
    var command = function() {__digifiss__remoteMethodCall(servicePath, methodName, args, onSuccess, onFailure, context);};

    setTimeout(command, __digifiss__waitdelay);
  }
  else
  {
    connector.busy = true;
    connector.context = context;
    connector.onSuccess = onSuccess;
    connector.onFailure = onFailure;
    connector.method = methodName;

    try
    {
      var postData;

      // Specify the function that will handle the HTTP response
      connector.request.onreadystatechange = function() {__digifiss__callback(connector);};

  		connector.request.open("post", servicePath, true);

      // Set the Content-Type header for a POST request
      connector.request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
      postData = "method=" + methodName + "&argCount=" + args.length;

      for(x = 0; x < args.length; x++)
      {
        postData += "&arg" + x + "=" + escape(args[x]);
      }

      connector.request.send(postData);
    }
    catch (errv)
    {
      onFailure("The application cannot contact the server at the moment. "
         + "Please try again in a few seconds.\n" +
         "Error detail: " + errv.message, context, methodName);
    }
  }
}

function __digifiss__callback(connector)
{
  if(connector.request.readyState == 4)
  {
    try
    {
      var xml = XML.parse(connector.request.responseText);
      var startNode = 0;

      if(xml.childNodes.length > 1) startNode = 1;

      if(xml.childNodes[startNode].nodeName != 'digifiss')
      {
        connector.onFailure('Invalid XML data encountered', connector.context, connector.method);
      }
      else
      {
        var status = xml.childNodes[startNode].getElementsByTagName('status');
        var result = xml.childNodes[startNode].getElementsByTagName('result');

        if(!status || status.length == 0)
        {
          connector.onFailure('Invalid XML data encountered (status not found)', connector.context, connector.method);
        }
        else if(!result || result.length == 0)
        {
          connector.onFailure('Invalid XML data encountered (result not found)', connector.context, connector.method);
        }
        else
        {
          var statusText = status[0].textContent;
          if(!statusText) statusText = status[0].text;

          var resultText = result[0].textContent;
          if(!resultText) resultText = result[0].text;

          switch(statusText)
          {
            case '0':
              connector.onFailure(resultText, connector.context, connector.method);
            break;
            case '1':
              connector.onSuccess(resultText, connector.context, connector.method);
            break;
            default:
              connector.onFailure('Invalid status encountered (' + statusText + ')', connector.context, connector.method);
            break;
          }
        }
      }

      connector.busy = false;
    }
    catch(ex)
    {
      if(!connector) alert('COnnector is null?');
      connector.onFailure('An error has occurred: ' + ex.message, connector.context, connector.method);
      connector.busy = false;
    }
  }
}

function __digifiss__connector()
{
  this.busy = false;
  if (window.XMLHttpRequest)
  {
    this.request = new XMLHttpRequest();
  }
  else if (window.ActiveXObject)
  {
    this.request = new ActiveXObject("Msxml2.XMLHTTP");
    if (!this.request)
    {
      this.request = new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
}

function __digifiss__getavailableconnector()
{
  for(x = 0; x < __digifiss__maxConcurrent; x++)
  {
    if(!__digifiss__connectors[x])
    {
      var connector = new __digifiss__connector();

      __digifiss__connectors[x] = connector;

      return connector;
    }
    else if(!__digifiss__connectors[x].busy)
    {
      //I originally tried to re-use connectors, but it cause strange
      //problems in IE:
      __digifiss__connectors[x] = new __digifiss__connector();
      return __digifiss__connectors[x];
    }
  }

  return null;
}

var XML = new function() {};

/**
 * Create a new Document object. If no arguments are specified,
 * the document will be empty. If a root tag is specified, the document
 * will contain that single root tag. If the root tag has a namespace
 * prefix, the second argument must specify the URL that identifies the
 *namespace.
 */
XML.newDocument = function(rootTagName, namespaceURL) {
    if (!rootTagName) rootTagName = "";
    if (!namespaceURL) namespaceURL = "";

    if (document.implementation && document.implementation.createDocument) {
        // This is the W3C standard way to do it
        return document.implementation.createDocument(namespaceURL,
                       rootTagName, null);
    }
    else { // This is the IE way to do it
        // Create an empty document as an ActiveX object
        // If there is no root element, this is all we have to do
        var doc = new ActiveXObject("MSXML2.DOMDocument");

        // If there is a root tag, initialize the document
        if (rootTagName) {
            // Look for a namespace prefix
            var prefix = "";
            var tagname = rootTagName;
            var p = rootTagName.indexOf(':');
            if (p != -1) {
                prefix = rootTagName.substring(0, p);
                tagname = rootTagName.substring(p+1);
            }

            // If we have a namespace, we must have a namespace prefix
            // If we don't have a namespace, we discard any prefix
            if (namespaceURL) {
                if (!prefix) prefix = "a0"; // What Firefox uses
            }
            else prefix = "";

            // Create the root element (with optional namespace) as a
            // string of text
            var text = "<" + (prefix?(prefix+":"):"") + tagname +
                (namespaceURL
                 ?(" xmlns:" + prefix + '="' + namespaceURL +'"')
                 :"") +
                "/>";
            // And parse that text into the empty document
            doc.loadXML(text);
        }
        return doc;
    }
};


/**
 * Parse the XML document contained in the string argument and return
 * a Document object that represents it.
 */
XML.parse = function(text) {
    if (typeof DOMParser != "undefined") {
        // Mozilla, Firefox, and related browsers
        return (new DOMParser()).parseFromString(text, "application/xml");
    }
    else if (typeof ActiveXObject != "undefined") {
        // Internet Explorer.
        var doc = XML.newDocument( );   // Create an empty document
        doc.loadXML(text);              //  Parse text into it
        return doc;                     // Return it
    }
    else {
        // As a last resort, try loading the document from a data: URL
        // This is supposed to work in Safari. Thanks to Manos Batsis and
        // his Sarissa library (sarissa.sourceforge.net) for this technique.
        var url = "data:text/xml;charset=utf-8," + encodeURIComponent(text);
        var request = new XMLHttpRequest();
        request.open("GET", url, false);
        request.send(null);
        return request.responseXML;
    }
};