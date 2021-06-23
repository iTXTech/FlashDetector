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

class FlashIdInfo extends Arrayable{
	protected $id;
	protected $vendor;
	protected $density;
	protected $die;
	protected $plane;
	protected $pageSize;
	protected $blockSize;
	protected $processNode;
	protected $cellLevel;
	protected $toggle;

	public function __construct(int $id = 0x0){
		$this->id = strtoupper(dechex($id));
	}

	public function getPlane() : ?int{
		return $this->plane;
	}

	public function getDie() : ?int{
		return $this->die;
	}

	public function setToggle(bool $toggle) : FlashIdInfo{
		$this->toggle = $toggle;
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

	public function setBlockSize(int $blockSize) : FlashIdInfo{
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
}
