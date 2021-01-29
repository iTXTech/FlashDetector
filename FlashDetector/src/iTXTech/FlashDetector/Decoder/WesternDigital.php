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
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\SimpleFramework\Util\StringUtil;

class WesternDigital extends Decoder{
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
		return Constants::VENDOR_WESTERN_DIGITAL;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "SD")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName());
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
		if(strlen($partNumber) == 0){
			return $flashInfo;
		}
		if($partNumber[0] == "N"){
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
		$data = FlashDetector::getFdb()->getPartNumber(self::getName(), $info->getPartNumber()) ??
			FlashDetector::getFdb()->getPartNumber(Constants::VENDOR_SANDISK, $info->getPartNumber());
		if($data != null){
			$remark = "";
			$parts = explode("/", $data->getRemark() ?? "");
			$extraInfo = $info->getExtraInfo() ?? [];
			foreach($parts as $part){
				if($part == ""){
					continue;
				}elseif($part == "CODE"){
					$extraInfo[Constants::SANDISK_CODE] = true;
				}elseif($part[0] == "T"){
					$extraInfo[Constants::VENDOR_KIOXIA] = substr($part, 1);
				}else{
					$remark .= $part . "/";
				}
			}
			if($remark != ""){
				$remark = substr($remark, 0, strlen($remark) - 1);
			}
			$data->setRemark($remark, true);
			$info->setExtraInfo($extraInfo);
		}
		return $data;
	}
}
