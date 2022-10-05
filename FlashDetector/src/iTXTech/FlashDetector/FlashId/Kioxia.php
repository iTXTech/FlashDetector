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

class Kioxia extends Decoder {
	public const ID_DEFINITION = [
		2 => [
			"density" => [
				"dq" => [7, 6, 5, 4, 3, 2, 1, 0],
				"def" => [
					0xD3 => 8 * Constants::DENSITY_GBITS,
					0xD5 => 16 * Constants::DENSITY_GBITS,
					0xD7 => 32 * Constants::DENSITY_GBITS,
					0xDE => 64 * Constants::DENSITY_GBITS,
					0x3A => 128 * Constants::DENSITY_GBITS,
					0x3C => 256 * Constants::DENSITY_GBITS,
					0x3E => 512 * Constants::DENSITY_GBITS,
					0x48 => 1 * Constants::DENSITY_TBITS, //48
					0x49 => 2 * Constants::DENSITY_TBITS,
					0x40 => 4 * Constants::DENSITY_TBITS,
					0x4C => 256 * Constants::DENSITY_GBITS
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
					0b00 => 1, //SLC
					0b01 => 2, //MLC
					0b10 => 3, //TLC
					0b11 => 4, //QLC
				]
			]
		],
		4 => [
			"pageSize" => [
				"dq" => [1, 0],
				"def" => [
					0b00 => 2, //2KB
					0b01 => 4, //4KB
					0b10 => 8, //8KB
					0b11 => 16, //16KB
				]
			],
			"blockSize" => [ //KB
				"dq" => [7, 5, 4],
				"def" => [
					0b000 => 128,
					0b001 => 256,
					0b010 => 512,
					0b011 => 1024,
					0b100 => 2048,
					0b101 => 4096,
					0b110 => 6144,
					//0b111 => 0
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
			]
		],
		6 => [
			"ext:interface" => [
				"dq" => [7],
				"def" => [
					0b0 => "Conventional",
					0b1 => "ToggleDDR"
				]
			],
			"processNode" => [
				"dq" => [6, 5, 2, 1, 0],
				"def" => [
					0b10100 => "43nm",
					0b10101 => "32nm",
					0b10110 => "24nm",
					0b10111 => "19nm",
					0b10000 => "A19nm",
					0b10001 => "15nm",
					0b11001 => "BiCS2 48L",
					0b11010 => "BiCS3 64L",
					0b11011 => "BiCS4 96L",
					0b11100 => "BiCS5 112L"
				]
			]
		]
	];

	public function __construct() {
		parent::__construct(Constants::VENDOR_KIOXIA, 0x98, self::ID_DEFINITION);
	}

	public function decode(int $id): FlashIdInfo {
		$info = parent::decode($id);
		if(self::checkProperties($info->plane, $info->die)) {
			return $info->setPlane($info->plane / $info->die);
		}
		return $info;
	}
}
