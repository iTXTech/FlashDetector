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

class SiliconMotionForceFlash extends Generator{
	public static function getDirName() : string{
		return "smff";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$data = explode("\r\n", mb_convert_encoding($data, "UTF-8", "UTF-8"));
		foreach($data as $line => $d){
			if(count($parts = explode("=", $d)) > 1 and !StringUtil::contains($parts[1], ",")){
				$d = explode(",", explode("_", $d)[0]);
				if(count($d) == 6){
					for($i = 0; $i < 6; $i++){
						if(strlen($d[$i]) == 1){
							$d[$i] = "0" . $d[$i];
						}
					}
					$d = implode("", $d);//FlashID
					$fid = $fdb->getIddb()->getFlashId($d, true);
					$index = explode("=", $data[$line + 1])[1];
					if(StringUtil::contains($index, "Page")){
						$end = $start = strpos($index, "Page");
						while($index{--$start} == "0" or ((int) $index{$start} > 0)) ;
						$start++;
						$fid->setPagesPerBlock((int) substr($index, $start, $end - $start));
					}
					foreach(["12", "16", "4", "8", "2"] as $p){
						if(StringUtil::contains($index, $p . "K")){
							$fid->setPageSize((int) $p);
							break;
						}
					}
					$info = explode(",", $index);
					$pn = explode("_", str_replace(" ", "", explode("(", @end($info))[0]))[0];
					$vendor = str_replace("skhynixnix", "skhynix",
						str_replace([" ", "tsb", "ss", "hy", "hynix"], ["", "toshiba", "samsung", "hynix", "skhynix"],
							strtolower(explode("_", $info[0])[0])));
					if(is_numeric($vendor{strlen($vendor) - 1})){
						$vendor = substr($vendor, 0, strlen($vendor) - 1);
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

					$fdb->getPartNumber($vendor, $pn, true)
						->addFlashId($d);

					$da = explode(",", explode("=", $data[$line + 2])[1]);
					if(isset($da[11])){
						$fid->setBlocks(hexdec($da[11] . $da[12]));
					}
				}
			}
		}
	}
}
