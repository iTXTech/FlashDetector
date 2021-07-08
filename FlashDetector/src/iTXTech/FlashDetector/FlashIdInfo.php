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

namespace iTXTech\FlashDetector;

use iTXTech\FlashDetector\Decoder\Decoder;

class FlashIdInfo extends Arrayable{
	public $id;
	public $vendor;
	public $density;
	public $die;
	public $plane;
	public $pageSize;
	public $blockSize;
	public $processNode;
	public $cellLevel;
	public $voltage;
	public $ext = [];
	public $controllers = [];
	public $partNumbers = [];

	public function __construct(int $id = 0x0){
		$this->id = strtoupper(dechex($id));
	}

	public function setPartNumbers(array $partNumbers) : FlashIdInfo{
		$this->partNumbers = $partNumbers;
		return $this;
	}

	public function setControllers(array $controllers) : FlashIdInfo{
		$this->controllers = $controllers;
		return $this;
	}

	public function setExt(array $ext) : FlashIdInfo{
		$this->ext = $ext;
		return $this;
	}

	public function setVoltage(string $v) : FlashIdInfo{
		$this->voltage = $v;
		return $this;
	}

	public function setId(string $id) : FlashIdInfo{
		$this->id = $id;
		return $this;
	}

	public function setCellLevel(int $cellLevel) : FlashIdInfo{
		$this->cellLevel = $cellLevel;
		return $this;
	}

	public function setPlane(int $plane) : FlashIdInfo{
		$this->plane = $plane;
		return $this;
	}

	public function setPageSize(int $pageSize) : FlashIdInfo{
		$this->pageSize = $pageSize;
		return $this;
	}

	public function setBlockSize(?int $blockSize) : FlashIdInfo{
		$this->blockSize = $blockSize;
		return $this;
	}

	public function setVendor(string $vendor) : FlashIdInfo{
		$this->vendor = $vendor;
		return $this;
	}

	public function setDensity(int $density) : FlashIdInfo{
		$this->density = $density;
		return $this;
	}

	public function setDie(int $die) : FlashIdInfo{
		$this->die = $die;
		return $this;
	}

	public function setProcessNode(string $processNode) : FlashIdInfo{
		$this->processNode = $processNode;
		return $this;
	}

	public function toArray(?string $lang = "chs", bool $raw = false) : array{
		$info = parent::toArray();
		if($raw){
			return $info;
		}
		foreach($info as $k => $v){
			if($v === null){
				$info[$k] = Constants::UNKNOWN;
			}
		}
		$info = FlashDetector::translateArray($info, false, $lang);
		if(isset($info["partNumbers"])){
			$pns = $info["partNumbers"];
			$info["partNumbers"] = [];
			foreach($pns as $pn){
				$p = explode(" ", $pn);
				$info["partNumbers"][] = FlashDetector::translateString($p[0], $lang) . " " . $p[1];
			}
		}
		if(isset($info["cellLevel"])){
			$info["cellLevel"] = Decoder::CELL_LEVEL[$info["cellLevel"]] ?? $info["cellLevel"];
		}
//		$info["pageSize"] .= " KB";
//		$info["blockSize"] .= " KB";
		$info["rawVendor"] = $this->vendor;
		return $info;
	}
}
