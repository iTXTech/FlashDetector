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
		->hasArg()->argName("Part Number")->build());
$group->setRequired(true);
$options = new Options();
$options->addOptionGroup($group);

try{
	$cmd = (new Parser())->parse($options, $argv);
	if($cmd->hasOption("i")){
		$info = FlashDetector::searchFlashId($cmd->getOptionValue("i"), true, true);
	}
	if($cmd->hasOption("p")){
		$info = FlashDetector::searchPartNumber($cmd->getOptionValue("p"), true, true);
	}
	Util::println(json_encode($info, JSON_PRETTY_PRINT));
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("rs", $options));
}
