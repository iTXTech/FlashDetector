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
use iTXTech\FlashDetector\Property\Classification;

class PartNumber extends Arrayable{
	protected $pn;
	protected $id;//Flash Ids
	protected $l;//process node
	protected $c;//cell level
	protected $t;//supported controllers
	protected $m;//additional info
	protected $d;//die
	protected $e;//ce
	protected $r;//rb
	protected $n;//ch

	public function __construct(string $pn, array $arr = null){
		$this->pn = strtoupper($pn);
		parent::__construct($arr);
	}

	public function getPartNumber() : string{
		return $this->pn;
	}

	public function getFlashIds() : array{
		return $this->id ?? [];
	}

	public function getProcessNode() : string{
		return $this->l ?? "";
	}

	public function getCellLevel() : string{
		return $this->c ?? "";
	}

	public function getControllers() : array{
		return $this->t ?? [];
	}

	public function getComment() : string{
		return $this->m ?? "";
	}

	public function getDie() : int{
		return $this->d ?? Classification::UNKNOWN_PROP;
	}

	public function getCe() : int{
		return $this->e ?? Classification::UNKNOWN_PROP;
	}

	public function getRb() : int{
		return $this->r ?? Classification::UNKNOWN_PROP;
	}

	public function getCh() : int{
		return $this->n ?? Classification::UNKNOWN_PROP;
	}

	public function setComment(string $comment) : PartNumber{
		$this->m = $comment;
		return $this;
	}
}
