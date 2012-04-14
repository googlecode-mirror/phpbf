<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="content-type" content="{$_data.content_type}; charset={$_data.encoding}" />
<title>
	{$_data.title|string_format:$_data.window_title}
</title>
<meta name="title" content="{$_data.title}" />
<meta name="creator" content="{$_data.creator}" />
<meta name="subject" content="{$_data.subject}" />
<meta name="keywords" content="{$_data.keywords}" />
<meta name="publisher" content="{* SET PUBLISHER HERE *}" />
<meta name="language" content="{BF::gl()->lang}" />
<meta name="description" content="{$_data.description}" />


{css src="reset.css"}{/css}
{css src="input.css"}{/css}
{css src="example.css"}{/css}
{css src="smoothness/jquery-ui-1.8.18.custom.css"}{/css}

{js src="lib/jquery-1.7.1.js"}{/js}
{js src="lib/jquery-ui-1.8.18.custom.min"}{/js}
{js src="lib/form.js"}{/js}

</head>
<body>

<div id="content">
