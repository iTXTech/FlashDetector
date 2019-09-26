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
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

//TODO: rename to Kioxia
class Toshiba extends Decoder{
	public static function getName() : string{
		return Constants::VENDOR_TOSHIBA;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "T")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		//Info with asterisk(*) means "Unique character for product variety control."
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName());
		$extra = [
			"multiChip" => self::shiftChars($partNumber, 2) === "TH"
		];
		$level = self::shiftChars($partNumber, 2);
		if(in_array($level, ["GV", "GB"])){
			//TODO: Toshiba NAND with Controller
			return $flashInfo->setType(Constants::NAND_TYPE_E2NAND)
				->setExtraInfo([Constants::UNSUPPORTED_REASON => Constants::TOSHIBA_E2NAND_NOT_SUPPORTED]);
		}
		$level = self::getOrDefault($if = self::shiftChars($partNumber, 1), [
			"N" => Constants::NAND_TYPE_NAND,
			"D" => Constants::NAND_TYPE_NAND,
			"T" => Constants::NAND_TYPE_NAND,
			"L" => Constants::NAND_TYPE_NAND,
		]);
		$flashInfo->setType($level)
			->setInterface((new FlashInterface(true))->setToggle(in_array($if, ["T", "L"])))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"V" => "3.3V",
				"Y" => "1.8V",
				"A" => "Vcc: 3.3V, VccQ: 1.8V",
				"B" => "Vcc: 3.3V, VccQ: 1.65V-3.6V",
				"D" => "Vcc: 3.3V/1.8V, VccQ: 3.3V/1.8V",
				"E" => "Vcc: 3.3V, VccQ: 3.3V/1.8V",
				"F" => "Vcc: 3.3V, VccQ: 3.3V/1.8V (UNOFFICIAL)",
				"J" => "Vcc: 3.3V, VccQ: 1.8V (UNOFFICIAL)"
				//TODO: F, J
			]))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"M8" => 256,
				"M9" => 512,
				"G0" => 1 * Constants::DENSITY_GBITS,
				"G1" => 2 * Constants::DENSITY_GBITS,
				"G2" => 4 * Constants::DENSITY_GBITS,
				"G3" => 8 * Constants::DENSITY_GBITS,
				"G4" => 16 * Constants::DENSITY_GBITS,
				"GA" => 24 * Constants::DENSITY_GBITS,
				"G5" => 32 * Constants::DENSITY_GBITS,
				"GB" => 48 * Constants::DENSITY_GBITS,
				"G6" => 64 * Constants::DENSITY_GBITS,
				"GC" => 96 * Constants::DENSITY_GBITS,
				"G7" => 128 * Constants::DENSITY_GBITS,
				"GD" => 192 * Constants::DENSITY_GBITS,
				"G8" => 256 * Constants::DENSITY_GBITS,
				"GE" => 384 * Constants::DENSITY_GBITS,
				"G9" => 512 * Constants::DENSITY_GBITS,
				"GF" => 768 * Constants::DENSITY_GBITS,
				"T0" => 1 * Constants::DENSITY_TBITS,
				"T1" => 2 * Constants::DENSITY_TBITS,
				"T2" => 4 * Constants::DENSITY_TBITS,
				"TG" => 1.5 * Constants::DENSITY_TBITS
			], 0))
			->setCellLevel(self::getOrDefault($ep = self::shiftChars($partNumber, 1), [
				"S" => 1,
				"H" => 1,//eSLC
				"D" => 2,
				"E" => 2,//eMLC
				"J" => 2,
				"C" => 2,
				"T" => 3,
				"U" => 3,//eTLC
				"V" => 3,
				"F" => 4,//QLC
			]));
		$extra[Constants::ENTERPRISE] = in_array($ep, ["H", "E", "U"]);
		$width = self::shiftChars($partNumber, 1);
		if(in_array($width, ["0", "1", "2", "3", "4", "A", "B", "C", "D"])){
			$flashInfo->setDeviceWidth(8);
		}elseif(in_array($width, ["5", "6", "7", "8", "9"])){
			$flashInfo->setDeviceWidth(16);
		}
		$size = self::getOrDefault($width, [
			"0" => ["4KB", "256KB"],
			"1" => ["4KB", "512KB"],
			"2" => [">4KB", ">512KB"],
			"3" => ["2KB", "128KB"],
			"4" => ["2KB", "256KB"],
			"5" => ["4KB", "256KB"],
			"6" => ["4KB", "512KB"],
			"7" => [">4KB", ">512KB"],
			"8" => ["2KB", "128KB"],
			"9" => ["2KB", "256KB"],
			"A" => ["8KB", "2MB"],
			"B" => ["16KB", "8MB"],
			"C" => ["16KB 1pl", "4MB"],
			"D" => ["16KB 2pl", "4MB"],
			//TODO: F
		], [Constants::UNKNOWN, Constants::UNKNOWN]);
		$extra["pageSize"] = $size[0];
		$extra["blockSize"] = $size[1];
		$flashInfo->setProcessNode(self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => "130 nm",
			"B" => "90 nm",
			"C" => "70 nm",
			"D" => "56 nm",
			"E" => "43 nm",
			"F" => "32 nm",
			"G" => "24 nm A-type",
			"H" => "24 nm B-type",
			"J" => "19 nm",
			"K" => "A19 nm",
			"L" => "15 nm",
			"2" => "BiCS2",
			"3" => "BiCS3",
			"4" => "BiCS4"
		]));
		$package = self::shiftChars($partNumber, 2);
		if(in_array($package, ["FT", "TG", "TA"])){
			$package = "TSOP48";
		}elseif(in_array($package, ["XB", "XG", "BA"])){
			$package = "BGA";
		}elseif(in_array($package, ["XL", "LA"])){
			$package = "LGA";
		}else{
			$package = Constants::UNKNOWN;
		}

		$extra[Constants::LEAD_FREE] = !in_array($package, ["FT", "XB"]);
		$extra[Constants::HALOGEN_FREE] = in_array($package, ["TA", "BA", "LA"]);
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
			"V" => [Classification::CHANNEL_SINGLE_OR_DUAL, 8],
			"D" => [4, 4],
			"E" => [4, 8]
		], [-1, -1]);
		$flashInfo->setClassification(new Classification($classification[1], $classification[0]));
		$detailedPackage = [
			"LGA" => [
				"1" => "LGA40 (12 x 18 x 0.7)",
				"2" => "LGA40 (12 x 18 x 1.15)",
				"3" => "LGA40 (12 x 17 x 0.65)",
				"4" => "LGA40 (12 x 17 x 1.0)",
				"5" => "LGA40 (12 x 17 x 1.04)",
				"6" => "LGA40 (13 x 17 x 1.04)",
				"7" => "LGA52 (14 x 18 x 1.4)",
				"8" => "LGA52 (14 x 18 x 1.04)",
				"9" => "LGA52 (14 x 18 x 1.0)",
				"A" => "LGA52 (12 x 17 x 1.04/1.0)",
				"B" => "LGA52 (12 x 17 x 1.4)",
				"C" => "LGA52 (11 x 14 x 0.9)",
			],
			"BGA" => [
				"1" => "BGA224 (14 x 18 x 1.46)",
				"2" => "BGA224 (14 x 18 x 1.46)",
				"3" => "BGA60 (8.5 x 13)",
				"4" => "BGA60 (9 x 11)",
				"5" => "BGA60 (10 x 13)",
				"6" => "BGA60 (8.5 x 13)",
				"7" => "BGA60 (9 x 11)",
				"8" => "BGA60 (10 x 13)",
				"9" => "BGA132 (12 x 18 x 1.4)",
				"A" => "BGA132 (12 x 18 x 1.85)",
				"B" => "BGA224 (14 x 18 x 1.35)",
				"C" => "BGA132",
				"D" => "BGA132",
				"E" => "BGA272",
				"F" => "BGA272",
				"G" => "BGA272",
				"H" => "BGA132",
				"J" => "BGA152",
				"K" => "BGA152",
				"N" => "BGA152",
				"P" => "BGA132"
			]
		];

		$p = self::shiftChars($partNumber, 1);
		$flashInfo->setPackage($detailedPackage[$package][$p] ?? $package);

		return $flashInfo;
	}
}
