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

class Micron extends Intel {
	public function __construct(string $name = Constants::VENDOR_MICRON, int $id = 0x2C) {
		$def = self::ID_DEFINITION;
		$def[6]["ext:enterprise"] = [
			"dq" => [1, 0],
			"def" => [
				0b00 => false,
				0b01 => true,
			]
		];
		Decoder::__construct($name, $id, $def);
	}
}
