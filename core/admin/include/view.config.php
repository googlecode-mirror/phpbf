<?php

if (LOGGED !== true) die();

if (!config::config_exists()) {
	print '
		<h2>PhpBF Framework configuration</h2>
		Config file does not exists or is not valid. Do you want to create a new config file with default values?
		<br/><br/>
		<input type="button" value="Create" onclick="document.location.href=\''.common::url('self').'?edit=config\';"/>
		';
} else {

	config::load_confdata();

	print '<h2>PhpBF Framework configuration</h2>';
	$first = reset(array_keys(config::$sections));
	print '<style>
		#menu {
			background: #333;
			list-style: none;
			margin: 0;
			padding: 0;
			width: 100%;
		}
		#menu li {
			margin: 0;
			padding: 0;
		}
		#menu a {
			background: #333;
			border-bottom: 1px solid #393939;
			color: #ccc;
			display: block;
			margin: 0;
			padding: 8px 12px;
			text-decoration: none;
			font-weight:normal;
		}
		#menu a:hover, #menu a.selected {
			background: #2C7BA6 url('.common::url('online').'Images/hover.png) left center no-repeat;
			color: #fff;
			padding-bottom: 8px;
		}  
		h4 {
			width: 150px;
			float: left;
			padding: 0;
			margin: 0;
			font-weight: normal;
			font-size: 100%;
		}
		h3 {
			background-color: #EEEEEE;
			padding: 5px;
			font-size: 90%;
			margin-top: 0px;
			float: left;
			width: 640px;
		}
		div.field {
			width: 500px;
			float: left;
			margin-bottom: 10px;
		}
		div.field .text {
			width: 300px;
			padding: 5px;
			margin-bottom: 3px;
		}
		div.field .table .text {
			width: 100%;
			padding: 3px;
		}
		div.field .table {
			width: 100%;
			border: solid 1px #CCCCCC;
			padding: 5px;
			background-color: #EEEEEE;
		}
		div.field .table th {
			font-size: 75%;
		
		}
		div.field .default {
			font-size: 80%;
			margin-left: 10px;
		}
		span.desc {
			font-size: 85%;
			color: #333333;
		} 
		.advanced {
			display: block;
		}        
	</style>
	<script language="javascript">
		get = function (id) { return document.getElementById(id)};
		var currenttab = "'.$first.'";
		showtab = function(id) {
			if (id == currenttab) return;
			if (get("tab_"+id)) {
				get("tab_"+id).style.display = "block";
				get("tab_"+currenttab).style.display = "none";
				get("tabbutton_"+currenttab).className = "";
				get("tabbutton_"+id).className = "selected";
				currenttab = id;
			}
		}
		switchAdvanced = function (advanced) {
			for (var j = 0; j < document.styleSheets.length; j++) {
				var rules=document.styleSheets[j].cssRules? document.styleSheets[j].cssRules: document.styleSheets[j].rules
				outer:for (var i=0; i<rules.length; i++){
					if(rules[i].selectorText.toLowerCase()==".advanced"){
						targetrule=rules[i]
						break outer;
					}
				}
			}
			targetrule.style.display = advanced? "block":"none";
		}
	</script>';

	print '<div style="float: left; width: 130px; margin-right: 20px;"><ul id="menu">';
	foreach (config::$sections as $id => $sec) {
		print '<li'.(isset($sec['advanced']) && $sec['advanced']? ' class="advanced"':'').'><a id="tabbutton_'.$id.'" '.($first == $id? 'class="selected"':'').' onclick="showtab(\''.$id.'\');">'.$sec['title'].'</a></li>';
	}
	print '</ul>';
	print '<br/><a style="font-size: 80%;" href="'.common::url('self').'?view=reset_confirm">Reset to default settings</a>';
	print '</div><form action="'.common::url('self').'?edit=config" method="post"><div style="position: absolute; right: 0; top: 40px; text-align:right; font-size: 90%;"><label>Show advanced options <input id="advancedButton" type="checkbox" onclick="switchAdvanced(this.checked);"/></label></div>';
	print '<script language="javascript">switchAdvanced(get("advancedButton").checked);</script>';
	foreach (config::$sections as $id_sec => $sec) {
		if (!isset($sec['advanced'])) $sec['advanced'] = false;
		print '<div'.($sec['advanced']? ' class="advanced"':'').' id="tab_'.$id_sec.'" style="float: left;width: 650px; margin-bottom: 30px; display: '.($first == $id_sec? 'block':'none').';">';
		foreach (config::$subsections[$id_sec] as $id_sub => $sub) {
			if (!isset($sub['advanced'])) $sub['advanced'] = false;
			if (!isset($sub['desc'])) $sub['desc'] = "";
			print '<h3'.($sec['advanced'] || $sub['advanced']? ' class="advanced"':'').'>'.$sub['title'].'</h3>';
			if ($sub['desc']) print '<span'.($sec['advanced'] || $sub['advanced']? ' class="advanced"':'').' class="desc">'.$sub['desc'].'<br/><br/><br/></span>';
			foreach (config::$fields[$id_sec][$id_sub] as $id => $field) {
				if (!isset($field['advanced'])) $field['advanced'] = false;
				$value = isset($field["onload"]) && $field["onload"]? $field["onload"]($id, config::get($id)) : config::get($id);
				print '<h4'.($sec['advanced'] || $sub['advanced'] || $field['advanced']? ' class="advanced"':'').'>'.$field['title'];
				if ($field['type'] == 'table' && $field['num'] == 'auto') {
					print '<br/><br/><input type="button" value="Add a row" onclick="var newrow = get(\'new_'.$id.'\').cloneNode(true); newrow.id = \'\'; get(\''.$id.'_table\').appendChild(newrow);"/>';
				}
				print '</h4><div class="'.($sec['advanced'] || $sub['advanced'] || $field['advanced']? 'advanced ':'').'field">';
				switch ($field['type']) {
					case 'radio' :
						foreach ($field['options'] as $option_value => $text) {
							print '<label><input class="radio" type="radio" name="'.$id.'" value="'.$option_value.'"'.($value == $option_value? ' checked="checked"':'').'/>'.$text.($field['default'] == $option_value? ' <span class="default">(Default)</span>':'').'</label><br/>';
						}
						print '<span class="desc">'.$field['desc'].'</span><br/>';
						break;
					case 'table' :
						print '<table cellspacing="0" cellpadding="0" class="table" id="'.$id.'_table"><thead><tr>';
						foreach ($field['columns'] as $id_col => $col) {
							print '<th style="'.($col['width']? 'width: '.$col['width'].';':'').'">'.$col['title'].'</th>';
						}
						print '</tr></thead><tbody>';
						for ($i = 0; $i < ($field['num'] == 'auto'? count($value) : $field['num']); $i++) {
							print '<tr>';
							foreach ($field['columns'] as $id_col => $col) {
								print '<td style="'.($col['width']? 'width: '.$col['width'].';':'').'">';
								$row = isset($value[$i])? $value[$i] : array();
								switch ($col['type']) {
									case 'custom' : printf ($col['content'], config::to_html($row[$id_col])); break;
									case 'text' :
									default : print '<input type="text" class="text" name="'.$id.'['.$id_col.'][]" value="'.config::to_html($row[$id_col]).'"/>'; break;
								}
								print '</td>';
							}
							print '</tr>';								
						}
						print '</tbody></table>';
						if ($field['num'] == 'auto') {
							print '<table style="display: none;"><tr id="new_'.$id.'">';
							foreach ($field['columns'] as $id_col => $col) {
								print '<td style="'.($col['width']? 'width: '.$col['width'].';':'').'">';
								switch ($col['type']) {
									case 'custom' : printf ($col['content'], ''); break;
									case 'text' : 
									default : print '<input type="text" class="text" name="'.$id.'['.$id_col.'][]" value="" />'; break;
								}
								print '</td>';
							}
							print '</tr></table>';	
						}
						if ($field['example']) {
							print '<span class="default">Example: ';
							$first = true;
							foreach ($field['columns'] as $id_col => $col) {
								if ($first) $first = false;
								else print ', ';
								print $col['title'].': <i>'.$field['example'][$id_col].'</i>';
							}
							print '<br/><br/></span>';
						}
						print '<span class="desc">'.$field['desc'].'</span><br/>';
						break;
					case 'select' :
						print '<select class="select" name="'.$id.'">';
						foreach ($field['options'] as $option_value => $text) {
							print '<option value="'.$option_value.'"'.($option_value == $value? ' selected="selected"':'').'>'.$text.'</option>';
						}
						print '</select><span class="default">(Default: "'.$field['options'][$field['default']].'")</span><br/><span class="desc">'.$field['desc'].'</span><br/>';
						break;
					case 'checkbox' :
						print '<label><input type="hidden" name="'.$id.'_checkbox_submited" value="true"/><input type="checkbox" class="checkbox" value="1" name="'.$id.'"'.($value? ' checked="checked"':'').'/>'.$field['label'].'<span class="default">(Default is '.($field['default']? 'checked':'unchecked').')</span><br/>';
						print '<span class="desc">'.$field['desc'].'</span><br/>';
						break;
					case 'text' :
					default :
						print '<input type="text" class="text" name="'.$id.'" value="'.config::to_html($value).'"/><span class="default">(Default: "'.$field['default'].'")</span><br/>';
						print '<span class="desc">'.$field['desc'].'</span><br/>';
						break;
				}
				print '</div>';
			}
		}
		print '</div>';
	}

	print '<div style="text-align: center; float: left;width: 800px;"><input style="width: 150px;" type="submit" value="Save"/></div></form>';
}
