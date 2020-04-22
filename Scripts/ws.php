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

//FlashDetector WebServer

require_once "env.php";

use iTXTech\FlashDetector\WebServer\WebServer;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Console\SwooleLoggerHandler;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Util\Util;

Logger::$logLevel = 0;

$options = new Options();
$group = (new OptionGroup())
	->addOption((new OptionBuilder("s"))->longOpt("swoole")
		->desc("Launch swoole-powered FDWebServer")->build())
	->addOption((new OptionBuilder("w"))->longOpt("workermanee")
		->desc("Launch WorkerManEE-powered FDWebServer")->build());
$group->setRequired(true);
$options->addOptionGroup($group)
	->addOption((new OptionBuilder("a"))->longOpt("address")->hasArg()->argName("addr")
		->desc("Server address")->required()->build())
	->addOption((new OptionBuilder("p"))->longOpt("port")->hasArg()->argName("port")
		->desc("Server port")->required()->build());

global $moduleManager;

try{
	$cmd = (new Parser())->parse($options, $argv);
	$config = [
		"address" => $cmd->getOptionValue("a"),
		"port" => $cmd->getOptionValue("p"),
		"swoole" => [
			"worker_num" => 4
		]
	];
	if($cmd->hasOption("s")){
		loadModule($moduleManager, "FDWebServer" . DIRECTORY_SEPARATOR . "swoole");
		SwooleLoggerHandler::init();
		Logger::setLoggerHandler(SwooleLoggerHandler::class);
	}
	if($cmd->hasOption("w")){
		$moduleManager->registerModuleDependencyResolver(new WraithSpireMDR($moduleManager,
			"https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/", []));
		loadModule($moduleManager, "WorkerManEE_v17.10.1.phar");
		loadModule($moduleManager, "FDWebServer" . DIRECTORY_SEPARATOR . "WorkerManEE");
	}
	$server = new WebServer($config);
	$server->start();
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("ws", $options));
}
