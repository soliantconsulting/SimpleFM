<?

/**
 * HISTORY:
 * jsmall 2008-02-10 FMProxy class for FileMaker Server. Written for use with FlexFM, but built generically to be compatible with any technology.
 * jsmall 2008-05-19 v0.2 updated for PHP 5. This version will not work with implementations using the original FMProxy
 * jsmall/charmer 2009-12-10 v0.3 made constructor GET/POST dynamically configurable, updated doGet(), patched repackCommandString()
 */


/**
 * TODO: Finish move of all database functions to SimpleFMAdapter 2010-11-11 jsmall
 * SimpleFMProxy class should only deal with receiving the proxy $_POST request
 * The new thinking is that host, protocol, port and uri will no longer be settable from the proxy client
 */


class SimpleFMProxy {
	
	/**
    * @var string (The host name without protocol, port, path, etc.)
    * @example some.hostname.com
    */
	//public $host="localhost";
	
    /**
    * @var string (http or https)
    */
	//public $protocol="http";

	/**
    * @var string
    */
	//public $port="80";
	
	/**
    * @var string (valid uri for xml result grammar)
    */
	//public $fmiUri="/fmi/xml/fmresultset.xml";
	
	/**
    * @var string 
    */
	public $username="";

	/**
    * @var string 
    */
	public $password="";
	
	/**
    * @var string (The FileMaker CWP command string
    * @example -db=myFile&-lay=myLayout&-findany
    */
	private $commandString="";
	
	/**
    * @var array 
    */
	public $commandArray=array();
	
	/**
    * @var array 
    */
	public $proxyConfigArray=array();
	
	/**
    * @var array 
    */
	public $proxyParamsArray=array();
	
	/**
    * @var string 
    */
	public $result="";
	
	
	/**
    * Constructor
    * @return void
    */
	function __construct($method='POST'){
	
		$_method = '_'.$method;
		global ${$_method};
		
		if (@${$_method}['Host']!=null){
			$this->host = ${$_method}['Host'];
		}
		if (@${$_method}['Protocol']!=null){
			$this->protocol = ${$_method}['Protocol'];
		}
		if (@${$_method}['Port']!=null){
			$this->port = ${$_method}['Port'];
		}
		if (@${$_method}['FmiUri']!=null){
			$this->fmiUri = ${$_method}['FmiUri'];
		}
		if (@${$_method}['Username']!=null){
			$this->username = ${$_method}['Username'];
		}
		if (@${$_method}['Password']!=null){
			$this->password = ${$_method}['Password'];
		}
		if (@${$_method}['CommandString']!=null){
			$this->commandString = ${$_method}['CommandString'];
			$this->commandArray = $this->explodeNameValueString(${$_method}['CommandString']);
		}
		if (@${$_method}['ProxyConfig']!=null){
			$this->proxyConfigArray = $this->explodeNameValueString(${$_method}['ProxyConfig']);
		}
		if (@${$_method}['ProxyParams']!=null){
			$this->proxyParamsArray = $this->explodeNameValueString(${$_method}['ProxyParams']);
		}
		
		if ($_method == '_GET'){
			$theArgs = $this->explodeNameValueString($_GET);
			$this->commandArray = $theArgs[0];
		}
	}
    
	
    /**
    * @param string $string Parses parses a generic string of name=value pairs into an associative array
    * @access private
    * @return array
    */
    /* moved to SimpleFMAdapter
	private function explodeNameValueString($string){
		$_array=explode('&',$string);
		if (count($_array)<2){
			$_nameValue=explode('=',$_array[0],2);
			if (count($_nameValue)<2) {
				$resultArray = array($string);
			}else {
				$resultArray = array($_nameValue[0] => $_nameValue[1]);
			}
		} else {
		foreach ($_array as $_item) {
			$_nameValue=explode('=',$_item,2);
			$_name = $_nameValue[0];
			$_value = @$_nameValue[1];
			$resultArray[$_name]= $_value;
			}
		}
		return $resultArray;
	}
	*/
	
	/**
    * @access private
    * @return void
    */
    /* moved to SimpleFMAdapter
	private function repackCommandString(){
		// repack the command array into a command string.
		$amp = "";
		$this->commandString = "";
		foreach ($this->commandArray as $name=>$value){
		    $this->commandString .= $value===null?$amp.$name:$amp.$name.'='.urlencode($value);
    		$amp = '&';
		}
	}
	*/
	
	/**
    * @access public
    * @return void
    */
    /* deprecated. Use SimpleFMAdapter version
	public function execute(){
		$this->repackCommandString();
		//$this->result = $this->doPost();
		$this->result = $this->doGet();
	}
	*/
	
// This function is deprecated. Can't think of a reason to use GET. Just keeping it around for reference.
	/**
    * @access private
    * @return string
    * @abstract pume
    */
    /* moved to SimpleFMAdapter
	private function doGet() {
		$credentials = empty($this->username)?'':$this->username.':'.$this->password.'@';
		//$commandURL = urldecode("http://".$credentials. $this->host. ":". $this->port. $this->fmiUri . "?" . $this->commandString);
		$commandURL = "http://".$credentials. $this->host. ":". $this->port. $this->fmiUri . "?" . $this->commandString;
		$xml = @file_get_contents($commandURL);
		if (empty($xml)) {
			return "get error: NULL result";
		}
		return $xml;
	}
	*/


	/**
    * @access private
    * @return string
    */
    /* moved to SimpleFMAdapter
	private function doPost () {
		
		$post  = "POST $this->fmiUri HTTP/1.0\r\n";
        $post .= "Host: localhost\r\n";
        $post .= "User-Agent: Soliant-FMProxy\r\n";
        if (!(empty($this->username) && empty($this->password)) ) {
           $post .= "Authorization: Basic ".base64_encode($this->username.":".$this->password)."\r\n";
        }
        $post .= "Accept: text/xml,text/html,text/plain\r\n";
        $post .= "Content-type: application/x-www-form-urlencoded\r\n";
        $post .= "Content-length: " . strlen($this->commandString) . "\r\n";
        $post .= "\r\n";
        $post .= "$this->commandString";
		
        $response = "";
		$fp = @fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if ($fp === FALSE) {
            return "fsockopen error: " . $errno."/".$errstr;
        }
        $init = fwrite($fp, $post);
        while (!feof($fp)) {
            $response .= fgets($fp, 1024);
        }
        fclose($fp);
        $xml = strstr($response,"<?xml version");
        return $xml;
	}
	*/
}
