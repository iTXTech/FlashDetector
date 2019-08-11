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
use iTXTech\SimpleFramework\Util\StringUtil;

class MicronFbgaCode extends Decoder{
	public static function getName() : string{
		return "MicronFBGACode";
	}

	public static function check(string $partNumber) : bool{
		foreach(["NW", "NX", "NQ", "PF"] as $h){
			if(StringUtil::startsWith($partNumber, $h)){
				return true;
			}
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$pn = FlashDetector::searchMicronFbgaCode($partNumber);
		if(count($pn) > 0){
			$pn = $pn[0];
			$info = FlashDetector::detect($pn)->setPartNumber($partNumber);
			$extra = $info->getExtraInfo();
			$extra["micronPartNumber"] = $pn;
			return $info->setExtraInfo($extra);
		}else{
			return (new FlashInfo($partNumber))->setManufacturer(Constants::UNKNOWN);
		}
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?array{
		return null;
	}
}
