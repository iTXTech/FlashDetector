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

namespace iTXTech\FlashDetector\FDBGen\Generator;

use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\SimpleFramework\Util\StringUtil;

class PhisonSSD extends Generator {
	public const IDENTICAL_CONTROLLERS = ["PS3111", "INIC6081"];

	public static function getDirName(): string {
		return "ps";
	}

	public static function merge(Fdb $fdb, string $data, string $filename): void {
		$fdb->getInfo()->addController(self::IDENTICAL_CONTROLLERS);
		$flashes = json_decode($data, true);
		foreach ($flashes as $flash) {
			$vendor = strtolower($flash["Vendor"]);
			switch ($vendor) {
				case "hynix":
					$vendor = "skhynix";
					break;
			}
			$flashId = substr($flash["FlashId"], 0, 12);

			foreach ($fdb->getVendor($vendor)->getPartNumbers() as $pn => $f) {
				foreach ($f->getFlashIds() as $id) {
					if (StringUtil::startsWith($id, $flashId)) {
						$fdb->getIddb()->getFlashId($id)->addController(self::IDENTICAL_CONTROLLERS);
						$f->addController(self::IDENTICAL_CONTROLLERS);
						break 2;
					}
				}
			}
		}
	}
}
