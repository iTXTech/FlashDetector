<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2020 iTX Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace iTXTech\FlashDetector\Processor;

use iTXTech\FlashDetector\FlashInfo;

abstract class Processor{
	public function flashInfo(FlashInfo $flashInfo) : FlashInfo{
		return $flashInfo;
	}

	public function index(string $query, string $remote, string $name, array &$c) : bool{
		return true;
	}

	public function info(string $query, string $remote, array &$c) : bool{
		return true;
	}

	public function decode(string $query, string $remote, ?string $lang, ?string $pn, array &$c) : bool{
		return true;
	}

	public function searchId(string $query, string $remote, ?string $lang, ?string $id, array &$c) : bool{
		return true;
	}

	public function searchPn(string $query, string $remote, ?string $lang, ?string $pn, array &$c) : bool{
		return true;
	}

	public function summary(string $query, string $remote, ?string $lang, ?string $pn, array &$c) : bool{
		return true;
	}
}
