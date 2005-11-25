<?php

function get_lang_from_agent($accept) {

	$acceptarr = explode("," , $accept);
	foreach ($acceptarr as $lang) {
		//get rid of trailing garbage
		$lang = ereg_replace("^(.*)\;.*","\\1", $lang);
		if (is_dir("l10n/$lang")) {
			return $lang;
			exit;
		}
		//no translation for accept language
		return "en";
	}
}
?>
