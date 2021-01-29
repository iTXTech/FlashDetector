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
use iTXTech\FlashDetector\Fdb\PartNumber;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Url;
use iTXTech\SimpleFramework\Util\StringUtil;

class MicronFbgaCode extends Decoder{
	protected const COUNTRY_CODE = [
		"1" => Constants::USA,
		"2" => Constants::SINGAPORE,
		"3" => Constants::ITALY,
		"4" => Constants::JAPAN,
		"5" => Constants::CHINA,
		"7" => Constants::TAIWAN,
		"8" => Constants::KOREA,
		"9" => Constants::MIXED,
		"B" => Constants::ISRAEL,
		"C" => Constants::IRELAND,
		"D" => Constants::MALAYSIA,
		"F" => Constants::PHILIPPINES
	];

	public static function getName() : string{
		return "MicronFBGACode";
	}

	public static function check(string $partNumber) : bool{
		foreach(["NW", "NX", "NQ", "PF"] as $h){
			if(StringUtil::startsWith($partNumber, $h) or
				(strlen($partNumber) == 10 and substr($partNumber, 5, 2) == $h)){
				return true;
			}
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		if(strlen($partNumber) == 10){
			$i = self::shiftChars($partNumber, 5);
		}
		$pn = FlashDetector::searchMicronFbgaCode($partNumber);
		if(count($pn) > 0){
			$pn = $pn[0];
			$info = FlashDetector::detect($pn)->setPartNumber($partNumber);
			if($info->getVendor() == Micron::getName()){
				$info->addUrl(new Url(
					Constants::MICRON_WEBSITE,
					"https://www.micron.com/support/tools-and-utilities/fbga?fbga=$partNumber",
					Url::IMAGE_LOGO
				));
			}
			$extra = $info->getExtraInfo();
			$extra[Constants::MICRON_PN] = $pn;
			if(isset($i)){
				$extra[Constants::PROD_DATE] = self::shiftChars($i, 1);
				$week = ((ord(self::shiftChars($i, 1)) - 64) * 2);
				$extra[Constants::PROD_DATE] .= strlen($week) == 1 ? "0" . $week : $week;
				self::shiftChars($i, 1);
				$extra[Constants::DIFFUSION] = self::getOrDefault(self::shiftChars($i, 1), self::COUNTRY_CODE);
				$extra[Constants::ENCAPSULATION] = self::getOrDefault(self::shiftChars($i, 1), self::COUNTRY_CODE);
			}
			return $info->setExtraInfo($extra);
		}else{
			return (new FlashInfo($partNumber))->setVendor(Constants::UNKNOWN);
		}
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		return null;
	}
}
