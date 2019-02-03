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

class SpecTek extends Micron{
	public static function getName() : string{
		return Constants::MANUFACTURER_SPECTEK;
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
			->setManufacturer(self::getName())->setType(Constants::NAND_TYPE_NAND);
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
			->setProcessNode($cellLevel . self::shiftChars($partNumber, 3))
			->setDensity(self::matchFromStart($partNumber, self::DENSITY, 0));
		$extra["densityGrade"] = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"1" => "94-100%",
			"9" => "90-100%",
			"6" => "50-90%",
			"5" => "40-60%",
			"0" => Constants::SPECTEK_DENSITY_GRADE_ZERO
		]);
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
		$flashInfo->setExtraInfo($extra);

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
