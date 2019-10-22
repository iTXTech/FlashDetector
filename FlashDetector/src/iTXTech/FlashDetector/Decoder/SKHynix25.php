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
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynix25 extends SKHynix{
	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "H25")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setType(Constants::NAND_TYPE_NAND)
			->setVendor(self::getName())->setInterface((new FlashInterface(true))->setToggle(true));
		self::shiftChars($partNumber, 3);//remove H25
		//B => ToggleDDR 4.0, Q => ToggleDDR 2.0 | maybe voltage
		$flashInfo->setExtraInfo([
			"toggle" => self::getOrDefault(self::shiftChars($partNumber, 1), ["B" => "4.0", "Q" => "2.0"])
		])->setVoltage("Vcc: 3.3V, VccQ: 1.8V (UNOFFICIAL)");
		self::shiftChars($partNumber, 1);//E, F ???
		$flashInfo->setCellLevel($level = self::getOrDefault(self::shiftChars($partNumber, 1), [
			"M" => 2,
			"T" => 3
		]))->setDeviceWidth(8);
		self::shiftChars($partNumber, 1);//8, M
		$density = self::shiftChars($partNumber, 1);
		switch($level){
			case 2:
				$flashInfo->setDensity(self::getOrDefault($density, ["A" => 256 * Constants::DENSITY_GBITS], 0));
				break;
			case 3:
			default:
				$flashInfo->setDensity(self::getOrDefault($density, [
					"A" => 512 * Constants::DENSITY_GBITS,
					"B" => 1 * Constants::DENSITY_TBITS,
					"D" => 2 * Constants::DENSITY_TBITS,
					"F" => 4 * Constants::DENSITY_TBITS,
					"G" => 8 * Constants::DENSITY_TBITS,
				], 0));
		}
		self::shiftChars($partNumber, 1);//2, 3, 4 | maybe CE, Rb, Ch
		$flashInfo->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), Samsung::GENERATION));

		return $flashInfo;
	}
}
