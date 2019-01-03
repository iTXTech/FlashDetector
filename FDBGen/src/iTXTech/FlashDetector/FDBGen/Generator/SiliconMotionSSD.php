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

class SiliconMotionSSD extends Generator{
	public static function getDirName() : string{
		return "smssd";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		$controller = "SM" . explode("_", $filename)[0];
		$db["info"]["controllers"][] = $controller;
		$data = explode("\r\n", $data);
		foreach($data as $k => $config){
			if(StringUtil::startsWith($config, "A") and
				!StringUtil::startsWith($data[$k + 1], "A") and
				!StringUtil::endsWith($config, "[END]")){
				list($flash, $info) = explode("=", $data[$k + 1], 2);
				list($manufacturer, $density, $pn) = explode(",", $flash, 3);
				$manufacturer = str_replace(
					["hynix", "stm", "power flash"],
					["skhynix", "st", "powerchip"],
					strtolower($manufacturer));
				$pn = trim(preg_replace('/\(.*?\)/', '', $pn));
				if(StringUtil::contains($pn, "-")){
					$pn = trim(explode("-", $pn)[0]);
				}
				if(StringUtil::contains($pn, "_")){
					$pn = trim(explode("_", $pn)[0]);
				}
				switch($manufacturer){
					case "micron":
						$pn = Micron::removePackage($pn);
						break;
					case "skhynix":
						$pn = SKHynix::removePackage($pn);
						break;
				}
				$rawId = explode(",", $info);
				$id = "";
				for($i = 0; $i < 6; $i++){
					$id .= $rawId[$i];
				}
				if(isset($db[$manufacturer][$pn])){
					if(!in_array($id, $db[$manufacturer][$pn]["id"])){
						$db[$manufacturer][$pn]["id"][] = $id;
					}
					if(!in_array($controller, $db[$manufacturer][$pn]["t"])){
						$db[$manufacturer][$pn]["t"][] = $controller;
					}
				}else{
					$db[$manufacturer][$pn] = [
						"id" => [$id],//Flash ID
						"l" => "",//Lithography
						"c" => "",//cell level
						"t" => [$controller],//controller
						"m" => ""
					];
				}
			}
		}
	}
}
