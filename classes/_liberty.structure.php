<?php

// Liberty Tech Framework

class Page
{
	const PATH = 'pages/';
	
	protected $title    = '';
	protected $class    = '';
	protected $header   = '';
	protected $footer   = '';
	protected $page_start_time;
	protected $content_alteration;
	
	protected $CSS     = array();
	protected $JS      = array();
	protected $scripts = array();
	protected $meta    = array();
	
	protected $ob_ended = false;
	
	public function __construct()
	{
		$this->page_start_time = microtime(true);
		
		ob_start('ob_gzhandler');
		ob_start(array($this, 'writePage'));
	}
	
	public function addCSS($file)
	{
		if (!preg_match('/^http\:\/\//', $file) && file_exists('css/' . $file)) $file = '/css/' . $file . '?v=' . substr(sha1_file('css/' . $file), 5, 5);
		foreach ($this->CSS as $css) if ($css == $file) return $this;
		$this->CSS[] = $file;
		return $this;
	}
	
	public function addJS($file)
	{
		if (!preg_match('/^http\:\/\//', $file) && file_exists('javascript/' . $file)) $file = '/javascript/' . $file . '?v=' . substr(sha1_file('javascript/' . $file), 5, 5);
		foreach ($this->JS as $js) if ($js == $file) return $this;
		$this->JS[] = $file;
		return $this;
	}
	
	public function addScript($script, $trim = true)
	{
		if ($trim)
		{
			$script = str_replace("\r", '', $script);
			$script = explode("\n", $script);
			foreach ($script as &$l)
			{
				$l = preg_replace('/^\s*\/\/.*$/i', '', $l);
				$l = preg_replace('/\/\/[ a-z0-9\-]*$/i', '', $l);
				$l = preg_replace('/^\s+/i', ' ', $l);
				if (strpos($l, '//') !== false) $l .= "\n";
			} unset($l);
			$script = implode('', $script);
		}
		$this->scripts[] = $script;
		return $this;
	}
	
	public function setMeta($name, $content) { $this->meta[$name] = $content; return $this; }
	public function setTitle($title) { $this->title = $title; return $this; }
	public function setClass($class) { $this->class = $class; return $this; }
	
	public function setHeader($file = 'includes/header.php')
	{
		global $arrURL;
		
		if (!file_exists($file)) return;
		
		$tags = "<title>$this->title</title>\n";
		foreach ($this->meta as $k => $v) $tags .= "\t<meta name=\"$k\" content=\"$v\">\n";
		foreach ($this->CSS as $v)
		{
			$media = (strstr($v, 'print') !== false) ? ' media="print"' : '';
			$tags .= "\t<link rel=\"stylesheet\" href=\"$v\"$media>\n";
		}
		$tags = rtrim($tags, "\n");
		
		if ($this->class)
			$class = $this->class;
		else
		{
			$class = 'pg-' . $arrURL[1];
			if ($arrURL[2]) $class .= " pg-$arrURL[1]-$arrURL[2]";
			$class = preg_replace('/[^a-z0-9\- ]/i', '', $class);
		}
		
		$this->header = require($file);
	}
	
	public function setFooter($file = 'includes/footer.php')
	{
		if (!file_exists($file)) return;
		
		$js = '';
		foreach ($this->JS as $v) $js .= "<script src=\"$v\"></script>\n";
		if (count($this->scripts) != 0) $js .= '<script>' . implode("\n", $this->scripts) . '</script>';
		$js = rtrim($js, "\n");
		
		$this->footer = require($file);
	}
	
	public function writePage($display)
	{
		global $url;
		if (isset($this->content_alteration))
		{
			$f = $this->content_alteration;
			$display = $f($display);
		}
		$display = $this->header . $display . $this->footer;
		$_SESSION['load_time_last_total'] = round(microtime(true) - $this->page_start_time, 4);
		$_SESSION['load_time_last_page'] = $url;
		return $display;
	}
	
	public function setContentAlteration($function)
	{
		$this->content_alteration = $function;
		return $this;
	}
	
	public function printDebug()
	{
		$_SESSION['load_time'] = round(microtime(true) - $this->page_start_time, 4);
		$this->addScript('console.log(\'' . preg_replace('/\n/', "\\\\n\\\\\n", addslashes(preg_replace('/\r/', '', print_r($_SESSION, true)))) . '\');' . "\n", false);
	}
	
	public function pageError($error)
	{
		ob_clean();
		require(self::PATH . 'error.php');
		exit;
	}
	
	public function output($content = '')
	{
		if (!$this->ob_ended) ob_end_clean();
		echo $content;
		exit;
	}
	
	public function stopOB()
	{
		$this->ob_ended = true;
		ob_end_clean();
		return $this;
	}
	
	public function _exit() 		{ $this->output(); }
	public function _die($content = '') 	{ $this->output($content); }
}
