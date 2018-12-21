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

use iTXTech\FlashDetector\Constants;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class Samsung extends Decoder{
	private const CLASSIFICAITION = [
		//CellLevel, Die
		"9" => [4, 8],//never seen
		"A" => [3, 1],
		"B" => [3, 2],
		"C" => [2, 4],
		"D" => [3, -1],//TODO: confirm: 3D TLC V4
		"F" => [1, 1],
		"G" => [2, 1],
		"H" => [2, 4],
		"K" => [1, -1],//Die stack
		"L" => [2, 2],
		"M" => [2, -1],//Dual stack package = DSP
		"N" => [1, -1],//DSP
		"O" => [3, 8],
		"P" => [2, 8],
		"Q" => [1, 8],
		"R" => [2, 12],
		"S" => [2, 6],
		"T" => [1, -1],//SLC SINGLE (S/B)
		"U" => [2, 16],
		"W" => [1, 4]
	];
	private const DENSITY = [
		"12" => 512,
		"16" => 16,
		"28" => 128,
		"32" => 32,
		"40" => 4,
		"56" => 256,
		"64" => 64,
		"80" => 8,
		"1G" => 1 * 1024,
		"2G" => 2 * 1024,
		"4G" => 4 * 1024,
		"8G" => 8 * 1024,
		"AG" => 16 * 1024,
		"BG" => 32 * 1024,
		"CG" => 64 * 1024,
		"DG" => 128 * 1024,
		"EG" => 256 * 1024,
		"FG" => 256 * 1024,
		"GG" => 384 * 1024,
		"HG" => 512 * 1024,
		"LG" => 24 * 1024,
		"NG" => 96 * 1024,
		"ZG" => 48 * 1024,
		"00" => 0
	];

	public static function getName() : string{
		return Constants::MANUFACTURER_SAMSUNG;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "K")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))
			->setManufacturer(self::getName())
			->setType(Constants::NAND_TYPE_NAND);
		$partNumber = substr($partNumber, 2);//remove K9
		$c = self::getOrDefault(self::shiftChars($partNumber, 1), self::CLASSIFICAITION, [-1, -1]);
		$flashInfo->setCellLevel($c[0])
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), self::DENSITY, 0));
		$technology = self::shiftChars($partNumber, 1);
		//only check if is D => DDR
		$flashInfo->setInterface((new FlashInterface(true))->setToggle($technology === "D"))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"0" => 0,
				"8" => 8,
				"6" => 16
			], -1))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => "1.65V~3.6V",
				"B" => "2.7V (2.5V~2.9V)",
				"C" => "5.0V (4.5V~5.5V)",
				"D" => "2.65V (2.4V ~ 2.9V)",
				"E" => "2.3V~3.6V",
				"R" => "1.8V (1.65V~1.95V)",
				"Q" => "1.8V (1.7V ~ 1.95V)",
				"T" => "2.4V~3.0V",
				"S" => "3.3V (3V~3.6V/ VccQ1.8V (1.65V~1.95V)",
				"U" => "2.7V~3.6V",
				"V" => "3.3V (3.0V~3.6V)",
				"W" => "2.7V~5.5V, 3.0V~5.5V",
				"0" => "NONE",
			]));
		$mode = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"0" => [-1, -1],//CE, R/nB
			"1" => [2, 2],
			"3" => [3, 3],
			"4" => [4, 1],
			"5" => [4, 4],
			"6" => [6, 2],
			"7" => [8, 4],
			"8" => [8, 2],
			"9" => [-1, -1],//1st block OTP
			"A" => [-1, -1],//Mask Option 1
			"L" => [-1, -1],//Low grade
		], [-1, -1]);
		$flashInfo->setClassification(new Classification($mode[0], Classification::UNKNOWN_PROP, $mode[1], $c[1]))
			->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"M" => 1,
				"A" => 2,
				"B" => 3,
				"C" => 4,
				"D" => 5,
				"E" => 6,
				"Y" => 25,
				"Z" => 26
			]));

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		if(!isset(FlashDetector::getFdb()[strtolower(self::getName())][$partNumber]) and strlen($partNumber) === 10){//standard
			$c = self::CLASSIFICAITION[substr($partNumber, 2, 1)] ?? -1;
			//convert part number to single die
			if($c[1] > 1){//die
				foreach(self::CLASSIFICAITION as $code => $cf){
					if($cf[0] === $c[0] and $cf[1] === 1){
						$partNumber{2} = $code;
						$density = self::DENSITY[substr($partNumber, 3, 2)] ?? -1;
						foreach(self::DENSITY as $cd => $d){
							if($d * $c[1] === $density){
								$partNumber{3} = $cd{0};
								$partNumber{4} = $cd{1};
							}
						}
						break;
					}
				}
			}
			$partNumber{8} = "0";
			$info = FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
			if($info !== null){
				$info["m"] .= " (" . $c[1] . " x " . $partNumber . ")";
			}
			return $info;
		}
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
