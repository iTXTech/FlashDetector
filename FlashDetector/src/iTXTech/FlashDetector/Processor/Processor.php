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

namespace iTXTech\FlashDetector\Processor;

use iTXTech\FlashDetector\FlashInfo;

abstract class Processor{
	public function flashInfo(FlashInfo $flashInfo) : FlashInfo{
		return $flashInfo;
	}

	public function index(string $query, string $remote, string $ua, string $name, array &$c) : bool{
		return true;
	}

	public function info(string $query, string $remote, string $ua, array &$c) : bool{
		return true;
	}

	public function decode(string $query, string $remote, string $ua, ?string $lang, ?string $pn, array &$c) : bool{
		return true;
	}

	public function searchId(string $query, string $remote, string $ua, ?string $lang, ?string $id, array &$c) : bool{
		return true;
	}

	public function searchPn(string $query, string $remote, string $ua, ?string $lang, ?string $pn, int $limit, array &$c) : bool{
		return true;
	}

	public function summary(string $query, string $remote, string $ua, ?string $lang, ?string $pn, array &$c) : bool{
		return true;
	}
}
