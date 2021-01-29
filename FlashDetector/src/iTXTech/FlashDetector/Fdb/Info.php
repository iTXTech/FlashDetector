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
