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

namespace iTXTech\FlashDetector\Property;

use iTXTech\FlashDetector\Arrayable;

class FlashInterface implements Arrayable{
	//is ToggleDDR or ONFi?
	private $isToggle;
	//For ONFi
	private $async = false;
	private $sync = false;
	///For Toggle DDR
	private $toggle = false;
	//SPI (Seems only Micron has this)
	private $spi = false;

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
