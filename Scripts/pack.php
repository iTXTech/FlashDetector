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

require_once "env.php";

use iTXTech\FlashDetector\Packer;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Module\Module;
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
	$module = $moduleManager->getModule($name);
	packModule(Packer::VARIANT_TYPICAL, $name . ".phar", $module);
	if($module->getInfo()->composer()){
		packModule(Packer::VARIANT_COMPOSER, $name . "_composer.phar", $module);
	}
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("pack", $options));
}

function packModule(int $variant, string $file, Module $module){
	$module->pack($variant, __DIR__ . DIRECTORY_SEPARATOR, $file, true, true, true);
	if(file_exists($file)){
		$phar = new Phar($file);
		$metadata = $phar->getMetadata();
		$metadata["revision"] = Util::getLatestGitCommitId(".." . DIRECTORY_SEPARATOR);
		$phar->setMetadata($metadata);
	}
}
