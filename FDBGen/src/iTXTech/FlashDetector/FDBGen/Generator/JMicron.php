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

use iTXTech\SimpleFramework\Util\StringUtil;

class JMicron extends Generator{
	public static function getDirName() : string{
		return "jm";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		self::mergeInternal($db, $data, $filename, "JMF");
	}

	protected static function mergeInternal(array &$db, string $data, string $filename, string $prefix) : void{
		$controller = $prefix . explode("_", $filename)[0];
		$data = parse_ini_string($data, true);
		$db["info"]["controllers"][] = $controller;
		foreach($data as $manufacturer => $flashes){
			$manufacturer = str_replace(["hynix"], ["skhynix"], strtolower($manufacturer));
			if(in_array($manufacturer, ["version", "vendor"])){
				continue;
			}
			foreach($flashes as $flash){
				$info = explode(" ", $flash);
				$id = "";
				foreach($info as $k => $v){
					if($v === ""){
						unset($info[$k]);
					}else{
						$v = trim($v);
						$info[$k] = $v;
					}
					if(strlen($v) == 4 and StringUtil::startsWith($v, "0x")){
						$id .= substr($v, 2, 2);
					}
				}
				$info = array_values($info);
				if($id === str_repeat("0", strlen($id))){
					continue;
				}
				$pn = $info[1];

				switch($manufacturer){
					case "sandisk":
						$pn = substr_replace($pn, "-", strpos($pn, "0"), 0);
						break;
					case "toshiba":
						if(strlen($pn) == 17){
							$pn = substr($pn, 0, 15);
						}
						break;
					case "micron":
						$bit = strstr($pn, "08");
						if($bit !== false and strlen($bit) >= 8){
							$pn = substr($pn, 0, strlen($pn) + 7 - strlen($bit));
						}

						break;
				}

				if(isset($db[$manufacturer][$pn])){
					if(strlen($id) == 12 and !in_array($id, $db[$manufacturer][$pn]["id"])){
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
