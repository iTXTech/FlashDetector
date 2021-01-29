<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2021 iTX Technologies
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
							->setPagesPerBlock($flash["Pagesperblock"])->setBlocks($flash["Blocks"])
							->addController($controller);
						break;
					}
				}
			}
		}
	}
}
