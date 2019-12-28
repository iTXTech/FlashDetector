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

use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class Innostor extends Generator{
	public static function getDirName() : string{
		return "is";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$controller = "IS" . explode("_", $filename)[0];
		$d = [];
		foreach(explode("\r\n", $data) as $line){
			if(!StringUtil::startsWith($line, "//") and !StringUtil::startsWith($line, "~")){
				$d[] = $line;
			}
		}
		$data = parse_ini_string(implode("\n", $d), true, INI_SCANNER_RAW);
		$fdb->getInfo()->addController($controller);

		foreach($data as $id => $flash){
			if(!isset($flash["Vendor"])){
				continue;
			}
			$vendor = str_replace(["hynix", "psc"], ["skhynix", "powerchip"], strtolower($flash["Vendor"]));
			$flashId = $flash["FlashID"];
			foreach($fdb->getVendor($vendor)->getPartNumbers() as $partNumber){
				foreach($partNumber->getFlashIds() as $id){
					if(StringUtil::startsWith($id, $flashId)){
						$partNumber->addController($controller);
						$fdb->getIddb()->getFlashId($id, true)->setPageSize(round($flash["PageSize"] / 1024))
							->setPagesPerBlock($flash["Pagesperblock"]);
						break;
					}
				}
			}
		}
	}
}
