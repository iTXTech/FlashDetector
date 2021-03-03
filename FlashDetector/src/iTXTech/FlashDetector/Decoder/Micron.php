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
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\FlashDetector\Property\Url;
use iTXTech\SimpleFramework\Util\StringUtil;

class Micron extends Decoder{
	protected const PACKAGE = [
		"WP" => "48-pin TSOP I Center Package Leads (CPL) PB free",
		"WC" => "48-pin TSOP I Off-center Package Leads (OCPL) PB free",
		"C5" => "52-pad VLGA, 14 x 18 x 1.0 (SDP/DDP/QDP)",
		"G1" => "272/352 ball VBGA, 14 x 18 x 1.0 (SDP, DDP, 3DP, QDP)",
		"G2" => "272/352 ball TBGA, 14 x 18 x 1.3 (QDP, 8DP)",
		"G6" => "272/352 ball LBGA, 14 x 18 x 1.5 (16DP)",
		"H1" => "100/170 ball VBGA, 12 x 18 x 1.0",
		"H2" => "100/170 ball TBGA, 12 x 18 x 1.2",
		"H3" => "100/170 ball LBGA, 12 x 18 x 1.4 (8DP)",
		"H4" => "63/120 ball VFBGA, 9 x 11 x 1.0",
		"HC" => "63/120 ball VFBGA, 10.5 x 13 x 1.0",
		"H6" => "152/221 ball VBGA, 14 x 18 x 1.0 (SDP, DDP)",
		"H7" => "152/221 ball TBGA, 14 x 18 x 1.2 (QDP)",
		"H8" => "152/221 ball LBGA, 14 x 18 x 1.4 (8DP)",
		"H9" => "100-ball LBGA, 12 x 18 x 1.6 (16DP)",
		"J1" => "132/187 ball VBGA, 12 x 18 x 1.0 (SDP, DDP)",
		"J2" => "132/187 ball TBGA, 12 x 18 x 1.2 (QDP)",
		"J3" => "132/187 ball LBGA, 12 x 18 x 1.4 (8DP)",
		"J4" => "132/187 ball VBGA, 12 x 18 x 1.0 (SDP, DDP)",
		"J5" => "132/187 all TBGA, 12 x 18 x 1.2 (QDP)",
		"J6" => "132/187 ball LBGA, 12 x 18 x 1.4 (8DP)",
		"J7" => "152/221 ball LBGA, 14 x 18 x 1.5 (16DP)",
		"J9" => "132-ball LBGA, 12 x 18 x 1.5 (16DP)",
		//SpecTek
		"C3" => "52-pad ULGA, 12 x 17 x 0.65",
		"C4" => "52-pad VLGA, 12 x 17 x 1.0",
		"C6" => "52-pad LLGA, 14 x 18 x 1.47",
		"C7" => "48-pad LLGA, 12 x 20 x 1.47",
		"C8" => "52-pad WLGA, 14 x 18 x 0.75",
		"D1" => "52-pad VLGA, 11 x 14 x 0.9",
		"G4" => "252/308 ball LFBGA, 12 x 18 x 1.5",
		"G5" => "272/352 ball LFBGA, 14 x 18 x 1.4",
		"G7" => "252/308 ball LFBGA, 12 x 18 x 1.0",
		"G8" => "252/308 ball LFBGA, 12 x 18 x 1.3",
		"G9" => "252/308 ball LFBGA, 12 x 18 x 1.4",
		"H5" => "56/256 ball VFBGA, 12.8 x 9.5 x 1.0",
		"K3" => "100/170 ball VLGA 12 x 18 x 0.9",
		"K4" => "100/170 ball TLGA, 12 x 18 x 1.1",
		"K7" => "152/221 ball VLGA 14 x 18 x 0.9",
		"K8" => "152/221 ball TLGA 14 x 18 x 1.1",
		"K9" => "132/187 ball VLGA, 12 x 18 x 1.0",
		"MD" => "130-ball VFBGA, 8 x 9 x 1.0",
		"M4" => "132/187 ball TBGA, 12 x 18 x 1.3",
		"M5" => "132/187 ball LBGA, 12 x 18 x 1.5",
		"M8" => "55-ball VFBGA, 8 x 10 x 1.2",//should be M8Z
	];
	protected const DENSITY = [
        "1G" => 1 * Constants::DENSITY_GBITS,
        "2G" => 2 * Constants::DENSITY_GBITS,
        "4G" => 4 * Constants::DENSITY_GBITS,
        "8G" => 8 * Constants::DENSITY_GBITS,
        "16G" => 16 * Constants::DENSITY_GBITS,
        "21G" => 21 * Constants::DENSITY_GBITS,
        "32G" => 32 * Constants::DENSITY_GBITS,
        "42G" => 42 * Constants::DENSITY_GBITS,
        "64G" => 64 * Constants::DENSITY_GBITS,
        "84G" => 84 * Constants::DENSITY_GBITS,
        "128G" => 128 * Constants::DENSITY_GBITS,
        "168G" => 168 * Constants::DENSITY_GBITS,
        "192G" => 192 * Constants::DENSITY_GBITS,
        "256G" => 256 * Constants::DENSITY_GBITS,
        "336G" => 336 * Constants::DENSITY_GBITS,
        "384G" => 384 * Constants::DENSITY_GBITS,
        "512G" => 512 * Constants::DENSITY_GBITS,
        "768G" => 768 * Constants::DENSITY_GBITS,
		"1T" => Constants::DENSITY_TBITS,
		"1T2" => 1.125 * Constants::DENSITY_TBITS,
		"1HT" => 1.5 * Constants::DENSITY_TBITS,
		"2T" => 2 * Constants::DENSITY_TBITS,
		"3T" => 3 * Constants::DENSITY_TBITS,
		"4T" => 4 * Constants::DENSITY_TBITS,
		"6T" => 6 * Constants::DENSITY_TBITS,
		"8T" => 8 * Constants::DENSITY_TBITS,
		"16T" => 16 * Constants::DENSITY_TBITS,
	];
	protected const CLASSIFICATION = [
		"A" => [1, 0, 0, 1],//die, ce, rb, ch
		"B" => [1, 1, 1, 1],
		"D" => [2, 1, 1, 1],
		"E" => [2, 2, 2, 2],
		"F" => [2, 2, 2, 1],
		"G" => [3, 3, 3, 3],
		"J" => [4, 2, 2, 1],
		"K" => [4, 2, 2, 2],
		"L" => [4, 4, 4, 4],
		"M" => [4, 4, 4, 2],
		"Q" => [8, 4, 4, 4],
		"R" => [8, 2, 2, 2],
		"T" => [16, 8, 4, 2],
		"U" => [8, 4, 4, 2],
		"V" => [16, 8, 4, 4],
		//SpecTek, no R/nB documented
		"C" => [3, 3, -1, 2],
		"H" => [4, 1, -1, 1],
		"N" => [6, 6, -1, 3],
		"P" => [8, 8, -1, 2],
		"W" => [16, 4, -1, 2],
		"X" => [4, 4, -1, 2],
		"Y" => [11, 7, -1, 4],
		"1" => [16, 2, -1, 1],
		"2" => [64, 8, -1, 2],
		"3" => [8, 4, -1, 2],
		"4" => [4, 4, -1, 1],
		"S" => [16, 4, -1, 4]
	];
	protected const INTERFACE = [
		"A" => [false, true, false],//sync, async, spi
		"B" => [true, true, false],
		"C" => [true, false, false],
		"D" => [false, false, true],
		//SpecTek
		"E" => [true, true, false],//TODO: confirm
		"F" => [true, true, false],
		"G" => [true, true, false],//TODO: confirm
		"M" => [false, false, false],//TODO: confirm
		"N" => [true, true, false]
	];
	protected const SPEED_GRADE = [
		"15" => "NV-DDR TM3 133MT/s",
		"12" => "NV-DDR TM4 166MT/s",
		"10" => "NV-DDR TM5 200MT/s",
		"75" => "NV-DDR2 TM5 266MT/s",
		"6" => "NV-DDR2 TM6 333MT/s",
		"5" => "NV-DDR2 TM7 400MT/s",
		"37" => "NV-DDR2 TM8 533MT/s",
		"3" => "NV-DDR3 TM9 666MT/s",
		"25" => "NV-DDR3 TM10 800MT/s",
		"18" => "NV-DDR3 TM11 1066MT/s",
		"16" => "NV-DDR3 TM12 1200MT/s"
	];
	protected const OPERATING_TEMP_RANGE = [
		"AAT" => Constants::MICRON_OTR_AAT,
		"AIT" => Constants::MICRON_OTR_AIT,
		"IT" => Constants::MICRON_OTR_IT,
		"ET" => Constants::MICRON_OTR_IT,
		"WT" => Constants::MICRON_OTR_WT,
	];
	protected const FEATURES = [
		"E" => Constants::MICRON_F_E,
		"M" => Constants::MICRON_F_M,
		"R" => Constants::MICRON_F_R,
		"T" => Constants::MICRON_F_T,
		"S" => Constants::MICRON_F_S,
		"X" => Constants::MICRON_F_X,
		"Z" => Constants::MICRON_F_Z,
	];
	protected const PROD_STATUS = [
		"ES" => Constants::MICRON_P_ES,
		"QS" => Constants::MICRON_P_QS,
		"MS" => Constants::MICRON_P_MS
	];

