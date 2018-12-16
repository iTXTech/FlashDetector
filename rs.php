<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

require_once "sf/autoload.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal();

if(!isset($argv[1])){
	Util::println("Usage: \"" . PHP_BINARY . "\" \"" . $argv[0] . "\" <flash id>");
	exit(1);
}

Logger::$logLevel = 2;//disable logger

Logger::info("Loading iTXTech FlashDetector");

global $classLoader;
try{
	$moduleManager = new ModuleManager($classLoader, __DIR__ . DIRECTORY_SEPARATOR, "");
	$moduleManager->loadModules();
}catch(Throwable $e){
	Logger::logException($e);
}

if($moduleManager->getModule("iTXTech_FlashDetector") === null){
	Logger::error("Module not loaded.");
	exit(1);
}

FlashDetector::init();
$info = FlashDetector::getFlashPartNumberFromIddb($argv[1]);
Util::println(json_encode($info, JSON_PRETTY_PRINT));
