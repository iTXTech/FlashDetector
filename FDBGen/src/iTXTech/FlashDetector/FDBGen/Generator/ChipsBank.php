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

class ChipsBank extends Generator{
	public const CON_OFFSET = 9;

	public static function getDirName() : string{
		return "cbm";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$data = explode("\r\n", $data);
		$cons = explode(",", array_shift($data));
		foreach($cons as $k => $con){
			$cons[$k] = "CBM" . $con;
			$fdb->getInfo()->addController($cons[$k]);
		}
		foreach($data as $rec){
			if(trim($rec) == ""){
				continue;
			}
			$rec = explode(",", $rec);
			array_shift($rec);
			//Num#,Vendor,Capacity,Type,Part Number,FlashId,nCE,ECC bits,Process Node,Bus Width,2099S,2199S,2099E,2199
			$vendor = str_replace(["hynix"], ["skhynix"], strtolower($rec[0]));
			$flashId = str_replace(["/", " "], "", $rec[4]);
			if(strlen($flashId) > 12){
				$flashId = substr($flashId, 0, 12);
			}
			if($fdb->getVendor($vendor) == null){
				continue;
			}
			$found = false;
			$sup = [];
			for($i = self::CON_OFFSET; $i < count($rec); $i++){
				if(isset($rec[$i]) and $rec[$i] == "Y"){
					$sup[] = $cons[$i - self::CON_OFFSET];
				}
			}
			foreach($fdb->getVendor($vendor)->getPartNumbers() as $pn => $flash){
				foreach($flash->getFlashIds() as $id){
					if(StringUtil::startsWith($id, $flashId)){
						$fdb->getIddb()->getFlashId($id)->addController($sup);
						$flash->addController($sup);
						$found = true;
						break;
					}
				}
			}
			if(!$found){
				$pn = str_replace(["(T)", "(TOG)", "(TOG"], "", $rec[3]);
				switch($vendor){
					case "spectek":
						$pn = SpecTek::removePackage($pn);
						break;
					case "micron":
						$pn = Micron::removePackage($pn);
					case "toshiba":
					case "intel":
						$pn = explode("(", $pn)[0];
						break;
					case "mira":
					case "powerchip":
						$pn = "";
						break;
					case "skhynix":
						$pn = SKHynix::removePackage($pn);
						break;
				}
				if($pn !== ""){
					if(in_array($pn{strlen($pn) - 2}, ["_", "*"])){
						$pn = substr($pn, 0, strlen($pn) - 2);
					}

					$fdb->getPartNumber($vendor, $pn, true)
						->addFlashId($flashId)
						->addController($sup)
						->setProcessNode($rec[7])
						->setCellLevel(explode("-", $rec[2])[0]);

					$pageSize = explode("-", $rec[2])[1];
					if(StringUtil::endsWith($pageSize, "K")){
						$pageSize = substr($pageSize, 0, strlen($pageSize) - 1);
					}elseif(is_numeric($pageSize)){
						$pageSize /= 1024;
					}else{
						$pageSize = 0;
					}
					$fdb->getIddb()->getFlashId($flashId, true)->setPageSize($pageSize)->addController($sup);
				}
			}
		}
	}
}
