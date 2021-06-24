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

namespace iTXTech\FlashDetector\FlashId;

use iTXTech\FlashDetector\Constants;
use iTXTech\FlashDetector\FlashIdInfo;

class SKHynix extends Decoder{
	public const ID_DEFINITION = [
		2 => [
			"density" => [
				"dq" => [7, 6, 5, 4, 3, 2, 1, 0],
				"def" => [
					0xD3 => 8 * Constants::DENSITY_GBITS,
					0xD5 => 16 * Constants::DENSITY_GBITS,
					0xD7 => 32 * Constants::DENSITY_GBITS,
					0xDE => 64 * Constants::DENSITY_GBITS,
					0x3A => 128 * Constants::DENSITY_GBITS, //Voltage
					0x5A => 128 * Constants::DENSITY_GBITS,
					0x3C => 256 * Constants::DENSITY_GBITS,
					0x5C => 256 * Constants::DENSITY_GBITS,
					0x3E => 512 * Constants::DENSITY_GBITS,
					0x5E => 512 * Constants::DENSITY_GBITS,
					0x89 => 1 * Constants::DENSITY_TBITS,
				]
			]
		],
		3 => Samsung::ID_DEFINITION[3],
		4 => [
			"pageSize" => [
				"dq" => [1, 0],
				"def" => [
					0b00 => 2,
					0b01 => 4,
					0b10 => 8,
					0b11 => 16
				]
			],
			"blockSize" => [
				"dq" => [7, 5, 4],
				"def" => [
					0b000 => 128,
					0b001 => 256,
					0b010 => 512,
					0b011 => 768,
					0b100 => 1024,
					0b101 => 2048
				]
			],
			"ext:redundantAreaSize" => [
				"dq" => [6, 3, 2],
				"def" => [
					0b110 => "640B",
					0b010 => "448B",
					0b001 => "224B",
					0b000 => "128B",
					0b011 => "64B",
					0b100 => "32B",
					0b101 => "16B"
				]
			]
		],
		5 => [
			"plane" => [
				"dq" => [3, 2],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 4,
					0b11 => 8
				]
			],
			"ext:eccLevel" => [
				"dq" => [6, 5, 4],
				"def" => [
					0b000 => "None",
					0b001 => "1bit/512B",
					0b010 => "2bit/512B",
					0b011 => "4bit/512B",
					0b100 => "8bit/512B",
					0b101 => "24bit/512B",
					0b110 => "32bit/1KB",
					0b111 => "40bit/1KB"
				]
			]
		],
		6 => [
			"processNode" => [
				"dq" => [3, 2, 1, 0],
				"def" => [
					0x0 => "48nm",
					0x1 => "41nm",
					0x2 => "32nm",
					0x3 => "26nm",
					0x4 => "20nm",
					0x5 => "16nm",
					0xA => "16nm"
				]
			],
			"ext:edo" => [
				"dq" => [6],
				"def" => [
					0 => false,
					1 => true
				]
			],
			"ext:interface" => [
				"dq" => [7],
				"def" => [
					0 => "Async Only",
					1 => "Async and Sync"
				]
			]
		]
	];

	public const NEW_ID_DEFINITION = [
		6 => [
			"processNode" => [
				"dq" => [7, 6, 5, 4],
				"def" => [
					0x5 => "14nm",
					0x7 => "36L 3DV2",
					0x8 => "48L 3DV3",
					0x9 => "72L 3DV4",
					0xA => "96L 3DV5",
					0xB => "128L 3DV6"
				]
			]
		]
	];

	public function __construct(){
		parent::__construct(Constants::VENDOR_SKHYNIX, 0xAD, self::ID_DEFINITION);
	}

	public function decode(int $id) : FlashIdInfo{
		$info = parent::decode($id);
		$info->setPlane($info->ext["simultaneouslyProgrammedPages"]);
		if(self::getByte($id, 2) == 0xDE){ //0xDE patch
			$info->setDensity(64 * Constants::DENSITY_GBITS);
		}
		if(self::getByte($id, 6) >= 0x50){ //14nm and after
			$info->setExt([])->setBlockSize(null);
			$info = $this->decodeIdDef($id, self::NEW_ID_DEFINITION, $info);
		}
		return $info;
	}
}
