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

namespace iTXTech\FlashDetector\Decoder;

use iTXTech\FlashDetector\Constants;
use iTXTech\FlashDetector\Fdb\PartNumber;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\SimpleFramework\Util\StringUtil;

abstract class Decoder{
	public const CELL_LEVEL = [
		-1 => null,
		1 => "SLC",
		2 => "MLC",
		3 => "TLC",
		4 => "QLC"
	];

	public abstract static function getName() : string;

	public abstract static function check(string $partNumber) : bool;

	public abstract static function decode(string $partNumber) : FlashInfo;

	public static function shiftChars(string &$str, int $num) : string{
		if($num > strlen($str)){
			return "";
		}
		$res = substr($str, 0, $num);
		$str = substr($str, $num);
		return $res;
	}

	public static function getOrDefault($str, array $info, $default = Constants::UNKNOWN){
		return $info[$str] ?? $default;
	}

	public static function matchFromStart(string &$str, array $info, $default = Constants::UNKNOWN){
		$level = [];
		foreach($info as $k => $v){
			if(isset($level[strlen($k)])){
				$level[strlen($k)][$k] = $v;
			}else{
				$level[strlen($k)] = [$k => $v];
			}
		}
		krsort($level);
		foreach($level as $l){
			foreach($l as $k => $v){
				if(StringUtil::startsWith($str, $k)){
					$str = substr($str, strlen($k));
					return $v;
				}
			}
		}
		return $default;
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		return FlashDetector::getFdb()->getPartNumber($info->getVendor(), $info->getPartNumber());
	}
}
