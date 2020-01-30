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

require_once "env.php";

use iTXTech\FlashDetector\FDBGen\FDBGen;
use iTXTech\FlashDetector\FDBGen\MicronDatabase;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Util;

global $moduleManager;
loadModule($moduleManager, "FDBGen");

Logger::$logLevel = 0;

FDBGen::init();

$options = new Options();
$options
	->addOption((new OptionBuilder("f"))->longOpt("file")->hasArg()->argName("file")
		->desc("Micron Database file")->required(true)->build())
	->addOption((new OptionBuilder("p"))->longOpt("proxy")->hasArg()->argName("proxy")
		->desc("Use HTTP proxy")->required(false)->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	if($cmd->hasOption("p")){
		Curl::$GLOBAL_PROXY = $cmd->getOptionValue("p");
	}
	$db = new MicronDatabase($cmd->getOptionValue("f"));
	$db->update(JSON_PRETTY_PRINT);
	$db->save(JSON_PRETTY_PRINT);
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("microndb", $options));
}
