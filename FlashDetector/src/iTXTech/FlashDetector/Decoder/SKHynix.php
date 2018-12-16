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

use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynix extends Decoder{
	public const SMALL_BLOCK = 0;//512+16 B/page
	public const LARGE_BLOCK = 1;//2048+64 B/page

	public static function getName() : string{
		return "SKHynix";
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
			return $flashInfo->setType("E2NAND (Not supported)");
		}elseif($level === "HY2"){
			return $flashInfo->setType("LEGACY NAND (Not supported)");
		}else{
			$flashInfo->setType("NAND");
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
				"64" => "64Mb",
				"25" => "256Mb",
				"1G" => "1Gb",
				"4G" => "4Gb",
				"AG" => "16Gb",
				"CG" => "64Gb",
				"12" => "128Mb",
				"51" => "512Mb",
				"2G" => "2Gb",
				"8G" => "8Gb",
				"BG" => "32Gb",
				"DG" => "128Gb",
				"EG" => "256Gb",
				"FG" => "512Gb",
				"PG" => "192Gb",
				"RG" => "384Gb",
				"VG" => "768Gb",
				"1T" => "1Tb",
				"2T" => "2Tb",
				//TODO: more
			]))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"8" => "x8",
				"6" => "x16"
			]));
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1), [
			//Type, Die, Block
			"S" => ["SLC", 1, self::SMALL_BLOCK],
			"A" => ["SLC", 2, self::SMALL_BLOCK],
			"B" => ["SLC", 4, self::SMALL_BLOCK],
			"F" => ["SLC", 1, self::LARGE_BLOCK],
			"G" => ["SLC", 2, self::LARGE_BLOCK],
			"H" => ["SLC", 4, self::LARGE_BLOCK],
			"J" => ["SLC", 8, self::LARGE_BLOCK],
			"K" => ["SLC", -1, self::LARGE_BLOCK],//Double Stack Package
			"T" => ["MLC", 1, self::LARGE_BLOCK],
			"U" => ["MLC", 2, self::LARGE_BLOCK],
			"V" => ["MLC", 4, self::LARGE_BLOCK],
			"W" => ["MLC", -1, self::LARGE_BLOCK],//Double Stack Package
			"Y" => ["MLC", 8, self::LARGE_BLOCK],
			"R" => ["MLC", 6, self::LARGE_BLOCK],
			"Z" => ["MLC", 12, self::LARGE_BLOCK],
			"C" => ["MLC", 16, self::LARGE_BLOCK],
			"M" => ["TLC", 1, self::LARGE_BLOCK],
			"N" => ["TLC", 2, self::LARGE_BLOCK],
			"P" => ["TLC", 4, self::LARGE_BLOCK],
			"Q" => ["TLC", 8, self::LARGE_BLOCK]
			//TODO: more
		], ["Unknown", -1, self::SMALL_BLOCK]);
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
			"A" => "Wafer",
			"P" => "Lead Free",
			"L" => "Leaded",
			"R" => "Lead & Halogen Free"
		]);
		$flashInfo->setExtraInfo([
			"page" => $classification[2] === self::SMALL_BLOCK ? "512+16 B" : "2048+64 B",
			"package_material" => $packageMaterial,
			"double_stack_package" => $classification[1] === -1,
			"dual_interface" => $mode[3] > 1,//maybe this property is Channel
		]);


		return $flashInfo;
	}
}
