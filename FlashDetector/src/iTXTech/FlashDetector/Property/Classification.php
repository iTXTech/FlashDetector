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

namespace iTXTech\FlashDetector\Property;

use iTXTech\FlashDetector\Arrayable;

class Classification extends Arrayable{
	public const UNKNOWN_PROP = -1;
	public const CHANNEL_SINGLE_OR_DUAL = 2;

	protected $ce;
	protected $ch;
	protected $rb;
	protected $die;

	public function __construct(int $ce = self::UNKNOWN_PROP,
	                            int $ch = self::UNKNOWN_PROP,
	                            int $rb = self::UNKNOWN_PROP,
	                            int $die = self::UNKNOWN_PROP){
		$this->ce = $ce;
		$this->ch = $ch;
		$this->rb = $rb;
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

	public function setRb(int $rb) : Classification{
		$this->rb = $rb;
		return $this;
	}

	public function getCe() : int{
		return $this->ce;
	}

	public function getCh() : int{
		return $this->ch;
	}

	public function getDie() : int{
		return $this->die;
	}

	public function getRb() : int{
		return $this->rb;
	}
}