	public static function getName() : string{
		return Constants::VENDOR_MICRON;
	}

	public static function check(string $partNumber) : bool{
		if(StringUtil::startsWith($partNumber, "MT")){
			return true;
		}
		return false;
	}

	public static function decode(string $partNumber) : FlashInfo{
		$flashInfo = (new FlashInfo($partNumber))->setVendor(self::getName());
		$flashInfo->addUrl(new Url(
			Constants::MICRON_WEBSITE,
			"https://www.micron.com/support/tools-and-utilities/fbga?matpart=$partNumber",
			Url::IMAGE_LOGO
		));
		if(!StringUtil::startsWith($partNumber, "29")){
			$partNumber = substr($partNumber, 2);//remove MT
		}
		$partNumber = substr($partNumber, 2);//remove 29
		$extra[Constants::ENTERPRISE] = self::shiftChars($partNumber, 1) === "E";
		$flashInfo
			->setType(Constants::NAND_TYPE_NAND)
			->setDensity(self::matchFromStart($partNumber, self::DENSITY, 0))
			->setDeviceWidth(self::getOrDefault(self::shiftChars($partNumber, 2), [
				"01" => 1,
				"08" => 8,
				"16" => 16
			], -1))
			->setCellLevel(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => 1,
				"C" => 2,
				"E" => 3,
				"G" => 4
			]));

		$classification = self::getOrDefault(self::shiftChars($partNumber, 1),
			self::CLASSIFICATION, [-1, -1, -1, -1]);

		$flashInfo->setClassification(new Classification(
			$classification[1], $classification[3], $classification[2], $classification[0]))
			->setVoltage(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => "Vcc: 3.3V (2.70–3.60V), VccQ: 3.3V (2.70–3.60V)",
				"B" => "1.8V (1.70–1.95V)",
				"C" => "Vcc: 3.3V (2.70–3.60V), VccQ: 1.8V (1.70–1.95V)",
				"E" => "Vcc: 3.3V (2.70–3.60V), VccQ: 3.3V (2.70–3.60V) or 1.8V (1.70–1.95V)",
				"F" => "Vcc: 3.3V (2.50–3.60V), VccQ: 1.2V (1.14–1.26V)",
				"G" => "Vcc: 3.3V (2.60–3.60V), VccQ: 1.8V (1.70–1.95V)",
				"H" => "Vcc: 3.3V (2.50–3.60V), VccQ: 1.2V (1.14–1.26) or 1.8V (1.70–1.95V)",
				"J" => "Vcc: 3.3V (2.50–3.60V), VccQ: 1.8V (1.70–1.95V)",
				"K" => "Vcc: 3.3V (2.60–3.60V), VccQ: 3.3V (2.60–3.60V)",
				"L" => "Vcc: 2.5V (2.35–3.60V), VccQ: 1.2V (1.14–1.26V)",
			]))
			->setGeneration(self::getOrDefault(self::shiftChars($partNumber, 1), [
				"A" => 1,
				"B" => 2,
				"C" => 3,
				"D" => 4
			]));
		self::setInterface(self::shiftChars($partNumber, 1), $flashInfo)
			->setPackage(self::getOrDefault(self::shiftChars($partNumber, 2), self::PACKAGE));

		if(self::shiftChars($partNumber, 1) == "-"){
			$extra[Constants::PROD_STATUS] = Constants::MICRON_P;
			foreach(self::PROD_STATUS as $k => $stat){
				if(StringUtil::contains($partNumber, $k)){
					$extra[Constants::PROD_STATUS] = $stat;
					$partNumber = str_replace($k, "", $partNumber);
					break;
				}
			}
			$speed = self::matchFromStart($partNumber, self::SPEED_GRADE);
			if($speed != Constants::UNKNOWN){
				$extra[Constants::SPEED_GRADE] = $speed;
			}
			$extra[Constants::OPERATION_TEMPERATURE] = self::matchFromStart($partNumber, self::OPERATING_TEMP_RANGE, Constants::MICRON_OTR_C);
			$features = self::getOrDefault(self::shiftChars($partNumber, 1), self::FEATURES);
			if($features != Constants::UNKNOWN){
				$extra[Constants::FEATURES] = $features;
			}
			if(self::shiftChars($partNumber, 1) == ":" and ($rev = self::shiftChars($partNumber, 1)) != ""){
				$extra[Constants::DESIGN_REV] = $rev;
			}
		}


		return $flashInfo->setExtraInfo($extra);
	}

	protected static function setInterface(string $interface, FlashInfo $info) : FlashInfo{
		$i = self::getOrDefault($interface, self::INTERFACE, [false, false, false]);
		return $info->setInterface((new FlashInterface(false))
			->setSync($i[0])->setAsync($i[1])->setSpi($i[2]));
	}

	public static function getFlashInfoFromFdb(FlashInfo $info) : ?PartNumber{
		return FlashDetector::getFdb()->getPartNumber(self::getName(), self::removePackage($info->getPartNumber()));
	}

	public static function removePackage(string $pn) : string{
		if(SpecTek::check($pn)){
			return SpecTek::removePackage($pn);
		}
		$bit = strstr($pn, "08");
		if($bit !== false and strlen($bit) >= 8){
			$pn = substr($pn, 0, strlen($pn) + 7 - strlen($bit));
		}
		return $pn;
	}
}
