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
use iTXTech\FlashDetector\Property\Classification;

class FlashId extends Arrayable{
	protected $id;
	protected $s;//page size
	protected $p;//pages
	protected $b;//blocks
	protected $t = [];//controllers
	protected $n = [];//part numbers

	public function __construct(string $id, array $arr = null){
		$this->id = strtoupper($id);
		parent::__construct($arr);
	}

	public function addPartNumber(string $pn) : FlashId{
		if(!in_array($pn, $this->n)){
			$this->n[] = $pn;
		}
		return $this;
	}

	public function getPageSize() : float{
		return $this->s ?? Classification::UNKNOWN_PROP;
	}

	public function getPagesPerBlock() : int{
		return $this->p ?? Classification::UNKNOWN_PROP;
	}

	public function getBlocks() : int{
		return $this->b ?? Classification::UNKNOWN_PROP;
	}

	public function getControllers() : array{
		return $this->t ?? [];
	}

	public function addController($controller) : FlashId{
		if(!is_array($controller)){
			$controller = [$controller];
		}
		foreach($controller as $con){
			if(!in_array($con, $this->t)){
				$this->t[] = $con;
			}
		}
		return $this;
	}

	public function setBlocks(int $blocks) : FlashId{
		if($blocks > 0){
			$this->b = $blocks;
		}
		return $this;
	}

	public function setPageSize(float $size) : FlashId{
		if($size > 0){
			$this->s = $size;
		}
		return $this;
	}

	public function setPagesPerBlock(int $pages) : FlashId{
		if($pages > 0){
			$this->p = $pages;
		}
		return $this;
	}

	public function getFlashId() : string{
		return $this->id;
	}

	public function getPartNumbers() : array{
		return $this->n ?? [];
	}

	public function toArray() : array{
		$arr = parent::toArray();
		unset($arr["id"]);
		foreach($arr as $k => $v){
			if($v == -1 or $v == null){
				unset($arr[$k]);
			}
		}
		return $arr;
	}
}
