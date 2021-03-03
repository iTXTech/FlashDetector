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
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class Yangtze extends Decoder{
	protected const DIE_SIZE = [
		"06" => 64 * Constants::DENSITY_GBITS,
		"07" => 128 * Constants::DENSITY_GBITS,
		"08" => 256 * Constants::DENSITY_GBITS,
		"09" => 512 * Constants::DENSITY_GBITS,
		"10" => Constants::DENSITY_TBITS
	];

	protected const CLASSIFICATION = [
		"A" => [1, 1, 1, 1], //Die, CE, Rb, Ch
		"B" => [2, 1, 1, 1],
		"C" => [2, 2, 2, 1],
		"D" => [2, 2, 2, 2],
		"E" => [4, 2, 2, 1],
		"F" => [4, 4, 4, 1],
		"G" => [4, 2, 2, 2],
		"H" => [4, 4, 4, 2],
		"Q" => [4, 4, 4, 4],
		"I" => [8, 2, 2, 2],
		"J" => [8, 4, 4, 2],
		"K" => [8, 4, 4, 4],
		"L" => [8, 8, 4, 4],
		"R" => [8, 8, 4, 2],
		"M" => [16, 4, 4, 2],
		"N" => [16, 4, 4, 4],
		"O" => [16, 8, 4, 2],
		"P" => [16, 8, 4, 4]
	];

	public static function getName() : string{
		return Constants::VENDOR_YANGTZE;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "YM")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName());
		self::shiftChars($partNumber, 2);
		$flashInfo->setType(self::shiftChars($partNumber, 1) == "N" ?
			Constants::NAND_TYPE_NAND : Constants::UNKNOWN);
		$dieSize = self::getOrDefault(self::shiftChars($partNumber, 2), self::DIE_SIZE, 0);
		$flashInfo->setCellLevel(self::getOrDefault(self::shiftChars($partNumber, 1), [
			"S" => 1,
			"M" => 2,
			"T" => 3,
			"Q" => 4
		]))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => "Vcc=2.7~3.6V; VccQ=3.3V",
				"B" => "Vcc=2.7~3.6V; VccQ=1.8V",
				"C" => "Vcc=2.35~3.6V; VccQ=1.2V",
				"D" => "Vcc=2.35~3.6V; VccQ=3.3/1.8V",
				"E" => "Vcc=2.35~3.6V; VccQ=1.8/1.2V"
			]))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"1" => 8,
				"2" => 16
			], -1))
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"W0" => "Wafer",
				"T1" => "TSOP-48; 12 x 20 x 1.x",
				"B1" => "BGA-132; 12 x 18 x 1.x",
				"B2" => "BGA-152; 14 x 18 x 1.x",
				"B3" => "BGA-272; 14 x 18 x 1.x",
				"L1" => "LGA-52; 12 x 17 x 1.x"
			]));
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [-1, -1, -1, -1]);
		$flashInfo->setClassification(new Classification($classification[1], $classification[3],
			$classification[2], $classification[0]));
		$deviceSize = $dieSize * $classification[0];
		$flashInfo->setDensity($deviceSize > 0 ? $deviceSize : 0);
		$additionalInfo = self::getOrDefault(self::shiftChars($partNumber, 2), [
			"C0" => ["0°C ~ 70°C", "ONFI 3.2; Max Speed=400MB/s"],
			"C1" => ["0°C ~ 70°C", "ONFI 3.2; Max Speed=533MB/s"],
			"C2" => ["0°C ~ 70°C", "ONFI 4.0; Max Speed=667MB/s"],
			"C3" => ["0°C ~ 70°C", "ONFI 4.0; Max Speed=800MB/s"],
		], [-1, -1]);
		$extra = [];
		if($additionalInfo[0] != -1){
			$extra[Constants::OPERATION_TEMPERATURE] = $additionalInfo[0];
		}
		if($additionalInfo[1] != -1){
			$extra[Constants::SPEED_GRADE] = $additionalInfo[1];
		}

		return $flashInfo->setExtraInfo($extra)
			->setInterface((new FlashInterface(false))->setAsync(true)->setSync(true))
			->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => 1,
				"B" => 2,
				"C" => 3,
				"D" => 4,
				"E" => 5
			]));
	}
}
