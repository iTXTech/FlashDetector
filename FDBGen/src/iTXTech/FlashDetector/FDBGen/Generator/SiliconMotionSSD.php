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

class SiliconMotionSSD extends Generator{
	private const CONTROLLERS = [
		"2258XT" => null,
		"2259XT" => null,
		"2258" => null,
		"58XT" => "2258XT",
		"2259AB" => "2259",
		"2258AB" => "2258"
	];

	public static function getDirName() : string{
		return "smssd";
	}

	public static function merge(Fdb $fdb, string $data, string $filename) : void{
		$controller = "SM" . explode("_", $filename)[0];
		//新版本SM2258XT的Flash.SET格式变成更加完整的列表，包含SM2258系列，SM2259系列的配置
		//标识前缀也变为 B
		$prefix = $controller == "SM2258XT" ? "B" : "A";
		//部分支持不在新版列表中体现，比如SKHY16nm/14nm，Kioxia BiCS2等
		if($controller == "SM2258XTLEGACY"){
			$controller = "SM2258XT";
		}
		$fdb->getInfo()->addController($controller);
		$data = explode("\r\n", $data);
		foreach($data as $k => $config){
			if(StringUtil::startsWith($config, $prefix) and
				!StringUtil::startsWith($data[$k + 1], $prefix) and
				!StringUtil::endsWith($config, "[END]")){
				list($flash, $info) = explode("=", $data[$k + 1], 2);
				list($vendor, $density, $pn) = explode(",", $flash, 3);
				//更正错误厂商命名
				$vendor = str_replace(
					["hynix", "stm", "power flash"],
					["skhynix", "st", "powerchip"],
					strtolower($vendor));
				if($prefix == "B"){
					$matches = [];
					preg_match_all('/(?<=\(SM)[^)]+/', $pn, $matches);
					$matches = $matches[0];
					if(count($matches) > 0){
						$matches = $matches[0];
						if(in_array($matches, array_keys(self::CONTROLLERS))){
							$con = "SM" . (self::CONTROLLERS[$matches] ?? $matches);
						}
					}
					if(isset($con)){//在prefix为B的模式下，只有有主控后缀的才被加入FDB
						$controller = $con;
						unset($con);
					}else{
						continue;
					}
				}
				$pn = trim(preg_replace('/\(.*?\)/', '', $pn));
				if($vendor == "sandisk"){
					if(StringUtil::startsWith($pn, "SNDK ") and strlen(substr($pn, 5)) > 5){
						$pn = str_replace(["-8G", "-16G", "-32G", "-64G"],
							["-008G", "-016G", "-032G", "-064G"],
							str_replace(["  ", " "], "-", substr($pn, 5)));
					}
					$pn = explode("_", str_replace(["Toggle)", " "], ["", "_"], $pn));
					if(StringUtil::startsWith($pn[count($pn) - 1], "DDR")){
						unset($pn[count($pn) - 1]);
					}
					$pn = str_replace(["---", "--"], "-", implode("-", $pn));
					if(StringUtil::endsWith($pn, "-")){
						$pn = substr($pn, 0, strlen($pn) - 1);
					}
				}else{
					foreach(["-", "_", " "] as $char){
						if(StringUtil::contains($pn, $char)){
							$pn = trim(explode($char, $pn)[0]);
						}
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
						$pn = SKHynix::removePackage($pn);
						break;
				}
				$rawId = explode(",", $info);
				$id = "";
				for($i = 0; $i < 6; $i++){
					$id .= $rawId[$i];
				}
				if($pn != "TSB"){
					$fdb->getPartNumber($vendor, $pn, true)
						->addFlashId($id)
						->addController($controller);
				}
				$fdb->getIddb()->getFlashId($id, true)->addController($controller);
			}
		}
	}
}
