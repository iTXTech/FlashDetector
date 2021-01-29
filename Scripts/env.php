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

require_once "../sfloader.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;

Initializer::initTerminal();

Logger::$logLevel = 2;//disable logger

Logger::info("Loading iTXTech FlashDetector");

$modules = ["FlashDetector"];

global $classLoader;
try{
	$moduleManager = new ModuleManager($classLoader,
		__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR);
	$moduleManager->registerModuleDependencyResolver(new WraithSpireMDR($moduleManager,
		"https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/", []));
	foreach($modules as $m){
		loadModule($moduleManager, $m);
	}
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
