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

class Intel extends Decoder{
	public const ID_DEFINITION = [
		2 => [
			"voltage" => [
				"dq" => [2, 1, 0],
				"def" => [
					0b011 => "Vcc: 2.5V/3.3V",
					0b100 => "Vcc: 3.3V"
				]
			],
			"density" => [
				"dq" => [7, 6, 5, 4, 3],
				"def" => [
					0b01001 => 32 * Constants::DENSITY_GBITS,
					0b10001 => 64 * Constants::DENSITY_GBITS,
					0b10000 => 128 * Constants::DENSITY_GBITS,
					0b10100 => 256 * Constants::DENSITY_GBITS,
					0b10110 => 384 * Constants::DENSITY_GBITS,
					0b11000 => 512 * Constants::DENSITY_GBITS,
					0b11010 => 1 * Constants::DENSITY_TBITS,
					0b11100 => 2 * Constants::DENSITY_TBITS,
					0b11110 => 4 * Constants::DENSITY_TBITS,
					0b00101 => 8 * Constants::DENSITY_TBITS
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
					0b11 => 4,
				]
			],
			"ext:pagesPerBlock" => [
				"dq" => [6, 5, 4],
				"def" => [
					0b010 => 9216
				]
			]
		],
		4 => [
			"pageSize" => [
				"dq" => [2, 1, 0],
				"def" => [
					0b110 => 4,
					0b111 => 8,
					0b011 => 8,
					0b010 => 16,
					0b100 => 16,
				]
			],
			"ext:spareAreaSizePer512B" => [
				"dq" => [6, 5, 4, 3],
				"def" => [
					"0b0110" => "61-70B",
				]
			]
		],
		5 => [
			"plane" => [
				"dq" => [1, 0],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 4
				]
			],
			"ext:blocksPerLun" => [
				"dq" => [4, 3, 2],
				"def" => [
					0b001 => "1025~2048"
				]
			],
			"ext:timingModeAsync" => [
				"dq" => [7, 6, 5],
				"def" => [
					0b000 => "0 (100ns)",
					0b001 => "1 (50ns)",
					0b010 => "2 (35ns)",
					0b011 => "3 (30ns)",
					0b100 => "4 (25ns)",
					0b101 => "5 (20ns)",
					0b110 => "Default"
				]
			]
		],
		6 => [
			"ext:revision" => [
				"dq" => [3, 2],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 3,
					0b11 => 4
				]
			]
		]
	];

	public function __construct(){
		parent::__construct(Constants::VENDOR_INTEL, 0x89, self::ID_DEFINITION);
	}
}
