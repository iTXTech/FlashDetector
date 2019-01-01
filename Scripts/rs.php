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

//Reverse Search

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Util\Util;

$group = new OptionGroup();
$group->addOption((new OptionBuilder("i"))->desc("Reverse searching Flash Id")->longOpt("flash-id")
	->hasArg()->argName("Flash Id")->build())
	->addOption((new OptionBuilder("p"))->desc("Reverse searching Part Number")->longOpt("part-number")
		->hasArg()->argName("Part Number")->build())
	->addOption((new OptionBuilder("c"))->desc("Reverse searching supported controllers")->longOpt("controllers")
		->hasArg()->argName("Flash Id")->build());
$group->setRequired(true);
$options = new Options();
$options->addOptionGroup($group);

try{
	$cmd = (new Parser())->parse($options, $argv);
	if($cmd->hasOption("i")){
		$info = FlashDetector::searchFlashId($cmd->getOptionValue("i"), true);
	}
	if($cmd->hasOption("p")){
		$info = FlashDetector::searchPartNumber($cmd->getOptionValue("p"), true);
	}
	if($cmd->hasOption("c")){
		$info = FlashDetector::searchSupportedControllers($cmd->getOptionValue("c"), true);
	}
	Util::println(json_encode($info, JSON_PRETTY_PRINT));
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("rs", $options));
}
