#!/usr/bin/php -q
<?php
require_once("repos.php");

function getAllAddons($repos) {
	$allAddons=array();
	foreach($repos as $repo) {	
		$xmlString=file_get_contents($repo['xmlURL']);
		$xmlObj=simplexml_load_string($xmlString);
		foreach($xmlObj as $addon) {
			if(isset($allAddons[(string)$addon->attributes()->{'id'}])) {
				/*
				print("Addon " . (string)$addon->attributes()->{'id'} . " already found in repo " .
					$allAddons[(string)$addon->attributes()->{'id'}]["repo"] . " with version " . 
					$allAddons[(string)$addon->attributes()->{'id'}]["version"] . "\n" .
					"Now also found in repo " . $repo["name"] . " with version " . (string)$addon->attributes()->{'version'} . "\n\n\n");
				 */
				continue;
			} else {
				$dependencies=array();
				if(isset($addon->requires->import)) {
					foreach($addon->requires->import as $req) {
						array_push($dependencies, array(
							"name"=>(string)$req->attributes()->{'addon'},
							"version"=>(string)$req->attributes()->{'version'}));
					}
				}
				$allAddons[(string)$addon->attributes()->{'id'}] = array(
					"version"=>(string)$addon->attributes()->{'version'},
					"repo"=>$repo["name"],
					"dlURL"=>$repo["repoURL"] . "/" . (string)$addon->attributes()->{'id'} . "/" .
					(string)$addon->attributes()->{'id'} . "-" . (string)$addon->attributes()->{'version'} . ".zip",
						"dependencies"=>$dependencies);
			}
		}
	}
	return $allAddons;
}

function get_dependencies($addon) {
	$kodipath="/opt/kodi-server/share/kodi/portable_data/addons";
	$dependencies=array();
	$xml=simplexml_load_file("$kodipath/$addon/addon.xml");
	foreach($xml->requires->import as $req) {
		array_push($dependencies, (string)$req['addon']);
	}
	return $dependencies;
}

function installAddon($addon, $allAddons) {
	print("Start searching for addon $addon...\n");
	$kodipath="/opt/kodi-server/share/kodi/portable_data/addons";
	if(!isset($allAddons[$addon])) {
		print("NOTICE: Addon $addon not found.\n");
	} else {
		print("Downloading addon $addon from " . $allAddons[$addon]['repo'] . " repo...\n");
		$cmd="wget " . $allAddons[$addon]['dlURL'] . " -O " . $addon . "-" . $allAddons[$addon]['version'] . ".zip";
		exec($cmd);
		print("Installing addon...\n");
		$cmd="mkdir -p \"" . $kodipath . "\""; 
		exec($cmd);
		$cmd="unzip -o \"" . $addon . "-" . $allAddons[$addon]['version'] . ".zip\" -d \"" . $kodipath . "\"";
		exec($cmd);
		print("Addon $addon successfully installed.\n\n\n");
		foreach($allAddons[$addon]['dependencies'] as $dep) {
			installAddon($dep['name'], $allAddons);
		}
	}
}

$addons=explode("|",$argv[1]);
$allAddons=getAllAddons($repos);
foreach($addons as $addon) {
	installAddon($addon, $allAddons);
}
?>
