<?php
/**
 * toKernel- Universal PHP Framework.
 * Class library for sending email
 * 
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.2.5
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * email_lib class
 * 
 * Send email using php, sendmail and SMTP methods.  
 *  
 * @author Arshak Ghazaryan <khazaryan@gmail.com>
 */
class email_lib {

 /**
  * Email functionality configuration
  *
  * @access public
  * @var array
  */
 public $config = array();

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
 	$this->lib = lib::instance();
 }
 
/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
	unset($this->config);
 }
	 
 /**
  * Set Email configuration
  *
  * The configuration can be passed an array of config values
  *
  * @access	public
  * @param	mixed $config
  * @return	void
  */
  public function configure($config = array()) {
	
	// Sending protocol (phpmail,sendmail or smtp). By default protocol is phpmail
  	if (!isset($config['protocol'])) {
		$config['protocol'] = 'phpmail';
	} elseif ($config['protocol']!='smtp' and $config['protocol']!='sendmail') {
		$config['protocol'] = 'phpmail';
	}
	
	// Email priority. By default priority is 3. Can use (1 - 5)
	if (!isset($config['priority'])) {
		$config['priority'] = 3;
	} elseif (!$config['priority'] and $config['priority'] > 5 and $config['priority']<=0) {
		$config['priority'] = 3;
	}
	
	// Default charset
	if (!isset($config['charset'])) {
		$config['charset'] = 'UTF-8';
	} elseif (!in_array(strtoupper($config['charset']), mb_list_encodings())) {
		$config['charset'] = 'UTF-8';
	}
	
	// Set newline
	if (!isset($config['newline'])) {
		$config['newline'] = "\n";
	} elseif ($config['newline'] != "\n" and $config['newline'] != "\r\n" and $config['newline'] != "\r") {
		$config['newline']	= "\n";
	}
	
	// Set crlf. The RFC 2045 compliant CRLF for quoted-printable is "\r\n".
	if (!isset($config['crlf'])) {
		$config['crlf'] = "\n";
	} elseif ($config['crlf'] != "\n" and $config['crlf'] != "\r\n" and $config['crlf'] != "\r") {
		$config['crlf'] = "\n";
	}
  	
	// Validating email address
	if (!isset($config['validate'])) {
		$config['validate'] = false;
	} elseif($config['validate'] != true) {
		$config['validate'] = false;
	}
	
	// Multipart email sending
  	if (!isset($config['send_multipart'])) {
		$config['send_multipart'] = true;
	} elseif($config['send_multipart'] !== false) {
		$config['send_multipart'] = true;
	}
	
	// sendmail path 
	if ($config['protocol'] == 'sendmail') {
		if (!isset($config['sendmail_path'])) {
			$config['sendmail_path'] = "/usr/sbin/sendmail";
		}
	}
	
	// SMTP Server configurations 
	if ($config['protocol'] == 'smtp') {
		if (!isset($config['host'])) {
			$config['host'] = "localhost";
			$this->config['message'][] = 'Sendmail host '.$config['host'];		
		}
		
		if (!isset($config['port'])) {
			$config['port'] = 25;

		}
	}
 	
	$config['mailtype']     = 'text';
	$config['recipients']   = '';
	$config['body']         = '';		
	$config['error']        = false;
	$config['cc']           = '';
	$config['multipart']    =  "mixed";	
	$config['attachment']   = array();
	$config['email_header'] = '';
	$config['message_date'] = date("D, d M Y H:i:s O");
	$config['alt_boundary'] = "B_ALT_".uniqid();
	$config['atc_boundary'] = "B_ATC_".uniqid();
	$config['mime_message'] = "This is a multi-part message in MIME format.".$config['newline'].
													"Your email application may not support this format.";
	$this->config = $config;
  }
	
  /**
	* Set FROM
	*
	* @access	public
	* @param	string
	* @param	string
	* @return	bool
	*/
	public function from($from, $name = '')	{
		if (!is_array($this->config)) {
			$this->config['error'] = true;
			$this->set_message('Error : Email not configured');	
			return false;
		}
		
		if ($this->config['validate']) {
			
			if ($this->valid_email($from)=== false) {
				$this->config['error'] = true;
				$this->set_message('Error : From  Email is not correct');	
				return false;
			}

		}

		$this->set_email_header('From', $this->q_encode($name).' <'.$from.'>');
		$this->set_email_header('Return-Path', '<'.$from.'>');
		
		return true;		
	}
  
  /**
	* Add Header 
	* 
	* @access	protected
	* @param	string
	* @param	string	
	* @return	void
	*/
 	protected function set_email_header($header, $value) {
		$this->config['email_headers'][$header] = $value;
 	}
	
  /**
	* Set Recipients
	*
	* @access	public
	* @param	string
	* @return	bool
	*/
	public function to($to) {
		
		if (!is_array($this->config)) {
			$this->config['error'] = true;
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if (isset($this->config['error']) and $this->config['error'] == true) {
			return false;
		}
		
		if (!is_array($to)) {
			settype($to, "array");
		}
		
		if ($this->config['validate']) {
			if (($to = $this->validate_emails($to))===false) {
				$this->config['error'] = true;
				$this->set_message('Error : Recipient address not correctly');
				return false;
			}
		}

		if ($this->config['protocol'] != 'phpmail') {
			$this->set_email_header('To', implode(", ", $to));
		}

		if ($this->config['protocol'] == 'smtp') {
			$this->config['recipients'] = $to;
		} elseif ($this->config['protocol'] == 'phpmail' or $this->config['protocol'] == 'sendmail') {
			$this->config['recipients'] = implode(", ", $to);
		}
		
		return true;	
	}

   /**
	 * Email Array Validation
	 *
	 * @access	protected
	 * @param	array
	 * @return	array
	 */	
	 protected function validate_emails($emails) {
	 	if (!is_array($emails)) {
	 		return false;
	 	}
	 	$valid_emails = array();
	 	foreach ($emails as $val) {
	 		if($this->valid_email($val)===true) {
	 			$valid_emails[] = $val;
	 		}
	 	}
	 	if (count($valid_emails)>0) {
	 		return $valid_emails;
	 	} else {
	 		return false;
	 	}
	 }
   
  /**
	* Email Validation
	*
	* @access	protected
	* @param	string
	* @return	bool
	*/
 	protected function valid_email($email) {
		if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
			return false;
		} else {
			return true;	
		}
	}

  /**
	* Set CC
	*
	* @access	public
	* @param	string
	* @return	bool
	*/
	public function cc($cc)	{
		if (!is_array($this->config)) {
			$this->config['error'] = true;			
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if ($this->config['error'] == true) {
			return false;
		}
		
		if (!is_array($cc)) {
			settype($cc, "array");
		}

		if ($this->config['validate']) {
			if (($cc = $this->validate_emails($cc))===false) {
				$this->set_message('Error : Carbon copy not correctly');
				return false;
			}
		}
		
		$this->set_email_header('Cc', implode(", ", $cc));

		if ($this->config['protocol'] == 'smtp') {
			$this->config['cc'] = $cc;
		}
		
		return true;
	}

  /**
	* Set Email Subject
	*
	* @access	public
	* @param	string
	* @return	bool
	*/
	public function subject($subject) {
		if (!is_array($this->config)) {
			$this->config['error']=true;			
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if ($this->config['error']==true) {
			return false;
		}
		
		$this->set_email_header('Subject', $this->q_encode($subject));
		
		return true;		
	}


  /**
	* Encode text to Q encode
	*
	* @access	protected
	* @param	string
	* @return	string
	*/
	protected function q_encode($str) {
		if (!$str) {
			return false;	
		}
		
		$str = str_replace(array("\r", "\n"), array('', ''), $str);
		$limit = 75 - 7 - strlen($this->config['charset']);

		$convert = array('_', '=', '?');

		$output = '';
		$temp = '';

		for ($i = 0, $length = strlen($str); $i < $length; $i++) {
			$char = substr($str, $i, 1);
			$ascii = ord($char);

			if ($ascii < 32 or $ascii > 126 or in_array($char, $convert)) {
				$char = '='.dechex($ascii);
			}

			if ($ascii == 32) {
				$char = '_';
			}

			if ((strlen($temp) + strlen($char)) >= $limit) {
				$output .= $temp.$this->config['crlf'];
				$temp = '';
			}

			$temp .= $char;
		}

		$str = $output.$temp;

		return trim(preg_replace('/^(.*)$/m', ' =?'.$this->config['charset'].'?Q?$1?=', $str));

	}

  /**
	* Get the Message ID
	*
	* @access	prorected
	* @return	string
	*/
	protected function message_id() {
		return  "<".uniqid('').strstr($this->config['email_headers']['Return-Path'], '@');
	}

  /**
	* Assign file attachments
	*
	* @access	public
	* @param	string
	* @param	string	
	* @return	bool
	*/
	public function attach($filename, $disposition = 'attachment')	{
		if (!is_array($this->config)) {
			$this->config['error'] = true;			
			$this->set_message('Error : Email not configured');			
			return false;
		}
		
		if (!is_file($filename) or !is_readable($filename)) {
			$this->config['error'] = true;
			$this->set_message('Error : File not exist or not readable for attach.');
			return false;
		}
		
		if ($this->config['error'] == true) {
			return false;
		}
		
		$this->config['attachment'][] = array('filename' =>$filename,
		 	'type' =>$this->get_mime_type(strtolower(substr($filename, strrpos($filename, '.')+1, strlen($filename)))), 
		 	'disp'=>$disposition);
		
		return true;		
	}

  /**
	* Build final headers
	*
	* @access	protected
	* @return	void
	*/
	protected function final_headers() {
		if (!isset($this->config['email_headers']['From'])) {
			$this->set_message('Error : From not exist in email');
			$this->config['error'] = true;
			return false;
		}
		
		$this->set_email_header('X-Sender', $this->config['email_headers']['From']);
		$this->set_email_header('X-Mailer', 'toKernel');
		$this->set_email_header('X-Priority', $this->config['priority']);
		$this->set_email_header('Message-ID', $this->message_id());
		$this->set_email_header('Mime-Version', '1.0');
	}

  /**
	* Get content type (text/html/attachment)
	*
	* @access	protected
	* @return	string
	*/
	protected function content_type() {
		if	($this->config['mailtype'] == 'html' and  count($this->config['attachment']) == 0) {
			return 'html';
		} elseif ($this->config['mailtype'] == 'html' and  count($this->config['attachment'])  > 0) {
			return 'html-attach';
		} elseif ($this->config['mailtype'] == 'text' and  count($this->config['attachment'])  > 0) {
			return 'plain-attach';
		} else {
			return 'plain';
		}
	}

  /**
	* Set Email Body (message)
	*
	* @access	public
	* @param	string
	* @return	bool
	*/
	public function message($body) {
		if (!is_array($this->config)) {
			$this->config['error']=true;
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if ($this->config['error']==true) {
			return false;
		}		
		
		$this->config['body'] = stripslashes(rtrim(str_replace("\r", "", $body)));
		return true;	
	}
	
  /**
	* Set Html Email
	*
	* @access	public
	* @param	string
	* @return	bool
	*/
	public function html($html) {
		if (!is_array($this->config)) {
			$this->config['error']=true;
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if ($this->config['error']==true) {
			return false;
		}

	   	$this->config['mailtype'] = 'html';
		$this->message($html);
		return true;
	}
	
  /**
	* Write Headers as a string
	*
	* @access	protected
	* @return	void
	*/
	protected function set_headers() {
		if (!isset($this->config['email_headers']['Subject'])) {
			$this->config['error'] = true;
			$this->set_message('Error : Set email Subject');
			return false;
		}
		
		if ($this->config['protocol'] == 'phpmail') {
			$this->config['Subject'] = $this->config['email_headers']['Subject'];
			unset($this->config['email_headers']['Subject']);
		}

		foreach($this->config['email_headers'] as $key => $val) {
			if (trim($val) != "") {
				$this->config['email_header'].= $key.": ".$val.$this->config['newline'];
			}
		}

	}

  /**
	* Send Email
	*
	* @access	public
	* @return	bool
	*/
	public function send() {

		if (!is_array($this->config)) {
			$this->config['error'] = true;
			$this->set_message('Error : Email not configured');
			return false;
		}
		
		if ($this->config['error'] == true) {
			return false;
		}
		
		if (!isset($this->config['recipients'])) {
			$this->config['error'] = true;
			$this->set_message('Error : Recipient Address not exist');
			return false;
		}
		
		$this->set_email_header('User-Agent', 'toKernel');
		$this->set_email_header('Date', $this->config['message_date']);
		
		$this->final_headers();
		$this->set_headers();
	
		$hdr = '';
		$body = '';

		switch ($this->content_type()) {
			case 'plain' :

				$hdr .= "Content-Type: text/plain; charset=" . $this->config['charset'].$this->config['newline'];
				$hdr .= "Content-Transfer-Encoding: 8bit";
				
				if ($this->config['protocol'] == 'phpmail') {
					$this->config['email_header'].= $hdr;
					$this->config['finalbody'] = $this->config['body'];
				} else {
					$this->config['finalbody'] = $hdr . $this->config['newline'].$this->config['newline'].$this->config['body'];
				}

				break;
			case 'html' :

				if ($this->config['send_multipart'] === false) {
					$hdr .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['newline'];
					$hdr .= "Content-Transfer-Encoding: quoted-printable";
				} else {
					
					$hdr .= "Content-Type: multipart/alternative; boundary=\"" .$this->config['alt_boundary']. "\"" .
					$this->config['newline'].$this->config['newline'];

					$body .= $this->config['mime_message'].$this->config['newline'].$this->config['newline'];
					$body .= "--" . $this->config['alt_boundary'].$this->config['newline'];

					$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['newline'];
					$body .= "Content-Transfer-Encoding: 8bit".$this->config['newline'].$this->config['newline'];
					$body .= $this->config['newline'].$this->config['newline']."--".$this->config['alt_boundary'].
					$this->config['newline'];

					$body .= "Content-Type: text/html; charset=".$this->config['charset'].$this->config['newline'];
					$body .= "Content-Transfer-Encoding: quoted-printable".$this->config['newline'].$this->config['newline'];
				}

				$this->config['finalbody'] = $body.$this->q_printable($this->config['body']).$this->config['newline'].
				$this->config['newline'];


				if ($this->config['protocol'] == 'phpmail') {
					$this->config['email_header'].= $hdr;
				} else {
					$this->config['finalbody'] = $hdr.$this->config['finalbody'];
				}


				if ($this->config['send_multipart'] !== false) {
					$this->config['finalbody'].= "--".$this->config['alt_boundary']."--";
				}

				break;
			case 'plain-attach' :

				$hdr .= "Content-Type: multipart/".$this->config['multipart']."; boundary=\"".
				$this->config['atc_boundary']."\"".$this->config['newline'].$this->config['newline'];

				if ($this->config['protocol'] == 'phpmail') {
					$this->config['email_header'].= $hdr;
				}

				$body .= $this->config['mime_message'].$this->config['newline'].$this->config['newline'];
				$body .= "--" . $this->config['atc_boundary'].$this->config['newline'];

				$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['newline'];
				$body .= "Content-Transfer-Encoding: 8bit ".$this->config['newline'].$this->config['newline'];

				$body .= $this->config['body'].$this->config['newline'].$this->config['newline'];

				break;
			case 'html-attach' :

				$hdr .= "Content-Type: multipart/".$this->config['multipart']."; boundary=\"".
				$this->config['atc_boundary']."\"" . $this->config['newline'].$this->config['newline'];

				if ($this->config['protocol'] == 'phpmail') {
					$this->config['email_header'].= $hdr;
				}

				$body .= $this->config['mime_message'].$this->config['newline'].$this->config['newline'];
				$body .= "--" . $this->config['atc_boundary'].$this->config['newline'];

				$body .= "Content-Type: multipart/alternative; boundary=\"".$this->config['alt_boundary'].
				"\"".$this->config['newline'].$this->config['newline'];
				$body .= "--" . $this->config['alt_boundary'].$this->config['newline'];

				$body .= "Content-Type: text/plain; charset=".$this->config['charset'].$this->config['newline'];
				$body .= "Content-Transfer-Encoding: 8bit".$this->config['newline'].$this->config['newline'];
				$body .= $this->config['body'].$this->config['newline'].$this->config['newline']. "--" .
				$this->config['alt_boundary'].$this->config['newline'];

				$body .= "Content-Type: text/html; charset=".$this->config['charset'].$this->config['newline'];
				$body .= "Content-Transfer-Encoding: quoted-printable" . $this->config['newline'].$this->config['newline'];

				$body .= $this->q_printable($this->config['body']).$this->config['newline'].$this->config['newline'];
				$body .= "--" . $this->config['alt_boundary']."--".$this->config['newline'].$this->config['newline'];
				break;
		}


		if (count($this->config['attachment'])> 0) {
			$attachment = array();
			foreach ($this->config['attachment'] as $val) {
				
				$h  = "--".$this->config['atc_boundary'].$this->config['newline'];
				$h .= "Content-type: ".$val['type']."; ";
				$h .= "name=\"".basename($val['filename'])."\"".$this->config['newline'];
				$h .= "Content-Disposition: ".$val['disp'].";".$this->config['newline'];
				$h .= "Content-Transfer-Encoding: base64".$this->config['newline'];
	
				$attachment[] = $h;

				$fp = fopen($val['filename'], 'r');
				$attachment[] = chunk_split(base64_encode(fread($fp, (filesize($val['filename']) +1))));
				fclose($fp);
			}
			
			$body.= implode($this->config['newline'], $attachment).$this->config['newline']."--".
			$this->config['atc_boundary']."--";

			if ($this->config['protocol'] == 'phpmail') {
				$this->config['finalbody'] = $body;
			} else {
				$this->config['finalbody'] = $hdr.$body;
			}
		}
		
		if ($this->config['protocol'] == 'phpmail') {
			$this->phpmail();
		} elseif($this->config['protocol'] == 'sendmail') {
				$this->sendmail();
		} else {
			$this->smtp();
		}
		
		return true;
	}
	
  /**
	* Send using php mail() function 
	*
	* @access	protected
	* @return	bool
	*/
	protected function phpmail() {
		if ($this->config['error'] == true) {
			return false;
		}
		
		if (!mail($this->config['recipients'], $this->config['Subject'], 
		$this->config['finalbody'], $this->config['email_header'])) {
			$this->set_message('Send email failed');
			return false;
		} else {
			$this->set_message('Email sent successfully');
			return true;
		}
	}
	
  /**
	* Send Email using sendmail
	*
	* @access	protected
	* @return	bool
	*/
	protected function sendmail() {
		if ($this->config['error'] == true) {
			return false;
		}
		
		$fp = popen($this->config['sendmail_path']." -oi -f ".
		$this->only_email($this->config['email_headers']['From'])." -t", 'w');
		
		fputs($fp, $this->config['email_header']);
		fputs($fp, $this->config['finalbody']);

		$status = pclose($fp);

		if ($status != 0) {
			$this->set_message('Sendmail Status:', $status);
			return false;
		} else {
			return true;
		}
	}

  /**
	* Send Email using SMTP
	*
	* @access	protected
	* @return	bool
	*/
	
	protected function smtp() {
		if ($this->config['error'] == true) {
			return false;
		}
		
		if (!isset($this->config['smtp_user']) or !isset($this->config['smtp_pass'])) {
			$this->set_message('Error: Smtp Username or password not exist.');
			$this->config['error'] = true;
			return false;
		}
		
	   try {
	   
   		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
 	    
		if ($socket < 0) {
		      $this->set_message('socket_create() failed: '.socket_strerror(socket_last_error()));
		}

	   $result = socket_connect($socket, $this->config['host'], $this->config['port']);
	   if ($result === false) {
	      $this->set_message('socket_connect() failed: '.socket_strerror(socket_last_error()));
	   }
  	   
	   if(!is_resource($socket)) {
			$this->set_message('Smtp error - '.$errno." ".$errstr);
			return false;
	   }
			
	   $this->set_message('Read information to SMTP Server');     
  	   $this->read_answer($socket);

	   $this->set_message('Send Hello to Smtp Server');  
  	   $this->write_response($socket, 'EHLO '.$this->config['smtp_user']);
  	   $this->read_answer($socket);  		
       $this->set_message('Queation received');     
		
  	   $this->set_message('Cheack SMTP Username and password');				
  	   $this->write_response($socket, 'AUTH LOGIN');
  	   $this->read_answer($socket);
		
  	   $this->write_response($socket, base64_encode($this->config['smtp_user']));
  	   $this->read_answer($socket);
		
  	   $this->write_response($socket, base64_encode($this->config['smtp_pass']));
  	   $this->read_answer($socket);
		
  	   $this->set_message('Username and password is valid');
  	   $this->set_message('Write sender address ... ');

  	   $this->write_response($socket, 'MAIL FROM:<'.$this->only_email($this->config['email_headers']['From']).'>');
  	   $this->read_answer($socket);
		
  	   $this->set_message('Sender address writed successfully');
  	   $this->set_message('Write recipient address ... ');

  	   foreach ($this->config['recipients'] as $val) {
		  if ($val!='') {
			  $this->write_response($socket, 'RCPT TO:<'.$val.'>');
		  }
  	   }
  	   
  	   if (is_array($this->config['cc'])) {
			foreach ($this->config['cc'] as $val) {
		  		if ($val!='') {
			  		$this->write_response($socket, 'RCPT TO:<'.$val.'>');
		  		}
  	   		}
  	   }
  	    
  	   $this->read_answer($socket);
  	   $this->set_message('recipient address writed successfully');
   		
  	   $this->set_message('Write Mail Body ... ');

  	   $this->write_response($socket, 'DATA');
  	   $this->read_answer($socket);
		
  	   $this->write_response($socket, $this->config['email_header'].
								preg_replace('/^\./m', '..$1', 
								$this->config['finalbody']).$this->config['crlf'].".".$this->config['crlf']);
  	   $this->read_answer($socket);

  	   $this->set_message('Mail Body Writed successfully');

  	   $this->set_message('Close connection ... ');
  	   
	   if ($socket!==false) {
			$this->write_response($socket, 'QUIT');
  			$this->read_answer($socket);
  	   }
  	   $this->set_message('Connection closed and mail sent successfully');

  	   } catch (Exception $e) {
		   	$this->set_message('Error: '.$e->getMessage());
  	   }
 	   
  	   return true;
	}
	
  /**
	* Read SMTP data
	*
	* @access	protected
	* @param	resource
	* @return	string
	*/
	protected function read_answer($socket) {
   	  $read = socket_read($socket, 1024);
	     if ($read{0} != '2' && $read{0} != '3') {
 	     	 $this->set_message('Smtp socket read : '. socket_strerror(socket_last_error()));
    	 } else {
			return true;	 
		 }
	}

  /**
    * Write SMTP response
    *
    * @access	protected
	* @param	resource
	* @param	string
	* @return	bool
	*/
	protected function write_response($socket, $data) {
	    $data = $data.$this->config['newline'];
    	$write = socket_write($socket, $data, strlen($data));
		if ($write === false) {
 	     	 return false;
			 $this->set_message('Smtp socket write : '. socket_strerror(socket_last_error()));
		} else {
			return true;	
		}
	}
	
  /**
	* Set Message
	*
	* @access	protected
	* @param	string
	* @return	void
	*/
	protected function set_message($message) {
		$this->config['message'][] = $message;
	} 
	
  /**
	* Get Mime Types
	*
	* @access	protected
	* @param	string
	* @return	string
	*/
	protected function get_mime_type($ext = "") {
		
		$mime = '';
		
		if($ext != '' and is_readable(TK_PATH . 'config' . TK_DS . 'mimes.ini')) {
		
			$mimes = $this->lib->ini->instance(TK_PATH . 'config' . 
										   			TK_DS . 'mimes.ini');
											
			if(is_object($mimes)) {
				$mime = $mimes->item_get($ext);
			}
			
		} else {
			$mime = 'application/x-unknown-content-type'; 
		}

		return $mime;
	}


  /**
	* Prep Quoted Printable
	*
	* Prepares string for Quoted-Printable Content-Transfer-Encoding
	*
	* @access	protected
	* @param	string
	* @param	integer
	* @return	string
	*/
	protected function q_printable($str, $charlim = '') {
		if (!$str) {
			return false;	
		}
		
		if ($charlim == '' or $charlim > '76') {
			$charlim = '76';
		}

		$str = preg_replace("| +|", " ", $str);
		$str = preg_replace('/\x00+/', '', $str);

		if (strpos($str, "\r") !== FALSE) {
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		$lines = explode("\n", $str);
		$escape = '=';
		$output = '';

		foreach ($lines as $line) {
			$length = strlen($line);
			$temp = '';

			for ($i = 0; $i < $length; $i++) {
	
				$char = substr($line, $i, 1);
				$ascii = ord($char);

				if ($i == ($length - 1)) {
					$char = ($ascii == '32' or $ascii == '9') ? $escape.sprintf('%02s', dechex($ascii)) : $char;
				}

				if ($ascii == '61') {
					$char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));
				}

				if ((strlen($temp) + strlen($char)) >= $charlim) {
					$output .= $temp.$escape.$this->config['crlf'];
					$temp = '';
				}

				$temp .= $char;
			}

			$output .= $temp.$this->config['crlf'];
		}

		$output = substr($output, 0, strlen($this->config['crlf']) * -1);

		return $output;
	}

  /**
	* Return only email address
	*
	* @access	protected
	* @param	string
	* @return	string
	*/
	protected function only_email($email) {
		if ( ! is_array($email)) {
			if (preg_match('/\<(.*)\>/', $email, $match)) {
				return $match['1'];
			} else {
				return $email;
			}
		}

		$clean_email = array();

		foreach ($email as $addy) {
			if (preg_match( '/\<(.*)\>/', $addy, $match)) {
				$clean_email[] = $match['1'];
			} else {
				$clean_email[] = $addy;
			}
		}

		return $clean_email;
	}
	
	/**
	* Marge array values to string
	*
	* @access	protected
	* @param	array
	* @param	string
	* @param	string
	* @param	string			
	* @return	string
	*/
	protected function to_string($array, $new_line, $pars_keys = false, $key_delemiter = false) {
		if (!is_array($array)){
			return $array;	
		}
		
		$str = '';
		if ($pars_keys == false) {
			foreach ($array as $val) {
				if (!is_array($val)){
					$str.=$val.$new_line;	
				} else {
					$this->to_string($val, $new_line);	
				}
			}
		} else {
			if ($key_delemiter==false) {
				$key_delemiter = '-';	
			}
			
			foreach ($array as $key=>$val) {
				if (!is_array($val)){
					$str.=$key.$key_delemiter.$val.$new_line;	
				} else {
					$this->to_string($val, $new_line, $pars_keys, $key_delemiter);
				}
			}
		}
		return $str;
	}
   
   /**
	* Log resulte 
	*
	* @access	public
	* @param	string
	* @param	string	
	* @return	void
	*/
	public function log_resulte($filename = false, $full= false) {
		if (!is_array($this->config)) {
			$this->config['error'] = true;
			$this->set_message('Error : Email not configured');
		}
		
		if (!$filename) {
			$filename = 'email_log'.'.log';
		} else {
			$filename.='.log';
		}
		
		$log_obj = $this->lib->log->instance($filename);

		$log_obj->write('Email Protocol : '.$this->config['protocol']);
		$log_obj->write('Email Priority : '.$this->config['priority']);
		$log_obj->write('Charset : '.$this->config['charset']);
		$log_obj->write('Email Type: '.$this->config['mailtype']);		

		if ($this->config['validate'] == false) {
			$log_obj->write('Validate Email : No');
		} else {
			$log_obj->write('Validate : Yes');
		}
		
		if (isset($this->config['email_headers']['Date'])) {
			$log_obj->write('Date : '.$this->config['email_headers']['Date']);	
		}

		if (isset($this->config['email_headers']['Message-ID'])) {
			$log_obj->write('Message-ID : '.$this->config['email_headers']['Message-ID']);
		}
		
		if (isset($this->config['email_headers']['Mime-Version'])) {
			$log_obj->write('Mime-Version : '.$this->config['email_headers']['Mime-Version']);
		}

		if (isset($this->config['email_headers']['From'])) {
			$log_obj->write('From : '.$this->config['email_headers']['From']);
		}
		
		if (isset($this->config['recipients'])) {
			$log_obj->write('To: '.$this->config['recipients']);
		}

		if (isset($this->config['Subject'])) {
			$log_obj->write('Subject : '.$this->config['Subject']);
		}
		
		if (isset($this->config['body'])) {
			$log_obj->write('Body : '.$this->config['body']);
		}
		
		if (is_array($this->config['attachment'])) {
				$log_obj->write('Attachment Files Count : '.count($this->config['attachment']));
			foreach ($this->config['attachment'] as $val) {
				$log_obj->write('Attached : '.$val['filename']);
			}
		}
		
		if ($this->config['error']) {
			$log_obj->write('Have error: Yes');			
		}

		if (isset($this->config['message'])) {
			foreach ($this->config['message'] as $val) {
				$log_obj->write($val);
			}
		}
		
		if ($full) {
			$log_obj->write('Full Dump : '.$this->to_string($this->config, '; ', true, ':'));
		}
	}
/* End of class email_lib */
}

/* End of file */
?>