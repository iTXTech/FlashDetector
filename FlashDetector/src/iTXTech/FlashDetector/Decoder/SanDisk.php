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

use iTXTech\FlashDetector\Constants;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\SimpleFramework\Util\StringUtil;

//There is no datasheet or guide available!
class SanDisk extends Decoder{
	public static function getName() : string{
		return Constants::MANUFACTURER_SANDISK;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "SD")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setManufacturer(self::getName());
		$partNumber = substr($partNumber, 2);//remove SD
		if(substr($partNumber, 0, 2) === "IN"){
			return $flashInfo->setType(Constants::NAND_TYPE_INAND)
				->setExtraInfo([Constants::NOT_SUPPORTED_REASON => Constants::SANDISK_INAND_NOT_SUPPORTED]);
		}

		return $flashInfo->setType(Constants::NAND_TYPE_NAND)
			->setExtraInfo([Constants::NOT_SUPPORTED_REASON => Constants::SANDISK_NAND_NOT_SUPPORTED]);
	}

	public static function getFlashInfoFromFdb(string $partNumber) : ?array{
		return FlashDetector::getFdb()[strtolower(self::getName())][$partNumber] ?? null;
	}
}
