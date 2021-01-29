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

class Iddb extends Arrayable{
	/** @var FlashId[] */
	protected $flashIds;

	public function __construct(array $arr = []){
		foreach($arr as $id => $data){
			$this->flashIds[strtoupper($id)] = new FlashId($id, $data);
		}
	}

	public function addFlashId(FlashId $id){
		$this->flashIds[$id->getFlashId()] = $id;
	}

	public function getFlashId(string $id, bool $force = false) : ?FlashId{
		$id = strtoupper($id);
		if($force and !isset($this->flashIds[$id])){
			$this->flashIds[$id] = new FlashId($id);
		}
		return $this->flashIds[$id] ?? null;
	}

	/**
	 * @return FlashId[]|null
	 */
	public function getFlashIds() : array{
		return $this->flashIds;
	}

	public function toArray() : array{
		$arr = [];
		foreach($this->flashIds as $pn){
			$arr[$pn->getFlashId()] = $pn->toArray();
		}
		return $arr;
	}
}
