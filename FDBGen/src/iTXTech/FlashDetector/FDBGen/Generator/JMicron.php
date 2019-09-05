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
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class JMicron extends Generator{
	public static function getDirName() : string{
		return "jm";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		self::mergeInternal($fdb, $data, $filename, "JMF");
	}

	protected static function mergeInternal(Fdb $fdb, string $data, string $filename, string $prefix) : void{
		$controller = $prefix . explode("_", $filename)[0];
		$data = parse_ini_string($data, true);
		$fdb->getInfo()->addController($controller);
		foreach($data as $vendor => $flashes){
			$vendor = str_replace(["hynix"], ["skhynix"], strtolower($vendor));
			if(in_array($vendor, ["version", "vendor"])){
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
				$pn = trim($info[1]);

				switch($vendor){
					case "sandisk":
						$pn = substr_replace($pn, "-", strpos($pn, "0"), 0);
						break;
					case "toshiba":
						if(strlen($pn) == 17){
							$pn = substr($pn, 0, 15);
						}
						break;
					case "micron":
						$pn = Micron::removePackage($pn);
						break;
					case "skhynix":
						$pn = SKHynix::removePackage($pn);
						break;
				}

				$partNumber = $fdb->getPartNumber($vendor, $pn, true)
					->addController($controller);
				if(strlen($id) == 12){
					$partNumber->addFlashId($id);
				}
			}
		}
	}
}
