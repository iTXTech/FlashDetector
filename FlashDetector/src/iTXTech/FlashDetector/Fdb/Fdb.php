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

class Fdb extends Arrayable{
	/** @var Info */
	protected $info;
	/** @var Vendor[] */
	protected $vendors = [];
	/** @var Iddb */
	protected $iddb;

	public function __construct(array $arr = null){
		if($arr != null){
			$this->info = new Info($arr["info"]);
			$this->iddb = new Iddb($arr["iddb"]);
			unset($arr["info"], $arr["iddb"]);
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

	public function setIddb(Iddb $iddb){
		$this->iddb = $iddb;
	}

	public function getIddb() : Iddb{
		return $this->iddb;
	}

	public function toArray() : array{
		$arr = [
			"info" => $this->info->toArray(),
			"iddb" => $this->iddb->toArray()
		];
		foreach($this->vendors as $vendor){
			$arr[$vendor->getName()] = $vendor->toArray();
		}
		return $arr;
	}
}
