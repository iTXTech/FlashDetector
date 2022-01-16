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

class YMTC extends Decoder{
	public const ID_DEFINITION = [
		2 => [
			"density" => [
				"dq" => [7, 6, 5, 4, 3, 2, 1, 0],
				"def" => [
					0b11000011 => 256 * Constants::DENSITY_GBITS,
					0b11000100 => 512 * Constants::DENSITY_GBITS,
					0b11000101 => 1 * Constants::DENSITY_TBITS,
					0b11000110 => 2 * Constants::DENSITY_TBITS,
					0x49 => 64 * Constants::DENSITY_GBITS
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
			"voltage" => [
				"dq" => [6, 5, 4],
				"def" => [
					0b000 => "3.3V VCC, 3.3V VCCQ",
					0b001 => "3.3V VCC, 1.8V VCCQ",
					0b010 => "3.3V VCC, 1.2V VCCQ",
					0b011 => "3.3V VCC, 3.3V/1.8V VCCQ",
					0b100 => "3.3V VCC, 1.8V/1.2V VCCQ"
				]
			]
		],
		4 => [
			"pageSize" => [
				"dq" => [1, 0],
				"def" => [
					0b00 => 8,
					0b01 => 16,
				]
			],
			"blockSize" => [
				"dq" => [4, 3, 2],
				"def" => [
					0b000 => 6144,
					0b001 => 18 * 1024
				]
			],
			"plane" => [
				"dq" => [6, 5],
				"def" => [
					0b00 => 1,
					0b01 => 2,
					0b10 => 4,
					0b11 => 8
				]
			]
		],
		5 => [
			"processNode" => [
				"dq" => [6, 5, 4],
				"def" => [
					0b000 => "32L DBS 大别山",
					0b001 => "64L Xtacking JGS 井冈山",
					0b010 => "128L Xtacking 2.0"
				]
			]
		]
	];

	public function __construct(){
		parent::__construct(Constants::VENDOR_YANGTZE, 0x9B, self::ID_DEFINITION);
	}
}
