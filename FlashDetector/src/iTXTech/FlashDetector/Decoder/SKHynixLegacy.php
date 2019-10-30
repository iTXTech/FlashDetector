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
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynixLegacy extends SKHynix{
	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "HY27")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setType(Constants::NAND_TYPE_NAND)
			->setVendor(self::getName());
		self::shiftChars($partNumber, 4);//remove HY27
		$flashInfo->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), self::VOLTAGE));
		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [-1, -1, self::SMALL_BLOCK]);
		$flashInfo->setCellLevel($classification[0]);
		$flashInfo->setInterface((new FlashInterface(false))->setAsync(true)->setSync(true))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"08" => 8,
				"16" => 16,
				"32" => 32
			], -1))
			->setDensity(self::getOrDefault(self::shiftChars($partNumber, 2), self::DENSITY, 0));
		$mode = self::getOrDefault(self::shiftChars($partNumber, 1), self::MODE, [-1, -1, false, -1]);
		$flashInfo->setClassification(new Classification(
			$mode[0], $mode[3], $mode[1], $classification[1]))
			->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), Samsung::GENERATION));

		self::shiftChars($partNumber, 1);

		$flashInfo->setPackage(self::getOrDefault(self::shiftChars($partNumber, 1), self::PACKAGE));
		$extra = [];
		if($partNumber != ""){
			$packageMaterial = self::shiftChars($partNumber, 1);
			$extra[Constants::LEAD_FREE] = in_array($packageMaterial, ["P", "R"]);
			$extra[Constants::HALOGEN_FREE] = in_array($packageMaterial, ["H", "R"]);
		}
		if($partNumber != ""){
			$extra[Constants::OPERATION_TEMPERATURE] = self::getOrDefault(self::shiftChars($partNumber, 1), self::OPERATION_TEMPERATURE);
		}
		if($partNumber != ""){
			$extra[Constants::BAD_BLOCK] = self::getOrDefault(self::shiftChars($partNumber, 1), self::BAD_BLOCK);
		}

		return $flashInfo->setExtraInfo($extra);
	}
}
