<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2023 iTX Technologies
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

// Research Code for IMFT Lithography / Process Node

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;

Logger::$logLevel = 0;

function decode(string $id, string $litho = "") {
	$info = FlashDetector::decodeFlashId($id);
	$str = "";
	foreach ([
				 "FlashId" => $id,
				 "DieSize" => ($info->density / $info->die),
				 "Revision" => $info->ext["revision"] ?? "unknown",
				 "Cell" => $info->cellLevel,
				 "Plane" => $info->plane,
				 "PageSize" => $info->pageSize,
				 "Litho" => $litho
			 ] as $k => $v) {
		$str .= $k . " " . TextFormat::GOLD . $v . TextFormat::WHITE . " ";

	}
	Logger::info($str);
}

foreach ([
//	["2C,D7,94,3E,84,00", "L63A"],
//	["2C,88,05,C6,89,00", "L63B"],
			 ["2C,68,04,4A,A9,00", "L73A"],
			 ["2C,88,04,4B,A9,00", "L74A"],
			 ["2C,84,C5,4B,A9,00", "L84A"],
			 ["2C,84,64,3C,A5,00", "L85A"],
			 ["2C,84,64,3C,A9,04", "L85C"],
			 ["2C,84,64,54,A9,00", "L95B"]
		 ] as $i) {
	decode(str_replace([",", " "], "", $i[0]), $i[1]);
}

