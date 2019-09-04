<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2019 iTX Technologies
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

class Fdb extends Arrayable{
	/** @var Info */
	protected $info;
	/** @var Vendor[] */
	protected $vendors = [];

	public function __construct(array $arr = null){
		if($arr != null){
			$this->info = new Info($arr["info"]);
			unset($arr["info"]);
			foreach($arr as $vendor => $pns){
				$this->vendors[$vendor] = new Vendor($vendor, $pns);
			}
		}
	}

	/**
	 * @return Vendor[]
	 */
	public function getVendors() : array{
		return $this->vendors;
	}

	public function getVendor(string $vendor) : ?Vendor{
		return $this->vendors[strtolower($vendor)] ?? null;
	}

	public function setVendors(array $vendors){
		$this->vendors = $vendors;
	}

	public function getPartNumber(string $vendor, string $partNumber, bool $real = false) : ?PartNumber{
		$vendor = strtolower($vendor);
		$partNumber = strtoupper($partNumber);
		if(isset($this->vendors[$vendor])){
			$pn = $this->vendors[$vendor]->getPartNumber($partNumber);
			if($pn != null){
				return $real ? $pn : clone $pn;
			}elseif($real){
				return $this->addPartNumber($vendor, $partNumber);
			}
		}
		return $real ? $this->addPartNumber($vendor, $partNumber) : null;
	}

	private function addPartNumber(string $vendor, string $partNumber) : PartNumber{
		if(!isset($this->vendors[$vendor])){
			$this->vendors[$vendor] = new Vendor($vendor);
		}
		$pn = new PartNumber($partNumber);
		$this->vendors[$vendor]->addPartNumber($pn);
		return $pn;
	}

	public function hasPartNumber(string $vendor, string $partNumber) : bool{
		return $this->getPartNumber($vendor, $partNumber) != null;
	}

	public function getInfo() : Info{
		return $this->info;
	}

	public function toArray() : array{
		$arr = [
			"info" => $this->info->toArray(),
		];
		foreach($this->vendors as $vendor){
			$arr[$vendor->getName()] = $vendor->toArray();
		}
		return $arr;
	}
}
