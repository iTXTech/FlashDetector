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

use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\{
	HelpFormatter,
	Options,
	OptionBuilder,
	Parser
};
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal();

$options = new Options();
$options->addOption((new OptionBuilder("d"))->longOpt("smi-dbf-dir")->required(true)
	->hasArg(true)->argName("dir")->build());

try{
	//Flash Database
	$fdb = [];

	$cmd = (new Parser())->parse($options, $argv);
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
	file_put_contents($file, json_encode($fdb, JSON_PRETTY_PRINT));
	Logger::info("FlashDetector Flash Database has been written to $file");
} catch(ParseException $e){
	Util::println($e->getMessage());
	echo((new HelpFormatter())->generateHelp("fdb_gen", $options));
}

function mergeDbf(array &$fdb, string $filename, string $db){
	$db = explode("\r\n", mb_convert_encoding($db, "UTF-8", "UTF-8"));//SMI DBF is in CRLF (Windows) format
	foreach($db as $record){
		if(StringUtil::startsWith($record, "@")){
			$record = substr($record, 2);
			list($id, $info) = explode(" // ", $record);
			$fid = explode(" ", $id);
			$id = "";
			for($i = 0; $i < 6; $i++){
				$id .= $fid[$i];
			}
			$comment = "";
			if(StringUtil::contains($info, "//")){
				$comment = trim(substr(strstr($info, "//"), 2));
			}
			$info = explode(" ", str_replace($comment, "", $info));//Manufacturer, PartNumber, SMICode, Lithography, CellLevel
			foreach($info as $k => $v){
				$info[$k] = trim(str_replace("/", "", $v));
			}
			$controller = str_replace(["flash_", ".dbf"], "", $filename);
			$info[0] = strtolower($info[0]);
			if($info[0] === "samaung"){
				$info[0] = "samsung";
			}
			$data = [
				"id" => [$id],//Flash ID
				"l" => $info[3] ?? "",//Lithography
				"c" => $info[4] ?? "",//cell level
				//"s" => $info[2] ?? "",//SMICode
				"t" => [$controller],//controller
			];
			if($comment !== ""){
				$data["m"] = $comment;
			}
			if(!isset($fdb[$info[0]])){
				$fdb[$info[0]] = [$info[1] => $data];
			} else {
				if(isset($fdb[$info[0]][$info[1]])){
					if(!in_array($id, $fdb[$info[0]][$info[1]]["id"])){
						$fdb[$info[0]][$info[1]]["id"][] = $id;
					}
					$fdb[$info[0]][$info[1]]["t"][] = $controller;
					if($fdb[$info[0]][$info[1]]["l"] === ""){
						$fdb[$info[0]][$info[1]]["l"] = $data["l"];
					}
					if($fdb[$info[0]][$info[1]]["c"] === ""){
						$fdb[$info[0]][$info[1]]["c"] = $data["c"];
					}
				} else{
					$fdb[$info[0]][$info[1]] = $data;
				}
			}
		}
	}

}