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

class SandForce extends Generator{
	public static function getDirName() : string{
		return "sf";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		$data = explode("\r\n", $data);//CRLF Windows Format csv
		array_shift($data);//remove header
		$controllers = [];
		foreach($data as $config){
			if(strlen(trim($config)) == 0){
				continue;
			}
			$config = explode(",", preg_replace('/\(.*?\)/', '', $config));
			//CLI Config ID,Release Config ID,Firmware Type,Title,Flash Vendor,Flash Geometry,Flash Type,Flash Part Number,Package Type,Package Count,Package Density,Package Die Count,Raw Capacity,User Capacity,512 LBA Count,512 Plus DIF LBA Count,520 LBA Count,524 LBA Count,528 LBA Count,4096 LBA Count,4096 Plus DIF LBA Count,Non 512 Sector Size,RAISE ON,Version Demo,Version Release,Topology ID,SATA Speed,Write IOPs,Firmware Fuse,Industrial Features,CLI Build ID,Release Build ID,Valid Flag,Release Status,Firmware Worksheet,Validated,Parent Folder
			list($prefix, $controller) = explode("-", $config[2]);
			$controller = "SF" . $controller;
			if(!isset($controllers[$controller])){
				$controllers[$controller] = "";
				$db["info"]["controllers"][] = $controller;
			}
			$manufacturer = str_replace(["hynix"], ["skhynix"], strtolower($config[4]));
			$pn = trim($config[7]);
			if(strlen($pn) <= 3 or StringUtil::contains(strtolower($pn), "custom")){
				continue;
			}
			switch($manufacturer){
				case "skhynix":
					if(StringUtil::contains($pn, "-")){
						$pn = explode("-", $pn)[0];
					}
					$pn = SKHynix::removePackage($pn);
					break;
				case "samsung":
					if(StringUtil::contains($pn, "-")){
						$pn = explode("-", $pn)[0];
					}
					break;
				case "micron":
					$pn = Micron::removePackage($pn);
					break;
				case "sandisk":
					if(strlen($pn) <= 9){//ignoring SDZNPQCHE without -
						continue 2;
					}
					break;
			}
			if(isset($db[$manufacturer][$pn])){
				if(!in_array($controller, $db[$manufacturer][$pn]["t"])){
					$db[$manufacturer][$pn]["t"][] = $controller;
				}
			}else{
				$db[$manufacturer][$pn] = [
					"id" => [],//Flash ID
					"l" => "",//Lithography
					"c" => "",//cell level
					"t" => [$controller],//controller
					"m" => ""
				];
			}
		}
	}
}
