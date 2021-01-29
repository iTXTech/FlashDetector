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

//Flash Database Generator

require_once "env.php";

use iTXTech\FlashDetector\FDBGen\FDBGen;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Util\Util;

global $moduleManager;
loadModule($moduleManager, "FDBGen");

FDBGen::init();

Logger::$logLevel = 0;

$options = new Options();
$options->addOption((new OptionBuilder("v"))->longOpt("version")
	->desc("FDB file version, optional")->hasArg(true)->argName("ver")->build())
	->addOption((new OptionBuilder("i"))->longOpt("input")->required()
		->desc("Input dir or file")->hasArg(true)->argName("file")->build())
	->addOption((new OptionBuilder("o"))->longOpt("output")->required()
		->desc("Output file")->hasArg(true)->argName("file")->build())
	->addOption((new OptionBuilder("p"))->longOpt("pretty")->desc("JSON pretty output")->build())
	->addOption((new OptionBuilder("e"))->longOpt("extra")->desc("Include Extra.json")->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	$fdb = FDBGen::generate($cmd->getOptionValue("v", "Undefined"),
		$cmd->getOptionValue("i"), $cmd->hasOption("e"));
	if($cmd->hasOption("p")){
		$fdb = json_encode($fdb, JSON_PRETTY_PRINT);
	}else{
		$fdb = json_encode($fdb);
	}
	file_put_contents($cmd->getOptionValue("o"), $fdb);
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("fdbgen", $options));
}
