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

require_once "../sf/autoload.php";

ini_set("memory_limit", -1);

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\{HelpFormatter, OptionBuilder, Options, Parser};
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal();

$options = new Options();
$options->addOption((new OptionBuilder("p"))->longOpt("json-pretty")->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	$fdb = json_decode(file_get_contents("fdb.json"), true);
	$iddb = [
		"info" => [
			"name" => "iTXTech FlashDetector Flash Identifier Database",
			"website" => "https://github.com/iTXTech/FlashDetector",
			"version" => $fdb["info"]["version"],
			"time" => date("r")
		]
	];
	unset($fdb["info"]);
	foreach($fdb as $k => $v){
		foreach($v as $partNumber => $i){
			foreach($i["id"] as $id){
				if(!isset($iddb[$id])){
					$iddb[$id] = [$k . " " . $partNumber];
				}else{
					$iddb[$id][] = $k . " " . $partNumber;
				}
			}
		}
	}

	$file = __DIR__ . DIRECTORY_SEPARATOR . "iddb.json";
	if($cmd->hasOption("p")){
		$json = json_encode($iddb, JSON_PRETTY_PRINT);
	}else{
		$json = json_encode($iddb);
	}
	file_put_contents($file, $json);
	Logger::info("FlashDetector Flash Identifier Database has been written to $file");
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("fdb_gen", $options));
}