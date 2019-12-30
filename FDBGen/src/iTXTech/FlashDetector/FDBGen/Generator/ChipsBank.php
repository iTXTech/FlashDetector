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
