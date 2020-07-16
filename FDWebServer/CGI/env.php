<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2020 iTX Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//Runtime Environment Initializer
ob_start();
foreach(["sf.phar", "SimpleFramework.phar", "sfloader.php", "sf/sfloader.php"] as $loader){
	if(file_exists($loader)){
		if(explode(".", $loader)[1] == "phar"){
			require_once "phar://" . $loader . "/sfloader.php";
		}else{
			require_once "$loader";
		}
		break;
	}
}
ob_clean();

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Module\ModuleManager;

Logger::$logLevel = 4;//disable logger

global $classLoader;
try{
	$moduleManager = new ModuleManager($classLoader,
		__DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, "");
	loadModule($moduleManager, "FlashDetector");
}catch(Throwable $e){
	Logger::logException($e);
}

function loadModule(ModuleManager $manager, string $name){
	$name = $manager->getModulePath() . $name;
	$manager->tryLoadModule(file_exists($phar = $name . ".phar") ? $phar : $name);
}

if($moduleManager->getModule("FlashDetector") === null){
	Logger::error("Module not loaded.");
	exit(1);
}

header("X-SimpleFramework: " . Framework::PROG_VERSION);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

function getQuery() : string{
	return $_SERVER["REQUEST_URI"];
}

function getRemote() : string{
	return $_SERVER["REMOTE_ADDR"];
}
