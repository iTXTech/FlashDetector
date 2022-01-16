<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2022 iTX Technologies
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

class Samsung extends Decoder{
	public const ID_DEFINITION = [
		2 => [
			"density" => [
				"dq" => [4, 3, 2, 1, 0],
				"def" => [
					0b10011 => 8 * Constants::DENSITY_GBITS,
					0b10101 => 16 * Constants::DENSITY_GBITS,
					0b10111 => 32 * Constants::DENSITY_GBITS,
					//0b11110 => 64 * Constants::DENSITY_GBITS, 0xDE, patch
					0b11010 => 128 * Constants::DENSITY_GBITS,
					0b11100 => 256 * Constants::DENSITY_GBITS,
					0b11110 => 512 * Constants::DENSITY_GBITS,
					0b11111 => 1 * Constants::DENSITY_TBITS
				]
			]
		],
		3 => [
			"die" => [
				"dq" => [1, 0],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 4,
					0b11 => 8
				]
			],
			"cellLevel" => [
				"dq" => [3, 2],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 3,
					0b11 => 4
				]
			],
			"ext:simultaneouslyProgrammedPages" => [
				"dq" => [5, 4],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 4,
					0b11 => 8
				]
			],
			"ext:interleave" => [
				"dq" => [6],
				"def" => [
					0 => false,
					1 => true
				]
			],
			"ext:cache" => [
				"dq" => [7],
				"def" => [
					0 => false,
					1 => true
				]
			]
		],
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
					0b011 => 1024
				]
			],
			"ext:redundantAreaSize" => [
				"dq" => [6, 3, 2],
				"def" => [
					0b001 => "128B",
					0b010 => "218B",
					0b011 => "400B",
					0b100 => "436B"
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
					0b000 => "1bit/512B",
					0b001 => "2bit/512B",
					0b010 => "4bit/512B",
					0b011 => "8bit/512B",
					0b100 => "16bit/512B",
					0b101 => "24bit/1KB"
				]
			]
		],
		6 => [
			"processNode" => [
				"dq" => [3, 2, 1, 0],
				"def" => [
					0x0 => "50nm",
					0x1 => "40nm",
					0x2 => "30nm",
					0x3 => "27nm",
					0x4 => "21nm",
					0x5 => "19nm",
					0x6 => "16nm",
					0x7 => "24L 3DV1",
					0x8 => "32L 3DV2",
					0x9 => "48L 3DV3",
					0xA => "14nm",
					0xB => "64L 3DV4",
					0xC => "92L 3DV5",
					0xD => "128L 3DV6"
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
					0 => "Conventional",
					1 => "ToggleDDR"
				]
			]
		]
	];

	public function __construct(){
		parent::__construct(Constants::VENDOR_SAMSUNG, 0xEC, self::ID_DEFINITION);
	}

	public function decode(int $id) : FlashIdInfo{
		$info = parent::decode($id);
		if(self::getByte($id, 2) == 0xDE){
			return $info->setDensity(64 * Constants::DENSITY_GBITS);
		}
		return $info;
	}
}
