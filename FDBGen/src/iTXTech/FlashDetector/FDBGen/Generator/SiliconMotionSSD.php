<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2020 iTX Technologies
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
use iTXTech\FlashDetector\Decoder\SpecTek;
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class SiliconMotionSSD extends Generator{
	public static function getDirName() : string{
		return "smssd";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$controller = "SM" . explode("_", $filename)[0];
		$fdb->getInfo()->addController($controller);
		$data = explode("\r\n", $data);
		foreach($data as $k => $config){
			if(StringUtil::startsWith($config, "A") and
				!StringUtil::startsWith($data[$k + 1], "A") and
				!StringUtil::endsWith($config, "[END]")){
				list($flash, $info) = explode("=", $data[$k + 1], 2);
				list($vendor, $density, $pn) = explode(",", $flash, 3);
				$vendor = str_replace(
					["hynix", "stm", "power flash"],
					["skhynix", "st", "powerchip"],
					strtolower($vendor));
				$pn = trim(preg_replace('/\(.*?\)/', '', $pn));
				if($vendor == "sandisk"){
					if(StringUtil::startsWith($pn, "SNDK ") and strlen(substr($pn, 5)) > 5){
						$pn = str_replace(["-8G", "-16G", "-32G", "-64G"],
							["-008G", "-016G", "-032G", "-064G"],
							str_replace(["  ", " "], "-", substr($pn, 5)));
					}
					$pn = explode("_", str_replace(["Toggle)", " "], ["", "_"], $pn));
					if(StringUtil::startsWith($pn[count($pn) - 1], "DDR")){
						unset($pn[count($pn) - 1]);
					}
					$pn = str_replace(["---", "--"], "-", implode("-", $pn));
					if(StringUtil::endsWith($pn, "-")){
						$pn = substr($pn, 0, strlen($pn) - 1);
					}
				}else{
					foreach(["-", "_", " "] as $char){
						if(StringUtil::contains($pn, $char)){
							$pn = trim(explode($char, $pn)[0]);
						}
					}
				}
				switch($vendor){
					case "spectek":
						$pn = SpecTek::removePackage($pn);
						break;
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

				$fdb->getPartNumber($vendor, $pn, true)
					->addFlashId($id)
					->addController($controller);
				$fdb->getIddb()->getFlashId($id, true)->addController($controller);
			}
		}
	}
}
