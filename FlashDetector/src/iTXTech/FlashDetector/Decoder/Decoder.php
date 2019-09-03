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
		return FlashDetector::getFdb()->getPartNumber($info->getManufacturer(), $info->getPartNumber());
	}
}
