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
use iTXTech\FlashDetector\Fdb\PartNumber;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\FlashInfo;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\SimpleFramework\Util\StringUtil;

//TODO: rename to Western Digital
class SanDisk extends Decoder{
	public const CELL_LEVEL = [
		"C" => 3,
		"F" => 2,
		"G" => 2,
		"H" => 2,
		"I" => 3,
		"M" => 2,
		"N" => 3,
		"Q" => 2,
	];

	public const DENSITY = [
		"1024" => Constants::DENSITY_GBITS, //128M
		"2048" => 2 * Constants::DENSITY_GBITS, //256M
		"4096" => 4 * Constants::DENSITY_GBITS,
		"001G" => 8 * Constants::DENSITY_GBITS,
		"002G" => 16 * Constants::DENSITY_GBITS,
		"004G" => 32 * Constants::DENSITY_GBITS,
		"008G" => 64 * Constants::DENSITY_GBITS,
		"016G" => 128 * Constants::DENSITY_GBITS,
		"032G" => 256 * Constants::DENSITY_GBITS,
		"064G" => 512 * Constants::DENSITY_GBITS,
		"128G" => Constants::DENSITY_TBITS,
		"256G" => 2 * Constants::DENSITY_TBITS,
		"512G" => 4 * Constants::DENSITY_TBITS
	];

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
				->setExtraInfo([Constants::UNSUPPORTED_REASON => Constants::SANDISK_INAND_NOT_SUPPORTED]);
		}elseif(substr($partNumber, 0, 2) === "IS"){
			return $flashInfo->setType(Constants::NAND_TYPE_ISSD)
				->setExtraInfo([Constants::UNSUPPORTED_REASON => Constants::SANDISK_ISSD_NOT_SUPPORTED]);
		}

		$flashInfo->setType(Constants::NAND_TYPE_NAND)
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"T" => "TSOP",
				"Y" => "BGA",
				"Z" => "LGA",
				"Q" => "BGA"
				//W ?
			]));
		if($partNumber{0} == "N"){
			$partNumber = substr($partNumber, 1);//remove N for NAND
		}
		$flashInfo->setProcessNode(self::getOrDefault(self::shiftChars($partNumber, 1), [
			"A" => "BiCS2",
			"B" => "BiCS3",
			"C" => "BiCS4",
			"L" => "56 nm",
			"M" => "43 nm",
			"N" => "32 nm",
			"P" => "24 nm",
			"Q" => "19 nm",
			"R" => "1y nm",
			"S" => "15 nm",
		]));

		$cell = self::shiftChars($partNumber, 1);
		$flashInfo->setCellLevel(self::getOrDefault($cell, self::CELL_LEVEL))
			->setClassification(new Classification(
				Classification::UNKNOWN_PROP, Classification::UNKNOWN_PROP, Classification::UNKNOWN_PROP,
				self::getOrDefault(self::shiftChars($partNumber, 1), [
					"A" => 1,
					"B" => 2,
					"C" => 4,
					"D" => 8,
				], -1)))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"H" => "2.7V~3.6V"
			]))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"E" => 8,
				"S" => 16
			], -1));

		if(StringUtil::startsWith($partNumber, "M")){
			$flashInfo->setExtraInfo([
				Constants::LEAD_FREE => true,
			]);
			$partNumber = substr($partNumber, 1);
		}
		if(StringUtil::startsWith($partNumber, "-")){
			$partNumber = substr($partNumber, 1);
			$flashInfo->setDensity(self::matchFromStart($partNumber, self::DENSITY, 0));
		}

		return $flashInfo;
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		$data = FlashDetector::getFdb()->getPartNumber(self::getName(), $info->getPartNumber());
		if($data != null){
			$comment = "";
			$parts = explode("/", $data->getComment() ?? "");
			$extraInfo = $info->getExtraInfo() ?? [];
			foreach($parts as $part){
				if($part == ""){
					continue;
				}elseif($part == "CODE"){
					$extraInfo[Constants::SANDISK_CODE] = true;
				}elseif($part{0} == "T"){
					$extraInfo[Constants::MANUFACTURER_TOSHIBA] = substr($part, 1);
				}else{
					$comment .= $part . "/";
				}
			}
			if($comment != ""){
				$comment = substr($comment, 0, strlen($comment) - 1);
			}
			$data->setComment($comment);
			$info->setExtraInfo($extraInfo);
		}
		return $data;
	}
}
