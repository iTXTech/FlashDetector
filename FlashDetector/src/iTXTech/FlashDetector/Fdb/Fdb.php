<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2022 iTX Technologies
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

class Fdb extends Arrayable {
	/** @var Info */
	protected $info;
	/** @var Vendor[] */
	protected $vendors = [];
	/** @var Iddb */
	protected $iddb;

	public static function fromJson(string $json): Fdb {
		return new Fdb(json_decode($json, true));
	}

	public function __construct(array $arr = null) {
		if ($arr != null) {
			$this->info = new Info($arr["info"]);
			$this->iddb = new Iddb($arr["iddb"]);
			unset($arr["info"], $arr["iddb"]);
			foreach ($arr as $vendor => $pns) {
				$this->vendors[$vendor] = new Vendor($vendor, $pns);
			}
		}
	}

	/**
	 * @return Vendor[]
	 */
	public function getVendors(): array {
		return $this->vendors;
	}

	public function getVendor(string $vendor): ?Vendor {
		return $this->vendors[Vendor::getInternalName($vendor)] ?? null;
	}

	public function setVendor(Vendor $vendor) {
		$this->vendors[$vendor->getName()] = $vendor;
	}

	public function getPartNumber(string $vendor, string $partNumber, bool $real = false): ?PartNumber {
		$vendor = trim(strtolower($vendor));
		$partNumber = trim(strtoupper($partNumber));
		if ($this->getVendor($vendor)) {
			$pn = $this->getVendor($vendor)->getPartNumber($partNumber);
			if ($pn != null) {
				return $real ? $pn : clone $pn;
			} elseif ($real) {
				return $this->addPartNumber($vendor, $partNumber);
			}
		}
		return $real ? $this->addPartNumber($vendor, $partNumber) : null;
	}

	private function addPartNumber(string $vendor, string $partNumber): PartNumber {
		if (!$this->getVendor($vendor)) {
			$this->setVendor(new Vendor($vendor));
		}
		$pn = new PartNumber($partNumber);
		$this->getVendor($vendor)->addPartNumber($pn);
		return $pn;
	}

	public function hasPartNumber(string $vendor, string $partNumber): bool {
		return $this->getPartNumber($vendor, $partNumber) != null;
	}

	public function getInfo(): Info {
		return $this->info;
	}

	public function setIddb(Iddb $iddb) {
		$this->iddb = $iddb;
	}

	public function getIddb(): Iddb {
		return $this->iddb;
	}

	public function toArray(): array {
		$arr = [
			"info" => $this->info->toArray(),
			"iddb" => $this->iddb->toArray()
		];
		foreach ($this->vendors as $vendor) {
			$arr[$vendor->getName()] = $vendor->toArray();
		}
		return $arr;
	}
}
