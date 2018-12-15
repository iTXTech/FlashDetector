<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

use iTXTech\FlashDetector\Classification;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\SimpleFramework\Util\StringUtil;

class Micron extends Decoder{
	public static function getName() : string{
		return "Micron";
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "mt")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$partNumber = substr($partNumber, 2, strlen($partNumber));//remove MT
		$level = self::getOrUnknown(self::shiftChars($partNumber, 3), [
			"29f" => "NAND Flash",
			"29e" => "Enterprise NAND Flash"
		]);
		$density = self::matchFromStart($partNumber, [
			"1g" => "1Gb",
			"2g" => "2Gb",
			"4g" => "4Gb",
			"8g" => "8Gb",
			"16g" => "16Gb",
			"32g" => "32Gb",
			"64g" => "64Gb",
			"128g" => "128Gb",
			"256g" => "256Gb",
			"384g" => "384Gb",
			"512g" => "512Gb",
			"1t" => "1024Gb",
			"1t2" => "1152Gb",
			"1ht" => "1536Gb",
			"2t" => "2048Gb",
			"3t" => "3072Gb",
			"4t" => "4096Gb",
			"6t" => "6144Gb",
		]);
		$deviceWidth = self::getOrUnknown(self::shiftChars($partNumber, 2), [
			"01" => "1 bit",
			"08" => "8 bits",
			"16" => "16 bits"
		]);
		$type = self::getOrUnknown(self::shiftChars($partNumber, 1), [
			"a" => "SLC",
			"c" => "MLC-2",
			"e" => "MLC-3"
		]);
		$cls = self::getOrUnknown(self::shiftChars($partNumber, 1), [
			"a" => [1, 0, 0, 1],
			"b" => [1, 1, 1, 1],
			"d" => [2, 1, 1, 1],
			"e" => [2, 2, 2, 2],
			"f" => [2, 2, 2, 1],
			"g" => [3, 3, 3, 3],
			"j" => [4, 2, 2, 1],
			"k" => [4, 2, 2, 2],
			"l" => [4, 4, 4, 4],
			"m" => [4, 4, 4, 2],
			"q" => [8, 4, 4, 4],
			"r" => [8, 2, 2, 2],
			"t" => [16, 8, 4, 2],
			"u" => [8, 4, 4, 2],
			"v" => [16, 8, 4, 4]
		], [0, 0, 0 ,0]);


		$flashInfo = new FlashInfo();
		$flashInfo->setManufacturer(self::getName())
			->setLevel($level)
			->setDensity($density)
			->setDeviceWidth($deviceWidth)
			->setClassification(new Classification($cls[1], $cls[3], $cls[2], $cls[0]))
			->setType($type);


		return $flashInfo;
	}
}
