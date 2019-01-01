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

//Micron/SpecTek FBGA and Component Marking Decoder

require_once "env.php";

use iTXTech\FlashDetector\Decoder\Online\Micron;
use iTXTech\FlashDetector\Decoder\Online\SpecTek;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Util\Util;

$decoder = new OptionGroup();
$decoder->addOption((new OptionBuilder("m"))->longOpt("micron")->desc("Micron decoder")->build())
	->addOption((new OptionBuilder("s"))->longOpt("spectek")->desc("SpecTek decoder")->build());
$decoder->setRequired(true);
$mode = new OptionGroup();
$mode->addOption((new OptionBuilder("c"))->longOpt("c2pn")->desc("FBGA Code or Mark Code to Part Number")->build())
	->addOption((new OptionBuilder("p"))->longOpt("pn2c")->desc("Part Number to FBGA Code or Mark Code")->build());
$mode->setRequired(true);
$options = new Options();
$options->addOptionGroup($decoder);
$options->addOptionGroup($mode);
$options->addOption((new OptionBuilder("v"))->longOpt("value")->hasArg()->argName("value")
	->desc("Code or PN value")->required(true)->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	$result = [];
	$value = $cmd->getOptionValue("v");
	if($cmd->hasOption("m")){
		if($cmd->hasOption("c")){
			$result = Micron::fbgaToPartNumber($value);
		}elseif($cmd->hasOption("p")){
			$result = Micron::partNumberToFbga($value);
		}
	}elseif($cmd->hasOption("s")){
		if($cmd->hasOption("c")){
			$result = SpecTek::markCodeToPartNumber($value);
		}elseif($cmd->hasOption("p")){
			$result = SpecTek::partNumberToMarkCode($value);
		}
	}
	$result = $result ?? [];
	Util::println("Found " . count($result) . " records.");
	if(count($result) > 0){
		Util::println(json_encode($result, JSON_PRETTY_PRINT));
	}
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("mfcmd", $options));
}
