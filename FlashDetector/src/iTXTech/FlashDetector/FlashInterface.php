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

class FlashInterface{
	private $isToggle;
	private $async = false;
	private $sync = false;
	private $toggle = false;
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

	public function getArray(){
		return $this->isToggle ? [
			"tog" => $this->toggle
		] : [
			"async" => $this->async,
			"sync" => $this->sync,
			"spi" => $this->spi
		];
	}
}
