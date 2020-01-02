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

namespace iTXTech\FlashDetector\Fdb;

use iTXTech\FlashDetector\Arrayable;

class Info extends Arrayable{
	protected $name;
	protected $version;
	protected $website;
	protected $time;
	protected $controllers = [];

	public function getName() : ?string{
		return $this->name;
	}

	public function setName(string $name){
		$this->name = $name;
		return $this;
	}

	public function getVersion() : ?int{
		return $this->version;
	}

	public function setVersion(int $version){
		$this->version = $version;
	}

	public function getWebsite() : ?string{
		return $this->website;
	}

	public function setWebsite(string $website){
		$this->website = $website;
	}

	public function getTime() : ?string{
		return $this->time;
	}

	public function setTime(string $time){
		$this->time = $time;
	}

	public function getControllers() : ?array{
		return $this->controllers;
	}

	public function addController($con){
		if(!is_array($con)){
			$con = [$con];
		}
		foreach($con as $c){
			if(!in_array($c, $this->controllers)){
				$this->controllers[] = $c;
			}
		}
	}
}
