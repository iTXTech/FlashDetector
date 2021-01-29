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

class Samsung extends Decoder{
	private const CLASSIFICATION = [
		//CellLevel, Die
		"3" => [4, 1],
		"9" => [4, 8],
		"A" => [3, 1],
		"B" => [3, 2],
		"C" => [3, 4],
		"D" => [3, 16],
		"F" => [1, 1],
		"G" => [2, 1],
		"H" => [2, 4],
		"K" => [1, 2],
		"L" => [2, 2],
		"M" => [2, 2],
		"N" => [1, 2],
		"O" => [3, 8],
		"P" => [2, 8],
		"Q" => [1, 8],
		"R" => [2, 12],
		"S" => [2, 6],
		"T" => [1, 1],
		"U" => [2, 16],
		"V" => [1, 16],
		"W" => [1, 4],
		"X" => [4, -1],//QLC ?die
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
		"1G" => 1 * Constants::DENSITY_GBITS,
		"2G" => 2 * Constants::DENSITY_GBITS,
		"4G" => 4 * Constants::DENSITY_GBITS,
		"8G" => 8 * Constants::DENSITY_GBITS,
		"AG" => 16 * Constants::DENSITY_GBITS,
		"BG" => 32 * Constants::DENSITY_GBITS,
		"CG" => 64 * Constants::DENSITY_GBITS,
		"DG" => 128 * Constants::DENSITY_GBITS,
		"EG" => 256 * Constants::DENSITY_GBITS,
		"FG" => 256 * Constants::DENSITY_GBITS,
		"GG" => 384 * Constants::DENSITY_GBITS,
		"HG" => 512 * Constants::DENSITY_GBITS,
		"LG" => 24 * Constants::DENSITY_GBITS,
		"NG" => 96 * Constants::DENSITY_GBITS,
		"ZG" => 48 * Constants::DENSITY_GBITS,
		"PG" => 171 * Constants::DENSITY_GBITS,
		"QG" => 341 * Constants::DENSITY_GBITS,
		"RG" => 683 * Constants::DENSITY_GBITS,
		"SG" => 1365 * Constants::DENSITY_GBITS,
		"KG" => 1 * Constants::DENSITY_TBITS,
		"MG" => 2 * Constants::DENSITY_TBITS,
		"UG" => 4 * Constants::DENSITY_TBITS,
		"VG" => 8 * Constants::DENSITY_TBITS,
		"00" => 0
	];
	public const GENERATION = [
		"M" => 1,
		"A" => 2,
		"B" => 3,
		"C" => 4,
		"D" => 5,
		"E" => 6,
		"F" => 7,
		"G" => 8,
		"H" => 9,
		"Y" => 25,
		"Z" => 26
	];

