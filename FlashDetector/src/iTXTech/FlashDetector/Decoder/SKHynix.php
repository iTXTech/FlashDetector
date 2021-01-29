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
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynix extends Decoder{
	public const SMALL_BLOCK = 0;//512+16 B/page
	public const LARGE_BLOCK = 1;//2048+64 B/page

	protected const VOLTAGE = [
		"U" => "Vcc: 3.3V, VccQ: 3.3V",
		"L" => "2.7V",
		"S" => "1.8V",
		"J" => "2.7V~3.6V/1.2V",
		"Q" => "Vcc: 2.7V~3.6V, VccQ: 1.7V~1.95V/2.7V~3.6V",
		"T" => "Vcc: 3.3V, VccQ: 1.8V/3.3V"
	];
	protected const DENSITY = [
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
		"4T" => 4 * Constants::DENSITY_TBITS,
	];
	protected const CLASSIFICATION = [
		//Type, Die, Block
		"S" => [1, 1, self::SMALL_BLOCK],
		"A" => [1, 2, self::SMALL_BLOCK],
		"B" => [1, 4, self::SMALL_BLOCK],
		"F" => [1, 1, self::LARGE_BLOCK],
		"G" => [1, 2, self::LARGE_BLOCK],
		"H" => [1, 4, self::LARGE_BLOCK],
		"J" => [1, 8, self::LARGE_BLOCK],
		"K" => [1, 2, self::LARGE_BLOCK],//Double Stack Package
		"T" => [2, 1, self::LARGE_BLOCK],
		"U" => [2, 2, self::LARGE_BLOCK],
		"V" => [2, 4, self::LARGE_BLOCK],
		"W" => [2, 2, self::LARGE_BLOCK],//Double Stack Package
		"Y" => [2, 8, self::LARGE_BLOCK],
		"R" => [2, 6, self::LARGE_BLOCK],
		"Z" => [2, 12, self::LARGE_BLOCK],
		"C" => [2, 16, self::LARGE_BLOCK],
		"M" => [3, 1, self::LARGE_BLOCK],
		"N" => [3, 2, self::LARGE_BLOCK],
		"P" => [3, 4, self::LARGE_BLOCK],
		"Q" => [3, 8, self::LARGE_BLOCK],
		"2" => [2, 1, self::LARGE_BLOCK],
		"4" => [2, 2, self::LARGE_BLOCK],
		"3" => [2, 4, self::LARGE_BLOCK],
		"5" => [2, 8, self::LARGE_BLOCK],
		"D" => [2, 1, self::LARGE_BLOCK],
		"L" => [3, 16, self::LARGE_BLOCK]
		//TODO: more
	];
	protected const MODE = [
		"1" => [1, 1, true, 1],//CE, RB, Sync
		"2" => [1, 1, false, 1],
		"4" => [2, 2, true, 1],
		"5" => [2, 2, false, 1],
		"D" => [2, 2, false, 2],//Dual Interface
		"F" => [4, 4, false, 2],//Dual Interface
		"T" => [5, 5, false, 1],
		"U" => [6, 6, false, 1],
		"V" => [8, 8, false, 1],
		"M" => [4, 1, true, 2],//Dual Interface
		"G" => [4, 2, true, 2],//Dual Interface
		"W" => [6, 6, true, 2],//Dual Interface
		"H" => [8, 8, true, 2],//Dual Interface
		"E" => [4, 4, true, 4],
		"Q" => [4, 4, true, 4],
		"A" => [4, 4, true, 2],//TODO: confirm
	];
	protected const PACKAGE = [
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
		"D" => "PGD2 (wafer)",
		"I" => "VFBGA-100",
		"J" => "LFBGA-100",
		"A" => "VLGA",
		"H" => "XLGA",
		"8" => "FBGA-152",
		"9" => "FBGA-152",
		"2" => "FBGA-316",
		"3" => "FBGA-316",
		//TODO: confirm
		"6" => "BGA-132",
		"0" => "BGA-132",
		"5" => "BGA-132",
		"L" => "BGA-132",
		"4" => "BGA-132",
	];
	protected const BAD_BLOCK = [
		"B" => Constants::SAMSUNG_CBB_B,
		"S" => Constants::SAMSUNG_CBB_L,
		"P" => Constants::SAMSUNG_CBB_S
	];//same as samsung
	protected const OPERATION_TEMPERATURE = [
		"C" => Constants::SKHYNIX_OT_C,
		"E" => Constants::SKHYNIX_OT_E,
		"M" => Constants::SKHYNIX_OT_M,
		"I" => Constants::SKHYNIX_OT_I,
	];

	public static function getName() : string{
		return Constants::VENDOR_SKHYNIX;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "H2")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))
			->setVendor(self::getName());
		//H2J, H2D => E2NAND, H23 => E3NAND, H26 => e-NAND (eMMC)
		if(in_array($level = self::shiftChars($partNumber, 3), ["H2J", "H2D", "H26", "H23"])){
			return $flashInfo->setType(Constants::NAND_TYPE_CON)
				->setExtraInfo([Constants::UNSUPPORTED_REASON => Constants::SKHYNIX_UNSUPPORTED]);
		}else{
			$flashInfo->setType(Constants::NAND_TYPE_NAND);
		}
		$flashInfo
			->setVoltage(self::getOrDefault($voltage = self::shiftChars($partNumber, 1), self::VOLTAGE))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), self::DENSITY, 0))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"8" => 8,
				"6" => 16,
				"L" => 8,
				"I" => 8,
				"D" => 8
			], -1));
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [-1, -1, self::SMALL_BLOCK]);
		$flashInfo->setCellLevel($classification[0]);
		$mode = self::getOrDefault(self::shiftChars($partNumber, 1), self::MODE, [-1, -1, false, -1]);
		$flashInfo->setClassification(new Classification(
			$mode[0], $mode[3], $mode[1], $classification[1]));
		if(in_array($voltage, ["Q", "T"])){
			$flashInfo->setInterface((new FlashInterface(false))->setAsync(true)->setSync(true));
		}else{
			$flashInfo->setInterface((new FlashInterface(false))->setAsync(true)->setSync(false));
		}
		$flashInfo->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), Samsung::GENERATION))
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 1), self::PACKAGE));

		$packageMaterial = self::shiftChars($partNumber, 1);
		$extra = [];
		if(in_array($packageMaterial, ["P", "R"])){
			$extra[Constants::LEAD_FREE] = true;
		}elseif($packageMaterial == "L"){
			$extra[Constants::LEAD_FREE] = false;
		}
		if($packageMaterial == "R"){
			$extra[Constants::HALOGEN_FREE] = true;
		}
		if($packageMaterial == "A"){
			$extra[Constants::WAFER] = true;
		}

		self::shiftChars($partNumber, 1);

		$extra[Constants::BAD_BLOCK] = self::getOrDefault(self::shiftChars($partNumber, 1), self::BAD_BLOCK);
		$extra[Constants::OPERATION_TEMPERATURE] = self::getOrDefault(self::shiftChars($partNumber, 1), self::OPERATION_TEMPERATURE);

		$flashInfo->setExtraInfo($extra);
		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		$partNumber = $info->getPartNumber();
		if(StringUtil::contains($partNumber, "-")){
			$partNumber = explode("-", $partNumber)[0];
		}
		return FlashDetector::getFdb()->getPartNumber(self::getName(), self::removePackage($partNumber));
	}

	public static function removePackage(string $pn) : string{
		if(StringUtil::startsWith($pn, "H27") or StringUtil::startsWith($pn, "H25")){
			$pn = substr($pn, 0, 10);
		}
		return $pn;
	}
}
