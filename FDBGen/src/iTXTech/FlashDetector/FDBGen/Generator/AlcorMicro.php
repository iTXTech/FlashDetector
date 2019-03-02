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

class AlcorMicro extends Generator{
	public static function getDirName() : string{
		return "al";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		$data = explode("\r\n", $data);
		$cons = explode(",", array_shift($data));
		foreach($cons as $k => $con){
			$db["info"]["controllers"][] = $con;
		}
		foreach($data as $k){
			if(trim($k) == ""){
				continue;
			}
			//Vendor,Type,Capacity,Part Number,Process Node,nCE,AU6989SN-GTC,AU6989SN-GTD,AU6989SN-GTE
			$blocks = explode(",", $k);
			list($manufacturer, $cellLevel, $density, $pn, $processNode) = $blocks;
			$manufacturer = str_replace(["powerflash", "hynix"], ["powerchip", "skhynix"], strtolower($manufacturer));
			$pn = trim($pn);
			$processNode = strlen($processNode) > 1 ? $processNode : "";
			switch($manufacturer){
				case "micron":
					$pn = Micron::removePackage($pn);
					break;
				case "skhynix":
					if(StringUtil::contains($pn, "-")){
						$pn = explode("-", $pn)[0];
					}
					$pn = SKHynix::removePackage($pn);
					break;
			}
			if(isset($db[$manufacturer][$pn])){
				if($db[$manufacturer][$pn]["c"] == ""){
					$db[$manufacturer][$pn]["c"] = $cellLevel;
				}
				if($db[$manufacturer][$pn]["l"] == ""){
					$db[$manufacturer][$pn]["l"] = $processNode;
				}
				foreach($cons as $controller){
					if(!in_array($controller, $db[$manufacturer][$pn]["t"])){
						$db[$manufacturer][$pn]["t"][] = $controller;
					}
				}
			}else{
				$db[$manufacturer][$pn] = [
					"id" => [],//Flash ID
					"l" => $processNode,//Lithography
					"c" => $cellLevel,//cell level
					"t" => $cons,//controller
					"m" => ""
				];
			}
		}
	}
}
