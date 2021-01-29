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

use iTXTech\FlashDetector\FlashInfo;

class WesternDigitalShortCode extends WesternDigital{
	public static function check(string $partNumber) : bool{
		//example: 05055-032G
		if(strlen($partNumber) == 10){
			$parts = explode("-", $partNumber, 2);
			if(count($parts) == 2 and strlen($parts[0]) == 5 and strlen($parts[1]) == 4){
				return true;
			}
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName())
			->setDensity(self::matchFromStart(explode("-", $partNumber, 2)[1],
				WesternDigital::DENSITY, 0));
		return $flashInfo;
	}
}
