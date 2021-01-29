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

class Intel extends Decoder{
	public static function getName() : string{
		return Constants::VENDOR_INTEL;
	}

	public static function check(string $partNumber) : bool{
		$code = substr($partNumber, 0, 2);
		if(in_array($code, ["JS", "29", "X2", "BK", "CU"]) or
			StringUtil::startsWith($partNumber, "PF29F") or
			StringUtil::startsWith($partNumber, "PF29R")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName());
		$extra = [
			Constants::WAFER => false
		];
		if(StringUtil::startsWith($partNumber, "X")){
			$extra[Constants::WAFER] = true;
			$partNumber = substr($partNumber, 1);
		}else{
			$package = substr($partNumber, 0, 2);
			if(in_array($package, ["JS", "PF", "BK", "CU"])){
				if(in_array($package, ["JS", "PF", "BK"])){
					$extra[Constants::LEAD_FREE] = true;
				}
				$flashInfo->setPackage(self::getOrDefault($package, [
					"JS" => "TSOP48",
					"BK" => "LGA",
					"PF" => "BGA",
					"CU" => "LSOP"
				]));
				$partNumber = substr($partNumber, 2);
			}
		}

		$partNumber = substr($partNumber, 3);//29F
		$flashInfo->setType(Constants::NAND_TYPE_NAND)
			->setDensity(self::getOrDefault($density = self::shiftChars($partNumber, 3), [
				"01G" => 1 * Constants::DENSITY_GBITS,
				"02G" => 2 * Constants::DENSITY_GBITS,
				"04G" => 4 * Constants::DENSITY_GBITS,
				"08G" => 8 * Constants::DENSITY_GBITS,
				"16G" => 16 * Constants::DENSITY_GBITS,
				"32G" => 32 * Constants::DENSITY_GBITS,
				"64G" => 64 * Constants::DENSITY_GBITS,
                "16B" => 128 * Constants::DENSITY_GBITS,
                "32B" => 256 * Constants::DENSITY_GBITS,
                "48B" => 384 * Constants::DENSITY_GBITS,
                "64B" => 512 * Constants::DENSITY_GBITS,
                "96B" => 768 * Constants::DENSITY_GBITS,
                "01T" => 1 * Constants::DENSITY_TBITS,
                "02T" => 2 * Constants::DENSITY_TBITS,
                "03T" => 3 * Constants::DENSITY_TBITS,
                "04T" => 4 * Constants::DENSITY_TBITS,
                "06T" => 6 * Constants::DENSITY_TBITS,
                "08T" => 8 * Constants::DENSITY_TBITS,
                "16T" => 16 * Constants::DENSITY_TBITS
            ], 0))
			->setDeviceWidth(self::getOrDefault($width = self::shiftChars($partNumber, 2), [
				"08" => 8,
				"16" => 16,
				"2A" => 8,
				"A8" => 8
			], -1));
		if(isset($density[2]) and ((int) $density[2]) > 0){//same as Micron
			return Micron::decode($flashInfo->getPartNumber());
		}
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => [1, 1, 1, true],//Die, CE, RB, I/O Common/Separate (Sync/Async only)
			"B" => [2, 1, 1, true],
			"C" => [2, 2, 2, true],
			"D" => [2, 2, 2, true],
			"E" => [2, 2, 2, false],
			"F" => [4, 2, 2, true],
			"G" => [4, 2, 2, false],
			"H" => [8, 4, 4, true],
			"J" => [4, 4, 4, true],
			"K" => [8, 4, 4, false],
			"L" => [1, 1, 1, true],
			"M" => [2, 2, 2, true],
			"N" => [4, 4, 4, true],
			"O" => [8, 8, 4, true],
			"P" => [8, 8, 4, true],//L74
            "Q" => [8, 2, -1, true],
			"S" => [16, 4, 4, true],
			"W" => [16, 8, 4, true],
            "Y" => [16, 4, 4, true]
		], [-1, -1, -1, false]);
		$flashInfo->setClassification(new Classification(
			$classification[1], $width == "2A" ? 2 : 1, $classification[2], $classification[0]))
			->setInterface((new FlashInterface(false))->setAsync(true)->setSync($classification[3]))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => "3.3V (2.70V-3.60V)",
				"B" => "1.8V (1.70V-1.95V)",
				"C" => "Vcc: 3.3V, VccQ: 1.8V/1.2V"
			]))
			->setCellLevel(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"N" => 1,
				"M" => 2,
				"T" => 3,
				"Q" => 4,
			]))
			->setProcessNode($lithography = self::getOrDefault(self::shiftChars($partNumber, 1), [
                "A" => "90 nm",
                "B" => "72 nm",
                "C" => "50 nm",
                "D" => "34 nm",
                "E" => "25 nm",
                "F" => "20 nm",
                "G" => "3D1",
                "H" => "3D2",
                "J" => "3D3",
                "K" => "3D4"
            ]));
		$gen = self::shiftChars($partNumber, 1);
		if(is_numeric($gen)){
			$flashInfo->setGeneration($gen);
		}else{
			$extra[Constants::SKU] = self::getOrDefault($gen, [
				"S" => Constants::INTEL_SKU_S
			]);
		}

		//Patch for L06B/B0KB TLC
		if($flashInfo->getCellLevel() == 3 and $lithography == "G" and $density == "01T"){
			$flashInfo->setDensity(1.5 * Constants::DENSITY_TBITS);
		}

		return $flashInfo->setExtraInfo($extra);
	}
}
