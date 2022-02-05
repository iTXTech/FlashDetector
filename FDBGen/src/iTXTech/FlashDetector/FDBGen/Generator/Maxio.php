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

namespace iTXTech\FlashDetector\FDBGen\Generator;

use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class Maxio extends JMicron {
	public static function getDirName(): string {
		return "ma";
	}

	public static function merge(Fdb $fdb, string $data, string $filename): void {
		if (StringUtil::startsWith($filename, "map")) {
			self::mergeInternal($fdb, $data, $filename, "", false, true);
		} else {
			self::mergeInternal($fdb, $data, $filename, "", true, true);
		}
	}
}
