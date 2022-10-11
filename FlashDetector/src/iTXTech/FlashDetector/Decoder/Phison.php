<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2022 iTX Technologies
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

class Phison extends Decoder {
	private const REBRAND_VENDOR = [
		"T" => Constants::VENDOR_KIOXIA,
		"I" => Constants::VENDOR_MICRON,
		"K" => Constants::VENDOR_MICRON,
		"H" => Constants::VENDOR_SKHYNIX,
		"D" => Constants::VENDOR_WESTERN_DIGITAL,
		"C" => Constants::VENDOR_YANGTZE,
		"N" => Constants::VENDOR_INTEL
	];

	private const PACKAGE = [
		"A" => "BGA132",
		"P" => "BGA152",
		"C" => "BGA272",
		"O" => "LGA60-SAT",
		"K" => "LGA60-SAT",
		"F" => "TSOP48",
		"T" => "TSOP48",
		"B" => "BGA132 / LGA110",
		"Y" => "BGA56-UFS"
	];

	private const CLASSIFICATION = [
		"1" => [1, 1], //ce die
		"3" => [1, 2],
		"5" => [2, 2],
		"6" => [2, 4],
		"7" => [4, 4],
		"8" => [4, 8],
		"A" => [4, 16],
		"B" => [8, 8],
		"C" => [8, 16]
	];

	private const DENSITY = [
		"7G" => 16 * Constants::DENSITY_GBYTES,
		"8G" => 32 * Constants::DENSITY_GBYTES,
		"9G" => 64 * Constants::DENSITY_GBYTES,
		"AG" => 128 * Constants::DENSITY_GBYTES,
		"BG" => 256 * Constants::DENSITY_GBYTES,
		"EG" => 384 * Constants::DENSITY_GBYTES,
		"HG" => 512 * Constants::DENSITY_GBYTES,
		"IG" => 1 * Constants::DESNITY_TBYTES,
		"JG" => 2 * Constants::DESNITY_TBYTES
	];

	private const PROCESS_NODE = [
		Constants::VENDOR_KIOXIA => [
			"H" => ["24nm 2plane", 2], // process node, cell
			"P" => ["A19nm 4plane", 2],
			"R" => ["15nm", 3],
			"S" => ["15nm 2plane", 2],
			"U" => ["15nm 4plane", 2],
			"V" => ["BiCS2", 3],
			"I" => ["BICS3", 3],
			"W" => ["BiCS4", 3],
			"X" => ["BiCS4.5", 3],
			"Y" => ["BiCS5", 3]
		],
		Constants::VENDOR_INTEL => [
			"N" => ["20nm", 2],
			"P" => ["16nm", 2],
			"O" => ["L06B/B16A/N18A 32L", -1],
			"V" => ["B16A/B27A", 3],
			"I" => ["B27B", 3],
			"X" => ["B37R", 3],
			"Y" => ["B47R", 3]
		],
		Constants::VENDOR_SKHYNIX => [
			"P" => ["16nm", 3],
			"O" => ["3DV4", 3]
		],
		Constants::VENDOR_YANGTZE => [
			"O" => ["JGS", 3]
		]
	];

	public static function getName(): string {
		return Constants::VENDOR_PHISON;
	}

	public static function check(string $partNumber): bool {
		if(strlen($partNumber) == 10 &&
			in_array($partNumber[0], array_keys(self::REBRAND_VENDOR)) &&
			in_array($partNumber[1], array_keys(self::PACKAGE))) {
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber): FlashInfo {
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName())->setType(Constants::NAND_TYPE_NAND)
			->setExtraInfo([Constants::ORIGINAL_VENDOR => ($vendor = self::getOrDefault(self::shiftChars($partNumber, 1), self::REBRAND_VENDOR))])
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 1), self::PACKAGE));
		$clz = self::getOrDefault(self::shiftChars($partNumber, 1), self::CLASSIFICATION, [-1, -1]);
		$flashInfo->setClassification(new Classification($clz[0], Classification::UNKNOWN_PROP, Classification::UNKNOWN_PROP, $clz[1]))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), self::DENSITY, 2));
		switch($vendor) {
			case Constants::VENDOR_MICRON:
				$vendor = Constants::VENDOR_INTEL;
				break;
			case Constants::VENDOR_WESTERN_DIGITAL:
				$vendor = Constants::VENDOR_KIOXIA;
				break;
		}
		self::shiftChars($partNumber, 3); // unknown
		$nodeInfo = self::getOrDefault(self::shiftChars($partNumber, 1), self::PROCESS_NODE[$vendor] ?? [], [Constants::UNKNOWN, -1]);
		$flashInfo->setCellLevel($nodeInfo[1])->setProcessNode($nodeInfo[0]);

		$flashInfo->setInterface(FlashInterface::getDefaultInterface())
			->setDeviceWidth(8);

		return $flashInfo;
	}
}
