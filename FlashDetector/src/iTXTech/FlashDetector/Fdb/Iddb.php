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
	 * @return FlashId[]
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
