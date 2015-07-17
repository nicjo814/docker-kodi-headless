#!/usr/bin/php -q
<?php

function get_dependencies($addon) {
	$kodipath="/opt/kodi-server/share/kodi/portable_data/addons";
	$dependencies=array();
	$xml=simplexml_load_file("$kodipath/$addon/addon.xml");
	foreach($xml->requires->import as $req) {
		array_push($dependencies, (string)$req['addon']);
	}
	return $dependencies;
}

function get_addon($addon) {
	$kodipath="/opt/kodi-server/share/kodi/portable_data/addons";
	unset($out);
	$p_addon = preg_quote($addon);
	$cmd = "xam all | grep '$p_addon\s.*$'";
	exec($cmd, $out);
	if(count($out) === 1) {
		unset($out);
		exec("xam get $addon");
		exec("mkdir -p '$kodipath'");
		exec("unzip -o '$addon/*.zip' -d'$kodipath'");
		$dependencies=get_dependencies($addon);
		foreach($dependencies as $dep) {
			get_addon($dep);
		}
	} else {
		print("No plugin found $addon\n");
	}

}

$addons=explode("|",$argv[1]);
foreach($addons as $addon) {
	get_addon($addon);
}
?>
