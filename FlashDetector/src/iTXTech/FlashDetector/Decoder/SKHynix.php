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
				"64" => 64,
				"25" => 256,
				"1G" => 1 * 1024,
				"4G" => 4 * 1024,
				"AG" => 16 * 1024,
				"CG" => 64 * 1024,
				"12" => 128,
				"51" => 512,
				"2G" => 2 * 1024,
				"8G" => 8 * 1024,
				"BG" => 32 * 1024,
				"DG" => 128 * 1024,
				"EG" => 256 * 1024,
				"FG" => 512 * 1024,
				"PG" => 192 * 1024,
				"RG" => 384 * 1024,
				"VG" => 768 * 1024,
				"1T" => 1 * 1024 * 1024,
				"2T" => 2 * 1024 * 1024,
				//TODO: more
			]))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"8" => "x8",
				"6" => "x16"
			]));
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
			"packageMaterial" => $packageMaterial,
			"doubleStackPackage" => $classification[1] === -1,
			"dualInterface" => $mode[3] > 1,//maybe this property is Channel
		]);

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
