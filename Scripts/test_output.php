<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

//Test Output

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Util\Util;

const PNS = [
	"29F64G08AAMF1",
	"MT29F32G08AFABA",
	"K9HDGD8U5M",
	"SDTNPMCHEM-032G",
	"SDIN5C4-32G",//iNAND
	"H27UDG8M2MTR",
	"H2JTDG8UD2MBR",//E2NAND
	"TH58TEG8D2HTA20",
	"THGVX1G7D2GLA08",//E2NAND
	"FNNL06B256G1KDBABWP"
];

$options = new Options();
$options->addOption((new OptionBuilder("l"))->longOpt("lang")
	->hasArg()->argName("lang")->required(true)->build());
$options->addOption((new OptionBuilder("r"))->longOpt("raw")->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	FlashDetector::init($cmd->getOptionValue("l"));

	foreach(PNS as $pn){
		$data = FlashDetector::detect($pn);
		Util::println(json_encode($data->toArray($cmd->hasOption("r")),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("test_output", $options));
}
