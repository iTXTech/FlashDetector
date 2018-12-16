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
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal();

$options = new Options();
$options->addOption((new OptionBuilder("d"))->longOpt("smi-dbf-dir")->required(true)
	->hasArg(true)->argName("dir")->build());
$options->addOption((new OptionBuilder("v"))->longOpt("set-version")->required(true)
	->hasArg(true)->argName("ver")->build());
$options->addOption((new OptionBuilder("p"))->longOpt("json-pretty")->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	//Flash Database
	$fdb = [
		"info" => [
			"name" => "iTXTech FlashDetector Flash Database",
			"website" => "https://github.com/iTXTech/FlashDetector",
			"version" => $cmd->getOptionValue("v"),
			"time" => date("r"),
			"controllers" => []
		]
	];

	$smiDir = $cmd->getOptionValue("d");
	$dbfs = scandir($smiDir);//Generate from SiliconMotion DBF
	natsort($dbfs);
	foreach($dbfs as $dbf){
		if(StringUtil::endsWith($dbf, ".dbf")){
			$db = file_get_contents($smiDir . DIRECTORY_SEPARATOR . $dbf);
			mergeDbf($fdb, $dbf, $db);
		}
	}

	$file = __DIR__ . DIRECTORY_SEPARATOR . "fdb.json";
	if($cmd->hasOption("p")){
		$json = json_encode($fdb, JSON_PRETTY_PRINT);
	}else{
		$json = json_encode($fdb);
	}
	file_put_contents($file, $json);
	Logger::info("FlashDetector Flash Database has been written to $file");
}catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("fdb_gen", $options));
}

function mergeDbf(array &$fdb, string $filename, string $db){
	$db = explode("\r\n", mb_convert_encoding($db, "UTF-8", "UTF-8"));
	//SMI DBF is in CRLF (Windows) format
	$controller = str_replace(["flash_", ".dbf"], "", $filename);
	$fdb["info"]["controllers"][] = $controller;
	foreach($db as $record){
		if(StringUtil::startsWith($record, "@")){
			$record = substr($record, 2);
			list($id, $info) = explode("// ", $record);
			$fid = explode(" ", $id);
			$id = "";
			for($i = 0; $i < 6; $i++){
				$id .= $fid[$i];
			}
			$comment = "";
			if(StringUtil::contains($info, "//")){
				$comment = trim(substr(strstr($info, "//"), 2));
			}
			$info = explode(" ", str_replace(
				[$comment, "NEW DATE CODE", "OLD DATE CODE", " - ", "L84A HP", "SanDisk SanDisk"],
				["", "", "", "-", "L84A_HP", "SanDisk"],
				$info));
			//Manufacturer, PartNumber, SMICode, Lithography, CellLevel
			foreach($info as $k => $v){
				$v = trim(str_replace(["/", ","], "", $v));
				if($v === ""){
					unset($info[$k]);
				}else{
					$info[$k] = $v;
				}
			}
			$info = array_values($info);
			if(!isset($info[2]) or StringUtil::endsWith($info[1], "nm")){//Sandisk iNAND wrong partNumber
				continue;
			}
			if(strlen($info[2]) !== 5){
				array_splice($info, 2, 0, "");
			}
			$info[0] = str_replace(["samaung", "hynix"], ["samsung", "skhynix"], strtolower($info[0]));
			if(isset($info[3]) and StringUtil::endsWith($info[3], "LC")){
				$cellLevel = $info[3];
				$info[3] = $info[4] ?? "";
				$info[4] = $cellLevel;
			}elseif(isset($info[5]) and strlen($info[5]) < 5){
				$info[3] .= " " . $info[5];
			}
			$data = [
				"id" => [$id],//Flash ID
				"l" => $info[3] ?? "",//Lithography
				"c" => $info[4] ?? "",//cell level
				//"s" => $info[2] ?? "",//SMICode
				"t" => [$controller],//controller
				"m" => $comment
			];
			if(!isset($fdb[$info[0]])){
				$fdb[$info[0]] = [$info[1] => $data];
			}else{
				if(isset($fdb[$info[0]][$info[1]])){
					if(!in_array($id, $fdb[$info[0]][$info[1]]["id"])){
						$fdb[$info[0]][$info[1]]["id"][] = $id;
					}
					if(!in_array($controller, $fdb[$info[0]][$info[1]]["t"])){
						$fdb[$info[0]][$info[1]]["t"][] = $controller;
					}
					$fdb[$info[0]][$info[1]]["l"] = $data["l"];
					$fdb[$info[0]][$info[1]]["c"] = $data["c"];
					$fdb[$info[0]][$info[1]]["m"] = $comment;
				}else{
					$fdb[$info[0]][$info[1]] = $data;
				}
			}
		}
	}

}