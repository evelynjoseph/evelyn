<?php

// Liberty Tech Framework

// --

// Array functions

function def(&$_,$a,$b=null){isset($_)?isset($b)&&($_=$a):$b&&($_=$b)||$_=$a;}
function arp($a,$k,$d=''){return isset($a[$k])?$a[$k]:$d;}

function pluck($array, $k, $r = array())
{
	foreach ($array as $v) $r[] = $v[$k];
	return $r;
}

function arrayFormat($array, $return_array = false, $r = array())
{
	foreach ($array as $i) $r[reset($i)] = $return_array ? $i : next($i);
	return $r;
}

function acount($array, $k, $v, $r = 0) { foreach ($array as $i) $r += (int) (arp($i, $k) == $v); return $r; }
function asum  ($array, $k,     $r = 0) { foreach ($array as $i) $r += arp($i, $k); return $r; }

// String functions

function plural($count, $item, $plural = 's', $singular = '')
{
	return "$count $item" . ($count == 1 ? $singular : $plural);
}

function limit($text, $length = 50)
{
	$text = preg_replace('/\<[^\>]+\>/', ' ', $text);
	$text = preg_replace('/[\xC2\xA0]/', '', $text);
	$text = preg_replace('/&nbsp;/', ' ', $text);
	$text = str_replace('£', '&pound;', $text);
	$text = trim($text);
	
	if (strlen($text) < $length) return $text;
	
	$return = preg_replace('/ ?[^ ]*$/', '', substr($text, 0, $length)) . '...';
	if (strlen($return) < $length - 10) $return = substr($text, 0, $length) . '...';
	return $return;
}

// Other functions

function redirect($target = '')
{
	global $page, $url;
	if ($target == '') $target = $url;
	header("Location: $target");
	$page->_exit();
}

function mailBySMTP($to, $nameto, $from, $message, $subject, array $attachments = array())
{
	global $mail_settings;
	
	# Temp override
	# return mail($to, $subject, $message);
	
	require_once('classes/plugins/PHPMailer/phpmailer.inc');
	require_once('classes/plugins/PHPMailer/smtp.inc');
	
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->CharSet  = 'UTF-8';
	$mail->SMTPAuth = true;
	$mail->Host 	= $mail_settings['host'];
	$mail->Username = $mail_settings['user'];
	$mail->Password = $mail_settings['pass'];
	$mail->From 	= $mail_settings['user'];
	$mail->FromName = $from;
	
	if (is_array($to) && count($to) == count($nameto))
		foreach ($to as $k => $v)
			$mail->AddAddress($to[$k], $nameto[$k]);
	else
		$mail->AddAddress($to, $nameto);
		
	$mail->WordWrap = 50;
	$mail->IsHTML(true);
	$mail->Subject 	= $subject;
	$mail->Body 	= $message;
	$mail->AltBody 	= strip_tags($message);
	foreach ($attachments as $attachment) $mail->AddAttachment($attachment);
	if (!$mail->Send())
	{
		trigger_error('Error sending email to: ' . $to);
		return false;
	}
	return true;
}

function geoLocate($address)
{
	$url = sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&region=uk', urlencode($address));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$raw_response = curl_exec($ch);
	curl_close($ch);
	$response = json_decode($raw_response, true);
	return $response['status'] == 'OK' ? array($response['results'][0]['geometry']['location']['lat'], $response['results'][0]['geometry']['location']['lng']) : array(0, 0);
}