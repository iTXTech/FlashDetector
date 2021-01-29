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

class SiliconMotionUFD extends Generator{
	public static function getDirName() : string{
		return "smufd";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$data = explode("\r\n", mb_convert_encoding($data, "UTF-8", "UTF-8"));
		//SMI DBF is in CRLF (Windows) format
		$controller = "SM" . str_replace(["flash_", ".dbf"], "", $filename);
		$fdb->getInfo()->addController($controller);
		foreach($data as $record){
			if(StringUtil::startsWith($record, "@")){
				$record = substr($record, 2);
				list($id, $info) = explode("// ", $record);
				$fid = explode(" ", $id);
				$id = "";
				for($i = 0; $i < 6; $i++){
					$id .= $fid[$i];
				}
				$remark = "";
				if(StringUtil::contains($info, "//")){
					$remark = trim(substr(strstr($info, "//"), 2));
				}
				$info = preg_replace("/3D V(\d)/", "3DV$1", $info);
				$info = explode(" ", str_replace(
					[$remark, "NEW DATE CODE", "OLD DATE CODE", " - ", "L84A HP", "SanDisk SanDisk", "-ES"],
					["", "", "", "-", "L84A_HP", "SanDisk", "ES"], $info));
				//Vendor, PartNumber, SMIPackageCode, Lithography, CellLevel, NodeCodename
				foreach($info as $k => $v){
					$v = trim(str_replace(["/", ","], "", $v));
					if($v === ""){
						unset($info[$k]);
					}else{
						$info[$k] = $v;
					}
				}
				$info = array_values($info);
				if(!isset($info[2]) or StringUtil::endsWith($info[1], "nm")){//Sandisk iNAND wrong partNumber
					continue;
				}
				if(strlen($info[2]) !== 5){
					array_splice($info, 2, 0, "");
				}
				$vendor = str_replace(["samaung", "hynix", "speteck"], ["samsung", "skhynix", "spectek"], strtolower($info[0]));
				if(isset($info[3]) and StringUtil::endsWith($info[3], "LC")){
					$cellLevel = $info[3];
					$info[3] = $info[4] ?? "";
					$info[4] = $cellLevel;
				}elseif(isset($info[5]) and strlen($info[5]) < 5){
					$info[3] .= " " . $info[5];
					$info[5] = "";
				}
				switch($vendor){
					case "skhynix":
						$info[1] = SKHynix::removePackage($info[1]);
						break;
					case "micron":
						$info[1] = Micron::removePackage($info[1]);
						break;
					case "spectek":
						$info[1] = SpecTek::removePackage($info[1]);
						break;
				}

				$pn = $fdb->getPartNumber($vendor, $info[1], true)
					->addFlashId($id)
					->addController($controller)
					->setRemark($remark);

				$fdb->getIddb()->getFlashId($id, true)->addController($controller);

				if(($info[3] ?? null) != null){
					$pn->setProcessNode($info[3]);
				}
			}
		}
	}
}
