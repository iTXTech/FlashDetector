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
use iTXTech\SimpleFramework\Util\StringUtil;

class SpecTek extends Micron{
	public const LEGACY_DENSITY = [
		"1G" => 1 * Constants::DENSITY_GBITS,
		"18" => 1.8 * Constants::DENSITY_GBITS,
		"2G" => 2 * Constants::DENSITY_GBITS,
		"38" => 3.8 * Constants::DENSITY_GBITS,
		"4G" => 4 * Constants::DENSITY_GBITS,
		"78" => 7.8 * Constants::DENSITY_GBITS,
		"8G" => 8 * Constants::DENSITY_GBITS,
		"F8" => 15.8 * Constants::DENSITY_GBITS,
		"HG" => 16 * Constants::DENSITY_GBITS,
		"31" => 31 * Constants::DENSITY_GBITS,
		"32" => 32 * Constants::DENSITY_GBITS,
		"64" => 64 * Constants::DENSITY_GBITS,
		"NX" => 128,
		"NY" => 256,
		"NZ" => 512
	];

	public const NEWER_DENSITY = [
		"1" => 1 * Constants::DENSITY_GBITS,
		"2" => 4 * Constants::DENSITY_GBITS,
		"3" => 8 * Constants::DENSITY_GBITS,
		"4" => 16 * Constants::DENSITY_GBITS,
		"5" => 32 * Constants::DENSITY_GBITS,
		"6" => 64 * Constants::DENSITY_GBITS,
		"7" => 128 * Constants::DENSITY_GBITS,
		"8" => 256 * Constants::DENSITY_GBITS,
		"9" => 512 * Constants::DENSITY_GBITS,
		"A" => Constants::DENSITY_TBITS,
		"B" => 2 * Constants::DENSITY_TBITS
	];

	public static function getName() : string{
		return Constants::VENDOR_SPECTEK;
	}

	public static function check(string $partNumber) : bool{
		$code = substr($partNumber, 0, 2);
		if(in_array($code, ["FN", "FT", "FB", "FX", "CB"])){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))
			->setVendor(self::getName())->setType(Constants::NAND_TYPE_NAND);
		if(strlen($partNumber) == 13){
			return $flashInfo->setExtraInfo([Constants::UNSUPPORTED_REASON => Constants::SPECTEK_OLD_NUMBERING]);
		}
		$partNumber = substr($partNumber, 3);//remove Fxx
		$extra = [
			"eccEnabled" => false,
			"halfPageAndSize" => false
		];
		$flashInfo
			->setCellLevel(self::getOrDefault($cellLevel = self::shiftChars($partNumber, 1), [
				"3" => 1,
				"M" => 1,
				"4" => 2,
				"L" => 2,
				"B" => 3,
				"Q" => 4,
			], -1))
			->setProcessNode($cellLevel . self::shiftChars($partNumber, 3));
		$density = self::matchFromStart($partNumber, self::DENSITY, 0);
		if($density === 0){//legacy numbering
			$density = self::getOrDefault($d = self::shiftChars($partNumber, 2), self::LEGACY_DENSITY, 0);
			if($density === 0){//"newer" numbering
				$density = self::getOrDefault($d[0], self::NEWER_DENSITY, 0);
				if($density > 0){
					$grade = $d[1];
				}
			}
		}else{
			$grade = self::shiftChars($partNumber, 1);
		}
		$flashInfo->setDensity($density);
		if(isset($grade)){
			$extra["densityGrade"] = self::getOrDefault($grade, [
				"1" => "94-100%",
				"9" => "90-100%",
				"6" => "50-90%",
				"5" => "40-60%",
				"0" => Constants::SPECTEK_DENSITY_GRADE_ZERO,
				"A" => Constants::SPECTEK_DENSITY_GRADE_ZERO
			]);
		}
		$configuration = self::shiftChars($partNumber, 1);
		if(in_array($configuration, ["G", "P"])){
			$extra["eccEnabled"] = true;
		}
		if($configuration == "M"){
			$extra["halfPageAndSize"] = true;
		}
		$flashInfo
			->setDeviceWidth(self::getOrDefault($configuration, [
				"G" => 8,
				"L" => 16,
				"H" => 1,
				"M" => 8,
				"J" => 4,
				"P" => 16,
				"K" => 8,
				"N" => 0
			], -1))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"1" => "Vcc: 1.8V, VccQ: 1.8V",
				"2" => "Vcc: 2.7V",
				"3" => "Vcc: 3.3V, VccQ:3.3V",
				"4" => "Vcc: 5.0V",
				"D" => "Vcc: 3.3V, VccQ: 1.8V, VssQ: 0V",
				"E" => "Vcc: 3.3V, VccQ: 1.8V/3.3V, VssQ: 0V",
				"F" => "Vcc: 3.3V, VccQ: 1.2V, VssQ: 0V",
				"J" => "Vcc: 3.3V, VccQ: 1.8V/3.3V, VssQ: 0V",
				"L" => "Vcc: 2.5V, VccQ: 1.2V, VssQ: 0V",
				"S" => "Vcc: 3.3V, VccQ: 3.3V, VssQ: 0V",
				"T" => "Vcc: 3.3V, VccQ: 1.8V/1.2V, VssQ: 0V"
			]));

		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [0, 0, 0, 0]);
		$flashInfo->setClassification(new Classification(
			$classification[1], $classification[3], $classification[2], $classification[0]));

		$extra["packageFunctionalityPartialType"] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => Constants::SPECTEK_PFPT_A,
			"B" => Constants::SPECTEK_PFPT_B,
			"C" => Constants::SPECTEK_PFPT_C,
			"D" => Constants::SPECTEK_PFPT_D
		]);

		self::setInterface($interface = self::shiftChars($partNumber, 1), $flashInfo)
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 2), self::PACKAGE));

		$ifInfo = self::getOrDefault($interface, [
			"E" => Constants::SPECTEK_IF_E,
			"F" => Constants::SPECTEK_IF_F,
			"G" => Constants::SPECTEK_IF_G,
			"M" => Constants::SPECTEK_IF_M,
			"N" => Constants::SPECTEK_IF_N
		], "");
		if($ifInfo !== ""){
			$extra["interfaceInfo"] = $ifInfo;
		}

		if(self::shiftChars($partNumber, 1) == "-"){
			$speed = self::matchFromStart($partNumber, self::SPEED_GRADE);
			if($speed != Constants::UNKNOWN){
				$extra[Constants::SPEED_GRADE] = $speed;
			}
		}

		return $flashInfo->setExtraInfo($extra);
	}

	public static function removePackage(string $pn) : string{
		$pn = explode("-", $pn)[0];
		foreach(array_keys(self::PACKAGE) as $package){
			if(StringUtil::endsWith($pn, $package)){
				return substr($pn, 0, strlen($pn) - 2);
			}
		}
		return $pn;
	}
}
