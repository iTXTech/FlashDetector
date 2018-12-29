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

use iTXTech\FlashDetector\Decoder\SKHynix;

class Innostor extends Generator{
	public static function getDirName() : string{
		return "is";
	}

	public static function merge(array &$db, string $data, string $filename) : void{
		$controller = "IS" . explode("_", $filename)[0];
		$data = parse_ini_string($data, true);
		$db["info"]["controllers"][] = $controller;
		foreach($data as $manufacturer => $flashes){
			$manufacturer = str_replace(["psc", "hynix"], ["powerchip", "skhynix"], strtolower($manufacturer));
			if($manufacturer == "flashdb version"){
				continue;
			}
			foreach($flashes as $flash){
				list($pn, $id, $ce) = explode("-", $flash);
				$id = substr($id, 0, 12);
				switch($manufacturer){
					case "sandisk":
						$pn = str_replace("_", "-", $pn);//_032G -> -032G
						break;
					case "skhynix":
						$pn = SKHynix::removePackage($pn);
						break;
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
