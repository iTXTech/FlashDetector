<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

namespace iTXTech\FlashDetector;

class Classification{
	public const UNKNOWN_PROP = -1;

	private $ce;
	private $ch;
	private $rnb;
	private $die;

	public function __construct(int $ce = self::UNKNOWN_PROP,
	                            int $ch = self::UNKNOWN_PROP,
	                            int $rnb = self::UNKNOWN_PROP,
	                            int $die = self::UNKNOWN_PROP){
		$this->ce = $ce;
		$this->ch = $ch;
		$this->rnb = $rnb;
		$this->die = $die;
	}

	public function setCh(int $ch) : Classification{
		$this->ch = $ch;
		return $this;
	}

	public function setCe(int $ce) : Classification{
		$this->ce = $ce;
		return $this;
	}

	public function setDie(int $die) : Classification{
		$this->die = $die;
		return $this;
	}

	public function setRnb(int $rnb) : Classification{
		$this->rnb = $rnb;
		return $this;
	}

	public function getArray() : array {
		return [
			"nCE" => $this->ce,
			"Ch" => $this->ch,
			"RnB" => $this->rnb,
			"Die" => $this->die
		];
	}
}
