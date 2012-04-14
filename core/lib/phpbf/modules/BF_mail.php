<?PHP

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Mail module
* @file mail.php
* @package PhpBF
* @subpackage mail
* @version 0.4
* @author Loic Minghetti
* @date Started on the 2006-05-10
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::gr("/lib/phpmailer/class.phpmailer.php")->load();

class BF_mail extends PHPMailer {

	public function __construct () {
		switch (BF::gc('mail_method')) {
			case "smtp" :
				$this->Host     = BF::gc('mail_smtp_host');
				$this->Mailer   = 'smtp';
				if (BF::gc('mail_smtp_username') != NULL) {
					$this->SMTPAuth = true;
					$this->Username = BF::gc('mail_smtp_username');
					$this->Password = BF::gc('mail_smtp_password');
					$this->SMTPSecure = 'ssl';
				}
				if (BF::gc('mail_smtp_port') != NULL) $this->Port = BF::gc('mail_smtp_port');
				break;
			case "sendmail" : 
				$this->Mailer   = "sendmail";
				$this->Sendmail = BF::gc('mail_sendmail_path');
				break;
			case "mail" :
			default :
				$this->Mailer   = "mail";
				break;
		}
		$this->PluginDir = BF::gr('/lib/phpmailer/')->path();
		$this->SetLanguage(BF::gl()->lang, BF::gr('/lib/phpmailer/language/')->path());
		$this->WordWrap = 75;
	}
}

/* USE EXEMPLE


$mail = new BF_mail();

$mail->From = $f_from[0];
$mail->FromName = $f_from[1];
$mail->AddReplyTo($f_from[0], $f_from[1]);

if (is_array($f_to)) {
	foreach($f_to as $v_address) {
		$mail->AddAddress($v_address[0], $v_address[1]);
	}
} else {
	return FALSE;
}

if (is_array($f_cc)) {
	foreach($f_cc as $v_address) {
		$mail->AddCC($v_address[0], $v_address[1]);
	}
}

$mail->Subject = $f_subject;
$mail->Body    = $f_msg;

if(!$mail->Send()) {
	return FALSE;
} else {
	return TRUE;
}


*/


?>
