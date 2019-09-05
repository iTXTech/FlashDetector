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
