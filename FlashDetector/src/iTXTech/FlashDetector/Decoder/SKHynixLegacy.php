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
