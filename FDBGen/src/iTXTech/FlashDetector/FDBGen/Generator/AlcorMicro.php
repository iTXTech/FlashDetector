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
use iTXTech\FlashDetector\Decoder\SpecTek;
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class AlcorMicro extends Generator{
	public const CON_OFFSET = 6;

	public static function getDirName() : string{
		return "al";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$data = explode("\r\n", $data);
		$cons = explode(",", array_shift($data));
		foreach($cons as $k => $con){
			$fdb->getInfo()->addController($con);
		}
		foreach($data as $k){
			if(trim($k) == ""){
				continue;
			}
			//Vendor,Type,Capacity,Part Number,Process Node,nCE,AU6989SN-GTC,AU6989SN-GTD,AU6989SN-GTE
			$rec = explode(",", $k);
			list($vendor, $cellLevel, $density, $pn, $processNode) = $rec;
			$vendor = str_replace(["powerflash", "hynix"], ["powerchip", "skhynix"], strtolower($vendor));
			$pn = trim($pn);
			$processNode = strlen($processNode) > 1 ? $processNode : "";
			$sup = [];
			for($i = self::CON_OFFSET; $i < count($rec); $i++){
				if(isset($rec[$i]) and $rec[$i] == "Y"){
					$sup[] = $cons[$i - self::CON_OFFSET];
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
					if(StringUtil::contains($pn, "-")){
						$pn = explode("-", $pn)[0];
					}
					$pn = SKHynix::removePackage($pn);
					break;
			}

			$fdb->getPartNumber($vendor, $pn, true)
				->setCellLevel($cellLevel)
				->setProcessNode($processNode)
				->addController($sup);
		}
	}
}
