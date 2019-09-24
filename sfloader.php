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

if(file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf") and is_dir($f)){
	require_once $f . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(isset($_ENV["SF_HOME"])){
	require_once $_ENV["SF_HOME"] . DIRECTORY_SEPARATOR . "autoload.php";
}elseif(file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf.json")){
	$config = json_decode(file_get_contents($f), true);
	if(file_exists($config["home"])){
		require_once $config["home"] . DIRECTORY_SEPARATOR . "autoload.php";
	}
}else{
	echo "SimpleFramework Loader cannot find SimpleFramework autoload.php" . PHP_EOL;
	echo "Run \"git clone https://github.com/iTXTech/SimpleFramework.git --depth=1 sf\"" . PHP_EOL;
}
