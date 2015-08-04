<?php

// Liberty Tech Framework - v2.3
// Error Class

class Error
{
	// -- Variables
	
	private $siteName;
	private $email;
	
	// -- Config
	
	private $error_count = 0;
	private $error_message = '';
	
	// -- Functions
	
	public function __construct($email = 'logging@libertytech.com')
	{
		$this->siteName = $_SERVER['HTTP_HOST'];
		$this->email = $email;
		
		set_error_handler(array($this, 'errorHandler'), error_reporting());
		ob_start(array($this, 'bufferCallback'));
	}
	
	public function setEmail() {} // Backwards compatibility
	
	public function trigger($message)
	{
		mail($this->email, "{$this->siteName}:Log - Custom Message", $message);
	}
	
	public function bufferCallback($buffer)
	{
		global $error, $arrURL, $email_errors;
		
		$message = $this->error_message;
		
		if (isset($_SESSION)) 	$message .= "SESSION:\n" . $this->pr($_SESSION) . "\n";
		if (isset($_POST)) 	$message .= "POST:\n"    . $this->pr($_POST)    . "\n";
		if (isset($_GET)) 	$message .= "GET:\n"     . $this->pr($_GET)    . "\n";
		if (isset($arrURL)) 	$message .= "URL:\n"     . $this->pr($arrURL)   . "\n";
		if (isset($_SERVER['HTTP_REFERER'])) $message .= "Referer: {$_SERVER['HTTP_REFERER']}\n";
		
		if ($this->error_count > 0) 
		{
			if (!isset($email_errors) || $email_errors)
				mail($this->email, "{$this->siteName}:Error - ({$this->error_count})", $message);
		}
		
		return $buffer;
	}
	
	public function errorHandler($code, $msg, $file, $line)
	{
		switch($code)
		{
			case E_WARNING: // 2 //
			case E_USER_WARNING: // 512 //
				$error_code = 'PHP Warning';
				break;
				
			case E_NOTICE: // 8 //
			case E_USER_NOTICE: // 1024 //
			case E_STRICT: // 2048 //
				$error_code = 'PHP Notice';
				break;
				
			case E_RECOVERABLE_ERROR: // 4096 //
				$error_code = 'Catchable error';
				break;
				
			case E_DEPRECATED: // 8192 //
			case E_USER_DEPRECATED: // 16384 //
				$error_code = 'PHP Deprecated error';
				break;
				
			case E_USER_ERROR: // 256 //
				$error_code = 'PHP Fatal Error';
			default:
				$error_code = "Error code $code:";
		}
		error_log("$error_code: $msg in $file on line $line", 0);
		
		$this->error_count++;
		$message = <<<MESSAGE
{$this->siteName} Error #{$this->error_count}:
Error code:\t\t{$code}
Error message:\t\t{$msg}
File:\t\t\t{$file}
Line:\t\t\t{$line}\n\n
MESSAGE;
		$this->error_message .= $message . "--- --- --- \n\n";
		
		return true;
	}
	
	public function pr($array, $count = 0)
	{
		$return = '';
		foreach ($array as $k => $v)
		{
			$return .= str_repeat(' ', $count * 4) . '[' . $k . '] => ';
			if (is_array($v)) $return .= "array(\n" . $this->pr($v, $count + 2) . str_repeat(' ', $count * 4) . "),\n";
			else $return .= "$v\n";
		}
		
		return $return;
	}

}
