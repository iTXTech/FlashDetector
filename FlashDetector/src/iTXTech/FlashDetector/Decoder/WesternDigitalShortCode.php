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
