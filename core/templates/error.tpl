{header}

{css src="error.css"}{/css}

<div class='error_box'>
	<h2>{$title}</h2>
	{if $type == 'BF_internal' || $type == 'BF_forbidden' || $type == 'BF_not_found'}
		{img src='error/error.png' class='error_icon'}
	{else}
		{img src='error/warning.png' class='error_icon'}
	{/if}
	<div>
		{$message}
		<br/>
		<br/>
		<br/>
		<a href="#" onclick="history.go(-1);">Return</a>
	</div>
</div>
<pre>{$debug}</pre>
	

{footer}
