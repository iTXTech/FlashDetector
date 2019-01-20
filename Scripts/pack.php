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

require_once "env.php";

use iTXTech\SimpleFramework\Console\Command\PackModuleCommand;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Util\Util;

Logger::$logLevel = 0;

$options = new Options();
$group = (new OptionGroup())
	->addOption((new OptionBuilder("g"))->longOpt("fdbgen")->desc("Pack FDBGen")->build())
	->addOption((new OptionBuilder("s"))->longOpt("fdwss")->desc("Pack swoole-powered FDWebServer")->build())
	->addOption((new OptionBuilder("w"))->longOpt("fdwsw")->desc("Pack WorkerManEE-powered FDWebServer")->build())
	->addOption((new OptionBuilder("f"))->longOpt("fd")->desc("Pack FlashDetector")->build());
$group->setRequired(true);
$options->addOptionGroup($group);

global $moduleManager;

try{
	$cmd = (new Parser())->parse($options, $argv);
	$path = "";
	$name = "";
	if($cmd->hasOption("g")){
		$path = $name = "FDBGen";
	}
	if($cmd->hasOption("s")){
		$path = "FDWebServer" . DIRECTORY_SEPARATOR . "swoole";
		$name = "FDWebServer-S";
	}
	if($cmd->hasOption("w")){
		$moduleManager->registerModuleDependencyResolver(new WraithSpireMDR($moduleManager,
			"https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/", []));
		loadModule($moduleManager, "WorkerManEE_v17.10.1.phar");
		$path = "FDWebServer" . DIRECTORY_SEPARATOR . "WorkerManEE";
		$name = "FDWebServer-W";
	}
	if($cmd->hasOption("f")){
		$path = $name = "FlashDetector";
	}
	loadModule($moduleManager, $path);
	$generator = new PackModuleCommand($moduleManager);
	$generator->execute("", [$name]);
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("pack", $options));
}

