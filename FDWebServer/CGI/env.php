<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2021 iTX Technologies
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function getRemote() : string{
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return str_replace(" ", "", explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
    }
    return $_SERVER["HTTP_X_REAL_IP"] ?? $_SERVER["REMOTE_ADDR"];
}

function getUserAgent() : string{
	return $_SERVER["HTTP_USER_AGENT"] ?? "Undefined";
}