	public static function getName() : string{
		return Constants::VENDOR_SAMSUNG;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "K9")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))
			->setVendor(self::getName())
			->setType(Constants::NAND_TYPE_NAND);
		$partNumber = substr($partNumber, 2);//remove K9
		$c = self::getOrDefault(self::shiftChars($partNumber, 1), self::CLASSIFICATION, [-1, -1]);
		$flashInfo->setCellLevel($c[0])
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), self::DENSITY, 0));
		$technology = self::shiftChars($partNumber, 1);
		$extra = ["toggle" => self::getOrDefault($technology, [
			"D" => "1.0",
			"Y" => "2.0",
			"B" => "3.0"
		], Constants::UNKNOWN)];
		$flashInfo->setInterface((new FlashInterface(true))
			->setToggle(in_array($technology, ["D", "Y", "B"])))
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
				"S" => "Vcc: 3.3V (3V~3.6V), VccQ: 1.8V (1.65V~1.95V)",
				"U" => "2.7V~3.6V",
				"V" => "3.3V (3.0V~3.6V)",
				"W" => "2.7V~5.5V, 3.0V~5.5V",
				"0" => Constants::SAMSUNG_NONE,
				"H" => "Vcc: 3.3V, VccQ: 1.8V (UNOFFICIAL)",//TODO: Confirm
				//TODO: J
			]));
		$mode = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"0" => [1, 1],//CE, R/nB
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
			"C" => [16, 2],
			"J" => [16, 4],
		], [-1, -1]);
		$flashInfo->setClassification(new Classification($mode[0], Classification::UNKNOWN_PROP, $mode[1], $c[1]))
			->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), self::GENERATION));

		self::shiftChars($partNumber, 1);//remove -
		$flashInfo->setPackage(self::getOrDefault($package = self::shiftChars($partNumber, 1), [
			"8" => "TSOP1",
			"9" => "56-TSOP1",
			"A" => "COB",
			"B" => "FBGA",
			"C" => "BGA316",
			"D" => "TBGA63 or 316",
			"E" => "ISM",
			"F" => "WSOP",
			"G" => "FBGA",
			"H" => "BGA132 or 136",
			"I" => "ULGA (12*17)",
			"J" => "FBGA",
			"K" => "ULGA (12*17)",
			"L" => "ULGA (14*18)",
			"M" => "ULGA52 (13*18)",
			"P" => "TSOP1",
			"Q" => "TSOP2",
			"R" => "56-TSOP1",
			"S" => "TSOP1",
			"T" => "WSOP",
			"U" => "COB (MMC)",
			"V" => "WSOP",
			"W" => "Wafer",
			"Y" => "TSOP1",
			"Z" => "WELP",
			"X" => "BGA108",
			"1" => "BGA108",
		]));
		$extra[Constants::LEAD_FREE] = in_array($package,
			["8", "9", "B", "E", "F", "I", "J", "K", "L", "M", "P", "Q", "R", "S", "T", "Z"]);
		$extra[Constants::HALOGEN_FREE] = in_array($package, ["8", "9", "B", "E", "K", "L", "M", "R", "S", "T"]);
		$extra[Constants::SAMSUNG_CU] = in_array($package, ["8", "9"]);
		$extra[Constants::OPERATION_TEMPERATURE] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"C" => Constants::SAMSUNG_TEMP_C,
			"S" => Constants::SAMSUNG_TEMP_S,
			"B" => Constants::SAMSUNG_TEMP_B,
			"I" => Constants::SAMSUNG_TEMP_I,
			"0" => Constants::SAMSUNG_NONE
		]);
		$extra[Constants::BAD_BLOCK] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"B" => Constants::SAMSUNG_CBB_B,
			"D" => Constants::SAMSUNG_CBB_D,
			"K" => Constants::SAMSUNG_CBB_K,
			"L" => Constants::SAMSUNG_CBB_L,
			"N" => Constants::SAMSUNG_CBB_N,
			"S" => Constants::SAMSUNG_CBB_S,
			"0" => Constants::SAMSUNG_NONE,
		]);

		$flashInfo->setExtraInfo($extra);

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		$partNumber = $info->getPartNumber();
		if(StringUtil::contains($partNumber, "-")){
			$partNumber = explode("-", $partNumber)[0];
		}
		if(!FlashDetector::getFdb()->hasPartNumber(self::getName(), $partNumber) and strlen($partNumber) === 10){//standard
			$c = self::CLASSIFICATION[substr($partNumber, 2, 1)] ?? -1;
			//convert part number to single die
			if($c[1] > 1){//die
				foreach(self::CLASSIFICATION as $code => $cf){
					if($cf[0] === $c[0] and $cf[1] === 1){
						$partNumber[2] = $code;
						$density = self::DENSITY[substr($partNumber, 3, 2)] ?? -1;
						foreach(self::DENSITY as $cd => $d){
							if($d * $c[1] === $density){
								$partNumber[3] = $cd[0];
								$partNumber[4] = $cd[1];
							}
						}
						break;
					}
				}
			}
			$partNumber[8] = "0";
			$info = FlashDetector::getFdb()->getPartNumber(self::getName(), $partNumber) ?? null;
			if($info !== null){
				$info->setRemark($info->getRemark() . " (" . $c[1] . " x " . $partNumber . ")");
			}
			return $info;
		}
		return FlashDetector::getFdb()->getPartNumber(self::getName(), $partNumber);
	}
}
