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
