<?php

/**
 * Configuration file for mail
 */


config::reg_section("mail", array("title" => "Mail"));

config::reg_subsection("mail", "mode", array("title" => "General settings for sending mails"));
config::reg_subsection("mail", "smtp", array("title" => "SMTP Settings", "advanced" => true));
config::reg_subsection("mail", "sendmail", array("title" => "Sendmail settings", "advanced" => true));


//// mode
config::reg_field("mail", "mode", "mail_default_to", array(
	"title" => "Default To address",
	"default" => "@",
	"desc" => "Default address to send mails to, used for contact page."
	)
);
config::reg_field("mail", "mode", "mail_method", array(
	"title" => "Mail method",
	"type" => "radio",
	"options" => array("mail" => "PHP Mail function", "smtp" => "SMTP", "sendmail" => "Sendmail"),
	"default" => "mail",
	"advanced" => true,
	"desc" => "Determine how user are sorted into groups. See doc for more info"
	)
);

//// SMTP
config::reg_field("mail", "smtp", "mail_smtp_host", array(
	"title" => "SMTP Host",
	"default" => "localhost"
	)
);
config::reg_field("mail", "smtp", "mail_smtp_port", array(
	"title" => "SMTP Port number",
	"default" => "",
	"Leave blank for default SMTP port"
	)
);
config::reg_field("mail", "smtp", "mail_smtp_username", array(
	"title" => "SMTP Username",
	"default" => ""
	)
);
config::reg_field("mail", "smtp", "mail_smtp_password", array(
	"title" => "SMTP Password",
	"default" => ""
	)
);

//// sendmail
config::reg_field("mail", "sendmail", "mail_sendmail_path", array(
	"title" => "Sendmail path",
	"default" => "",
	"desc" => "Path to Sendmail on server"
	)
);



//// ERROR LOG


config::reg_field("error", "log", "error_send_mail", array(
	"title" => "Mail errors",
	"type" => "checkbox",
	"label" => "Send a mail on error",
	"cast" => "bool",
	"default" => false,
	"advanced" => true,
	"desc" => "Send an email to below address everytime an error occurs (useful in production environment)"
	)
);
config::reg_field("error", "log", "error_email", array(
	"title" => "Email for sending errors",
	"type" => "text",
	"default" => "",
	"advanced" => true,
	"desc" => "Send an email to this address on error with informations for debuging. Need the mail module. Leaving blank will desactivate"
	)
);

