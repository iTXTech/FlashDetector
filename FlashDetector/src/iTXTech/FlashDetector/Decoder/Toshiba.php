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

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class Toshiba extends Decoder{
	public static function getName() : string{
		return "Toshiba";
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "T")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		//Info with asterisk(*) means "Unique character for product variety control."
		$flashInfo = (new FlashInfo($partNumber))->setManufacturer(self::getName());
		$extra = [
			"multi_chip" => self::shiftChars($partNumber, 2) === "TH"
		];
		$level = self::shiftChars($partNumber, 2);
		if(in_array($level, ["GV", "GB"])){
			//TODO: Toshiba E2NAND
			return $flashInfo->setType("E2NAND (Not supported)");
		}
		$level = self::getOrDefault($if = self::shiftChars($partNumber, 1), [
			"N" => "NAND",
			"D" => "NAND *",
			"T" => "Toggle mode NAND"
		]);
		$flashInfo->setType($level)
			->setInterface((new FlashInterface(true))->setToggle($if === "T"))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"V" => "3.3V",
				"Y" => "1.8V",
				"A" => "Vcc: 3.3V, VccQ: 1.8V",
				"B" => "Vcc: 3.3V, VccQ: 1.65V-3.6V",
				"D" => "Vcc: 3.3V/1.8V, VccQ: 3.3V/1.8V"
			]))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"M8" => 256,
				"M9" => 512,
				"G0" => 1 * 1024,
				"G1" => 2 * 1024,
				"G2" => 4 * 1024,
				"G3" => 8 * 1024,
				"G4" => 16 * 1024,
				"GA" => 24 * 1024,
				"G5" => 32 * 1024,
				"GB" => 48 * 1024,
				"G6" => 64 * 1024,
				"GC" => 96 * 1024,
				"G7" => 128 * 1024,
				"GD" => 192 * 1024,
				"G8" => 256 * 1024,
				"GE" => 384 * 1024,
				"G9" => 512 * 1024,
				"GF" => 768 * 1024,
				"T0" => 1 * 1024 * 1024,
				"T1" => 2 * 1024 * 1024
			], 0))
			->setCellLevel(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"S" => 1,
				"H" => 1,
				"D" => 2,
				"E" => 2,
				"T" => 3,
				"U" => 3
			]));
		$width = self::shiftChars($partNumber, 1);
		$flashInfo->setDeviceWidth($width <= 4 ? "x8" : "x16");
		$size = self::getOrDefault(((int) $width) % 5, [
			0 => ["4KB", "256KB"],
			1 => ["4KB", "512KB"],
			2 => [">4KB", ">512KB"],
			3 => ["2KB", "128KB"],
			4 => ["2KB", "256KB"]
		], ["Unknown", "Unknown"]);
		$extra["page_size"] = $size[0];
		$extra["block_size"] = $size[1];
		$flashInfo->setLithography(self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => "130 nm",
			"B" => "90 nm",
			"C" => "70 nm",
			"D" => "56 nm",
			"E" => "43 nm",
			"F" => "32 nm",
			"G" => "24 nm A-type",
			"H" => "24 nm B-type",
			//TODO: confirm
			"J" => "19 nm",
			"K" => "A19 nm",
			"L" => "15 nm",
		]));
		$package = self::shiftChars($partNumber, 2);
		if(in_array($package, ["FT", "TG", "TA"])){
			$flashInfo->setPackage("TSOP");
		}elseif(in_array($package, ["XB", "XG", "BA"])){
			$flashInfo->setPackage("BGA");
		}elseif(in_array($package, ["XL", "LA"])){
			$flashInfo->setPackage("LGA");
		}

		$extra["lead_free"] = !in_array($package, ["FT", "XB"]);
		$extra["halogen_free"] = in_array($package, ["TA", "BA", "LA"]);
		$flashInfo->setExtraInfo($extra);
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"0" => [1, 1],//Ch, nCE
			"I" => [1, 1],
			"2" => [1, 2],
			"K" => [1, 2],
			"4" => [2, 2],
			"M" => [2, 2],
			"7" => [1, 4],
			"R" => [1, 4],
			"8" => [Classification::CHANNEL_SINGLE_OR_DUAL, 4],
			"S" => [Classification::CHANNEL_SINGLE_OR_DUAL, 4],
			"A" => [Classification::CHANNEL_SINGLE_OR_DUAL, 6],
			"U" => [Classification::CHANNEL_SINGLE_OR_DUAL, 6],
			"B" => [Classification::CHANNEL_SINGLE_OR_DUAL, 8],
			"V" => [Classification::CHANNEL_SINGLE_OR_DUAL, 8]
		]);
		$flashInfo->setClassification(new Classification($classification[1], $classification[0]));
		//last symbol ignored

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
