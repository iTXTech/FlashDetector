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

namespace iTXTech\FlashDetector\FDBGen\Generator;

use iTXTech\FlashDetector\Decoder\Micron;
use iTXTech\FlashDetector\Decoder\SKHynix;
use iTXTech\SimpleFramework\Util\StringUtil;

class SiliconMotionUFD extends Generator{
	public static function getDirName() : string{
		return "smufd";
	}

	//Don't look at it, may do harm to you
	public static function merge(array &$db, string $data, string $filename) : void{
		$data = explode("\r\n", mb_convert_encoding($data, "UTF-8", "UTF-8"));
		//SMI DBF is in CRLF (Windows) format
		$controller = "SM" . str_replace(["flash_", ".dbf"], "", $filename);
		$db["info"]["controllers"][] = $controller;
		foreach($data as $record){
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
					[$comment, "NEW DATE CODE", "OLD DATE CODE", " - ", "L84A HP", "SanDisk SanDisk", "-ES"],
					["", "", "", "-", "L84A_HP", "SanDisk", "ES"],
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
				switch($info[0]){
					case "skhynix":
						$info[1] = SKHynix::removePackage($info[1]);
						break;
					case "micron":
						$info[1] = Micron::removePackage($info[1]);
						break;
				}
				$data = [
					"id" => [$id],//Flash ID
					"l" => $info[3] ?? "",//Lithography
					"c" => $info[4] ?? "",//cell level
					//"s" => $info[2] ?? "",//SMICode
					"t" => [$controller],//controller
					"m" => $comment
				];
				if(isset($db[$info[0]][$info[1]])){
					if(!in_array($id, $db[$info[0]][$info[1]]["id"])){
						$db[$info[0]][$info[1]]["id"][] = $id;
					}
					if(!in_array($controller, $db[$info[0]][$info[1]]["t"])){
						$db[$info[0]][$info[1]]["t"][] = $controller;
					}
					$db[$info[0]][$info[1]]["l"] = $data["l"];
					$db[$info[0]][$info[1]]["c"] = $data["c"];
					$db[$info[0]][$info[1]]["m"] = $comment;
				}else{
					$db[$info[0]][$info[1]] = $data;
				}
			}
		}
	}
}
