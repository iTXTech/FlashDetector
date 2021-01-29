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

class FlashInterface extends Arrayable{
	//is ToggleDDR or ONFi?
	protected $isToggle;
	//For ONFi
	protected $async = false;
	protected $sync = false;
	///For Toggle DDR
	protected $toggle = false;
	//SPI (Seems only Micron has this)
	protected $spi = false;

	public function __construct(bool $isToggle){
		$this->isToggle = $isToggle;
	}

	public function setAsync(bool $async) : FlashInterface{
		$this->async = $async;
		return $this;
	}

	public function setSync(bool $sync) : FlashInterface{
		$this->sync = $sync;
		return $this;
	}

	public function setSpi(bool $spi) : FlashInterface{
		$this->spi = $spi;
		return $this;
	}

	public function setToggle(bool $toggle) : FlashInterface{
		$this->toggle = $toggle;
		return $this;
	}

	public function toArray() : array{
		return $this->isToggle ? [
			"toggle" => $this->toggle
		] : ($this->spi ? [
			"spi" => $this->spi
		] : [
			"async" => $this->async,
			"sync" => $this->sync
		]);
	}
}
