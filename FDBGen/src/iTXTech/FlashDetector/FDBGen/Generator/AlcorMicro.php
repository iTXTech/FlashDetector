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

namespace iTXTech\FlashDetector\FDBGen\Generator;

use iTXTech\FlashDetector\Decoder\Micron;
use iTXTech\SimpleFramework\Util\StringUtil;

class AlcorMicro extends Generator{
	public static function getDirName() : string{
		return "al";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		$data = explode("\r\n", $data);
		$controllers = [];
		foreach($data as $k){
			$blocks = explode(" ", $k);
			if(trim($k) == "" or StringUtil::startsWith($k, "#")){
				continue;
			}
			if($blocks[0] == "controllers"){
				array_shift($blocks);
				$controllers = $blocks;
				foreach($blocks as $con){
					$db["info"]["controllers"][] = $con;
				}
				continue;
			}
			list($manufacturer, $cellLevel, $density, $pn, $processNode) = $blocks;
			$manufacturer = strtolower($manufacturer);
			switch($manufacturer){
				case "micron":
					$pn = Micron::removePackage($pn);
					break;
			}
			if(isset($db[$manufacturer][$pn])){
				if($db[$manufacturer][$pn]["c"] == ""){
					$db[$manufacturer][$pn]["c"] = $cellLevel;
				}
				if($db[$manufacturer][$pn]["l"] == ""){
					$db[$manufacturer][$pn]["l"] = $processNode;
				}
				foreach($controllers as $controller){
					if(!in_array($controller, $db[$manufacturer][$pn]["t"])){
						$db[$manufacturer][$pn]["t"][] = $controller;
					}
				}
			}else{
				$db[$manufacturer][$pn] = [
					"id" => [],//Flash ID
					"l" => $processNode,//Lithography
					"c" => $cellLevel,//cell level
					"t" => $controllers,//controller
					"m" => ""
				];
			}
		}

	}
}
