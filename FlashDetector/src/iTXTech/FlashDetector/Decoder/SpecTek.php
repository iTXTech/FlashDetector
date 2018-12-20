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

class SpecTek extends Micron{
	public static function getName() : string{
		return "SpecTek (Micron)";
	}

	public static function check(string $partNumber) : bool{
		$code = substr($partNumber, 0, 2);
		if(in_array($code, ["FN", "FT", "FB", "FX", "CB"])){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setManufacturer(self::getName())->setType("NAND Flash");
		$partNumber = substr($partNumber, 3);//remove Fxx
		$extra = [];
		$flashInfo
			->setCellLevel(self::getOrDefault($cellLevel = self::shiftChars($partNumber, 1), [
				"3" => 1,
				"M" => 1,
				"4" => 2,
				"L" => 2,
				"B" => 3,
				"Q" => 4,
			], -1))
			->setProcessNode($cellLevel . self::shiftChars($partNumber, 3))
			->setDensity(self::matchFromStart($partNumber, self::DENSITY, 0));
		$extra["densityGrade"] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"1" => "94-100%",
			"9" => "90-100%",
			"6" => "50-90%",
			"5" => "40-60%",
			"0" => "BL or S* grade definitions"
		]);
		$configuration = self::shiftChars($partNumber, 1);
		$flashInfo
			->setDeviceWidth(self::getOrDefault($configuration, [
				"G" => "x8 ECC enabled",
				"L" => "x16",
				"H" => "x1",
				"M" => "x8 (half page, size)",
				"J" => "x4",
				"P" => "x16 ECC enabled",
				"K" => "x8 (normal page, size)",
				"N" => "Not available"
			]))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"1" => "Vcc: 1.8V",
				"2" => "Vcc: 2.7V",
				"3" => "Vcc: 3.3V, VccQ:3.3V",
				"4" => "Vcc: 5.0V",
				"D" => "Vcc: 3.3V, VccQ: 1.8V, VssQ: 0V",
				"E" => "Vcc: 3.3V, VccQ: 1.8V/3.3V, VssQ: 0V",
				"F" => "Vcc: 3.3V, VccQ: 1.2V, VssQ: 0V",
				"J" => "Vcc: 3.3V, VccQ: 1.8V/3.3V, VssQ: 0V",
				"L" => "Vcc: 1.8V, VccQ: 1.8V, VssQ: 0V",
				"S" => "Vcc: 3.3V, VccQ: 3.3V, VssQ: 0V",
				"T" => "Vcc: 3.3V, VccQ: 1.8V/1.2V, VssQ: 0V"
			]));

		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [0, 0, 0, 0]);
		$flashInfo->setClassification(new Classification(
			$classification[1], $classification[3], $classification[2], $classification[0]));

		$extra["packageFunctionalityPartialType"] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => "All CE(s) are valid and usable",
			"B" => "CE1 Valid, CE2 not guaranteed",
			"C" => "CE2 Valid, CE1 not guaranteed",
			"D" => "SLC on the fly. Consult factory for more information"
		]);

		self::setInterface($interface = self::shiftChars($partNumber, 1), $flashInfo)
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 2), self::PACKAGE));

		$ifInfo = self::getOrDefault($interface, [
			"E" => "PPN (Perfect Page NAND)",
			"F" => "Async/NV-DDR2 or NV-DDR3 only",
			"G" => "Enterprise Sync",
			"M" => "SIM Flash",
			"N" => "Async/NV-DDR2"
		], "");
		if($ifInfo !== ""){
			$extra["interfaceInfo"] = $ifInfo;
		}
		$flashInfo->setExtraInfo($extra);

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
