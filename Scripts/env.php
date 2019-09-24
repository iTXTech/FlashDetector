<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2019 iTX Technologies
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

require_once "../sfloader.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;

Initializer::initTerminal();

Logger::$logLevel = 2;//disable logger

Logger::info("Loading iTXTech FlashDetector");

global $classLoader;
try{
	$moduleManager = new ModuleManager($classLoader,
		__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR);
	$moduleManager->registerModuleDependencyResolver(new WraithSpireMDR($moduleManager,
		"https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/", []));
	loadModule($moduleManager, "SimpleHtmlDom_v1.1.0.phar");
	loadModule($moduleManager, "FlashDetector");
}catch(Throwable $e){
	Logger::logException($e);
}

function loadModule(ModuleManager $manager, string $name){
	$manager->tryLoadModule($manager->getModulePath() . $name);
}

if($moduleManager->getModule("FlashDetector") === null){
	Logger::error("Module not loaded.");
	exit(1);
}

FlashDetector::init();
