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

function get_addon($addon, $repos) {
	print("Start searching for addon $addon...\n");
	$kodipath="/opt/kodi-server/share/kodi/portable_data/addons";
	for ($i=count($repos)-1;$i>0;$i--) {
		unset($out);
		$cmd="xam get --repo $repos[$i] $addon 2>&1";
		exec($cmd, $out, $res);
		$found=false;
		foreach($out as $line) {
			print("$line\n");
			if(preg_match('/^Downloading.*$/', $line)) {
				$found=true;
				print("Addon $addon installed\n\n\n");
				break;
			}
		}
		if($found) {
			exec("mkdir -p '$kodipath'");
			exec("unzip -o '$addon/*.zip' -d'$kodipath'");
			$dependencies=get_dependencies($addon);
			foreach($dependencies as $dep) {
				get_addon($dep, $repos);
			}
			break;
		}
	}
	if(!$found) {
		print("Addon $addon not found!\n\n\n");
	}
}

$addons=explode("|",$argv[1]);
exec("xam all --repo 2>&1", $out);
$prestr=preg_quote("usage: xam all [-h] [--repo {");
preg_match("/^$prestr([A-Z\,]+)\}\]$/", $out[0], $matches);
$repos=explode(",",$matches[1]);
foreach($addons as $addon) {
	get_addon($addon, $repos);
}
?>
