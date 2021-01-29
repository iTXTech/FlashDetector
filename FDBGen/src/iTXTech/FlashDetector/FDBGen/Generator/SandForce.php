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

use iTXTech\FlashDetector\Decoder\Micron;
use iTXTech\FlashDetector\Decoder\SKHynix;
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class SandForce extends Generator{
	public static function getDirName() : string{
		return "sf";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
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
				$fdb->getInfo()->addController($controller);
			}
			$vendor = str_replace(["hynix"], ["skhynix"], strtolower($config[4]));
			$pn = trim($config[7]);
			if(strlen($pn) <= 3 or StringUtil::contains(strtolower($pn), "custom")){
				continue;
			}
			switch($vendor){
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

			$fdb->getPartNumber($vendor, $pn, true)
				->addController($controller);
		}
	}
}
