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
