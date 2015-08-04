<?php

// Liberty Tech: Database Class v2.1

/*
 * -- Example usage:
 *
 * $db->sql("SELECT * FROM `table` WHERE `id` = %d AND `label` = '%s';", array($id, $label), 1);
 * $results = $db->rows();
 * 
 * $db->sql("UPDATE `table` SET `label` = '%s' AND `text` = '%s' WHERE `id` = %d;");
 * $db->data(array($label, $text, $id), 1);
 * $db->query();
 * 
 * $db->sql("UPDATE `table` SET `label` = '%s' AND `text` = '%s' WHERE `id` = %d;", array($label, $text, $id), 1)->query();
 * 
 * $results = $db->sql("SELECT * FROM `table` WHERE `id` = %d;", $id)->rows();
 * 
 */

class Db
{
	private $host = '';
	private $database = '';
	private $username = '';
	private $password = '';
	private $connected = false;
	
	private $sql;
	private $data;
	private $connection;
	private $result;
	private $purifier;
	
	public function __construct($host = 'localhost')
	{
		$this->host = $host;
		unset($_SESSION['db']['query']);
		unset($_SESSION['db']['error']);
		unset($_SESSION['db']['times']);
	}
	
	public function __destruct() { if ($this->connected) mysql_close($this->connection); }
	
	// -- Public
	
	public function connect()
	{
		if ($this->connected) return;
		$this->connection = mysql_connect($this->host, $this->username, $this->password);
		mysql_set_charset('utf8', $this->connection);
		mysql_select_db($this->database, $this->connection);
		$this->connected = true;
	}
	
	public function setLogin($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		return $this;
	}
	
	public function setDB($db)
	{
		$this->database = $db;
		return $this;
	}
	
	public function sql($sql, $data = '', $html = array(), $safe = true)
	{
		$this->sql = $sql;
		$this->data($data, $html, $safe);
		return $this;
	}
	
	public function data($data = '', $html = array(), $safe = true)
	{
		$this->connect();
		$this->result = null;
		
		if (!is_array($data)) $data = array($data);
		if (!is_array($html)) $html = array($html);
		
		foreach ($data as $k => &$d)
		{
			if ($safe)
				$d = $this->purify($d, in_array($k, $html));
			$d = mysql_real_escape_string($d);
		}
		unset($d);
		
		$this->data = $data;
		return $this;
	}
	
	public function purify($data, $html = false)
	{
		$data = str_replace('&nbsp;', ' ', $data);
		
		if ($html)
		{
			if (!isset($this->purifier))
			{
				require 'classes/plugins/HTMLPurifier/HTMLPurifier.auto.php';
				$this->purifier = new HTMLPurifier();
			}
			
			$data = $this->purifier->purify($data);
		}
		else
		{
			$data = htmlentities($data, ENT_COMPAT, 'UTF-8', false);
		}
		
		$data = trim($data);
		return $data;
	}
	
	public function display()
	{
		$this->connect();
		vprintf($this->sql, $this->data);
	}
	
	public function query()
	{
		$this->connect();
		
		$sql = vsprintf($this->sql, $this->data);
		
		$start_time = microtime(true);
		$this->result = mysql_query($sql, $this->connection);
		$end_time = microtime(true);
		
		$error = $this->error();
		$_SESSION['db']['query'][] = substr(str_replace(array("\r", "\n", "\t"), ' ', trim($sql)), 0, 60);
		$_SESSION['db']['error'][] = $error == '' ? $this->count() . ' rows.' : $error;
		$_SESSION['db']['times'][] = round($end_time - $start_time, 4) . ' seconds';
		if ($error != '') @trigger_error('MySQL error: ' . $error);
		
		return $this;
	}
	
	public function id() 		{ return mysql_insert_id($this->connection); }
	public function error() 	{ return mysql_error($this->connection); }
	public function result() 	{ return $this->result; }
	
	public function rows($col = null)
	{
		if (!isset($this->result)) $this->query();
		
		$return = array();
		if ($this->count() == 0) return $return;
		while ($data = mysql_fetch_assoc($this->result)) $return[] = $data;
		if ($col) return pluck($return, $col);
		return $return;
	}
	
	public function row($col = null)
	{
		if (!isset($this->result)) $this->query();
		
		if ($this->count() == 0) return ($col == null) ? array() : '';
		$result = mysql_fetch_assoc($this->result);
		
		if ($col != null) return $result[$col];
		return $result;
	}
	
	public function count()
	{
		if (!isset($this->result) || $this->result === false) return -1;
		if ($this->result === true) return 0;
		return mysql_num_rows($this->result);
	}
}