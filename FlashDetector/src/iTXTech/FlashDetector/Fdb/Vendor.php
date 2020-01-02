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
use iTXTech\FlashDetector\Constants;

class Vendor extends Arrayable{
	public const VENDOR_PATCH = [
		"sandisk" => Constants::VENDOR_WESTERN_DIGITAL,
		"toshiba" => Constants::VENDOR_KIOXIA
	];
	protected $name;
	/** @var PartNumber[] */
	protected $pns;

	public function __construct(string $name, array $pns = []){
		$this->name = strtolower($name);
		foreach($pns as $pn => $i){
			$this->pns[strtoupper($pn)] = new PartNumber($pn, $i);
		}
	}

	public function getName() : string{
		return self::VENDOR_PATCH[$this->name] ?? $this->name;
	}

	/**
	 * @return PartNumber[]
	 */
	public function getPartNumbers() : array{
		return $this->pns;
	}

	public function getPartNumber(string $partNumber) : ?PartNumber{
		return $this->pns[strtoupper($partNumber)] ?? null;
	}

	public function addPartNumber(PartNumber $pn){
		$this->pns[strtoupper($pn->getPartNumber())] = $pn;
	}

	public function toArray() : array{
		$arr = [];
		foreach($this->pns as $pn){
			$arr[$pn->getPartNumber()] = $pn->toArray();
		}
		return $arr;
	}
}
