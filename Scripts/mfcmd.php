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
