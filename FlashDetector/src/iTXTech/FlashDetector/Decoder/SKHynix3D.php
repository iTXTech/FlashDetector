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
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\SimpleFramework\Util\StringUtil;

class SKHynix3D extends SKHynix{
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
		])->setVoltage("Vcc: 2.7V~3.6V, VccQ: 1.7V~1.95V/1.14V~1.26V");
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
