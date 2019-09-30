<?php

/*
 *
 * SimpleFramework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

const SF = "SimpleFramework.phar";

if(isset($argv[1]) and substr($argv[1], 0, 2) == "sf"){
	$f = explode("=", $argv[1], 2)[1];
	array_splice($argv, 1, 1);
	if(is_file($f)){
		$phar = new Phar($f);
		if(($phar->getMetadata()["name"]) ?? "" == "SimpleFramework"){
			require_once "phar://" . $f . DIRECTORY_SEPARATOR . "autoload.php";
		}else{
			require_once $f;
		}
	}else{
		require_once $f . DIRECTORY_SEPARATOR . "autoload.php";
	}
}elseif(Phar::running() == "" and file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . SF) or
	Phar::running() != "" and file_exists($f = getcwd() . DIRECTORY_SEPARATOR . SF)){
	require_once "phar://" . $f . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "autoload.php")){
	require_once $f;
}elseif(file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf") and is_dir($f)){
	require_once $f . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(isset($_ENV["SF_HOME"])){
	require_once $_ENV["SF_HOME"] . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(isset($_ENV["SF_ARCHIVE"])){
	require_once "phar://" . $_ENV["SF_ARCHIVE"] . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf.json")){
	$config = json_decode(file_get_contents($f), true);
	if(file_exists($config["home"])){
		require_once $config["home"] . DIRECTORY_SEPARATOR . "autoload.php";
	}
}else{
	echo "SimpleFramework Loader cannot locate SimpleFramework autoload.php" . PHP_EOL;
	echo "Run \"git clone https://github.com/iTXTech/SimpleFramework.git --depth=1 sf\"" . PHP_EOL;
}

function isSimpleFrameworkLoaded() : bool{
	return class_exists("iTXTech\SimpleFramework\Initializer");
}
