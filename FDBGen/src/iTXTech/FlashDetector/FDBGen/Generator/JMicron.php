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
						if(strlen($pn) > 15){
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
					$fdb->getIddb()->getFlashId($id, true)->addController($controller);
				}
			}
		}
	}
}
