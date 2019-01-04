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
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynix extends Decoder{
	public const SMALL_BLOCK = 0;//512+16 B/page
	public const LARGE_BLOCK = 1;//2048+64 B/page

	public static function getName() : string{
		return Constants::MANUFACTURER_SKHYNIX;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "H")){//TODO: E2NAND
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))
			->setManufacturer(self::getName());
		if(in_array($level = self::shiftChars($partNumber, 3), ["H2J", "H2D"])){
			//TODO: SKHynix E2NAND
			return $flashInfo->setType(Constants::NAND_TYPE_E2NAND)
				->setExtraInfo([Constants::NOT_SUPPORTED_REASON => Constants::SKHYNIX_E2NAND_NOT_SUPPORTED]);
		}elseif($level === "HY2"){
			return $flashInfo->setType(Constants::NAND_TYPE_NAND)
				->setExtraInfo([Constants::NOT_SUPPORTED_REASON => Constants::SKHYNIX_OLD_NUMBERING]);
		}else{
			$flashInfo->setType(Constants::NAND_TYPE_NAND);
		}
		$flashInfo
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"U" => "2.7V~3.6V (3.3V)",
				"L" => "2.7V",
				"S" => "1.8V",
				"J" => "2.7V~3.6V/1.2V",
				"Q" => "2.7V~3.6V/1.8V"
				//what is Q, hynix's new NAND are most with Q?
				//According to SiliconMotion's Document, most of H27Q NAND are 3.3V required
			]))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"64" => 64,
				"25" => 256,
				"1G" => 1 * Constants::DENSITY_GBITS,
				"4G" => 4 * Constants::DENSITY_GBITS,
				"AG" => 16 * Constants::DENSITY_GBITS,
				"CG" => 64 * Constants::DENSITY_GBITS,
				"12" => 128,
				"51" => 512,
				"2G" => 2 * Constants::DENSITY_GBITS,
				"8G" => 8 * Constants::DENSITY_GBITS,
				"BG" => 32 * Constants::DENSITY_GBITS,
				"DG" => 128 * Constants::DENSITY_GBITS,
				"EG" => 256 * Constants::DENSITY_GBITS,
				"FG" => 512 * Constants::DENSITY_GBITS,
				"PG" => 192 * Constants::DENSITY_GBITS,
				"RG" => 384 * Constants::DENSITY_GBITS,
				"VG" => 768 * Constants::DENSITY_GBITS,
				"1T" => 1 * Constants::DENSITY_TBITS,
				"2T" => 2 * Constants::DENSITY_TBITS,
				//TODO: more
			], 0))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"8" => 8,
				"6" => 16
			], -1));
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1), [
			//Type, Die, Block
			"S" => [1, 1, self::SMALL_BLOCK],
			"A" => [1, 2, self::SMALL_BLOCK],
			"B" => [1, 4, self::SMALL_BLOCK],
			"F" => [1, 1, self::LARGE_BLOCK],
			"G" => [1, 2, self::LARGE_BLOCK],
			"H" => [1, 4, self::LARGE_BLOCK],
			"J" => [1, 8, self::LARGE_BLOCK],
			"K" => [1, -1, self::LARGE_BLOCK],//Double Stack Package
			"T" => [2, 1, self::LARGE_BLOCK],
			"U" => [2, 2, self::LARGE_BLOCK],
			"V" => [2, 4, self::LARGE_BLOCK],
			"W" => [2, -1, self::LARGE_BLOCK],//Double Stack Package
			"Y" => [2, 8, self::LARGE_BLOCK],
			"R" => [2, 6, self::LARGE_BLOCK],
			"Z" => [2, 12, self::LARGE_BLOCK],
			"C" => [2, 16, self::LARGE_BLOCK],
			"M" => [3, 1, self::LARGE_BLOCK],
			"N" => [3, 2, self::LARGE_BLOCK],
			"P" => [3, 4, self::LARGE_BLOCK],
			"Q" => [3, 8, self::LARGE_BLOCK]
			//TODO: more
		], [-1, -1, self::SMALL_BLOCK]);
		$flashInfo->setCellLevel($classification[0]);
		$mode = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"1" => [1, 1, true, 1],//CE, RnB, Sync
			"2" => [1, 1, false, 1],
			"4" => [2, 2, true, 1],
			"5" => [2, 2, false, 1],
			"D" => [-1, -1, false, 2],//Dual Interface
			"F" => [4, 4, false, 2],//Dual Interface
			"T" => [5, 5, false, 1],
			"U" => [6, 6, false, 1],
			"V" => [8, 8, false, 1],
			"M" => [4, 1, true, 2],//Dual Interface
			"G" => [4, 2, true, 2],//Dual Interface
			"W" => [6, 6, true, 2],//Dual Interface
			"H" => [8, 8, true, 2],//Dual Interface
		], [-1, -1, false, -1]);
		$flashInfo->setClassification(new Classification(
			$mode[0], $mode[3], $mode[1], $classification[1]));
		$flashInfo->setInterface((new FlashInterface(false))->setAsync(true)->setSync(true))//Async default = true
		->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), [
			"M" => 1,
			"A" => 2,
			"B" => 3,
			"C" => 4,
			"D" => 5,
			"E" => 6
		]))
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"T" => "TSOP1",
				"V" => "WSOP",
				"S" => "USOP",
				"N" => "LSOP1",
				"F" => "FBGA",
				"X" => "LGA",
				"M" => "WLGA",
				"Y" => "VLGA",
				"U" => "ULGA",
				"W" => "Wafer",
				"C" => "PGD1 (chip)",
				"K" => "KGD",
				"D" => "PGD2",
				"I" => "VFBGA-100",
				"J" => "LFBGA-100",
				"A" => "VLGA",
				"H" => "XLGA"
			]));
		$packageMaterial = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => Constants::SKHYNIX_PM_WAFER,
			"P" => Constants::SKHYNIX_PM_LEAD_FREE,
			"L" => Constants::SKHYNIX_PM_LEADED,
			"R" => Constants::SKHYNIX_PM_LEAD_AND_HALOGEN_FREE
		]);
		$flashInfo->setExtraInfo([
			"page" => $classification[2] === self::SMALL_BLOCK ? "512+16 B" : "2048+64 B",
			"packageMaterial" => $packageMaterial,
			"doubleStackPackage" => $classification[1] === -1,
			"dualInterface" => $mode[3] > 1,//maybe this property is Channel
		]);

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][self::removePackage($partNumber)] ?? null;
	}

	public static function removePackage(string $pn) : string{
		if(StringUtil::startsWith($pn, "H27") and strlen($pn) == 12){
			$pn = substr($pn, 0, 10);
		}
		return $pn;
	}
}
