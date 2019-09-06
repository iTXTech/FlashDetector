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
use iTXTech\SimpleFramework\Util\StringUtil;

class PartNumber extends Arrayable{
	protected $pn;
	protected $id = [];//Flash Ids
	protected $l;//process node
	protected $c;//cell level
	protected $t = [];//supported controllers
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

	public function setDie(int $die) : PartNumber{
		if($this->d == null){
			$this->d = $die;
		}
		return $this;
	}

	public function setCe(int $ce) : PartNumber{
		if($this->e == null){
			$this->e = $ce;
		}
		return $this;
	}

	public function setRb(int $rb) : PartNumber{
		if($this->r == null){
			$this->r = $rb;
		}
		return $this;
	}

	public function setCh(int $ch) : PartNumber{
		if($this->n == null){
			$this->n = $ch;
		}
		return $this;
	}

	public function setComment(string $comment, bool $force = false) : PartNumber{
		if($force or ($this->m == null and $comment != "")){
			$this->m = $comment;
		}
		return $this;
	}

	public function setProcessNode(string $processNode) : PartNumber{
		if($this->l != null){
			$this->l = $processNode;
		}
		return $this;
	}

	public function setCellLevel(string $cellLevel) : PartNumber{
		if($this->c != null){
			$this->c = $cellLevel;
		}
		return $this;
	}

	public function addController($controller) : PartNumber{
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

	public function addFlashId($ids) : PartNumber{
		if(!is_array($ids)){
			$ids = [$ids];
		}
		foreach($ids as $id){
			if(!in_array($id, $this->id)){
				$found = false;
				for($i = 0; $i < count($this->id); $i++){
					if(strlen($this->id[$i]) > strlen($id) and StringUtil::startsWith($this->id[$i], $id)){
						$found = true;
						break;
					}
				}
				if(!$found){
					$this->id[] = $id;
				}
			}
		}
		return $this;
	}

	public function merge(PartNumber $partNumber) : PartNumber{
		$this->setCe($partNumber->getCe());
		$this->setCh($partNumber->getCh());
		$this->setDie($partNumber->getDie());
		$this->setRb($partNumber->getRb());
		$this->setProcessNode($partNumber->getProcessNode());
		$this->setCellLevel($partNumber->getCellLevel());
		$this->addController($partNumber->getControllers());
		$this->addFlashId($partNumber->getFlashIds());
		$this->setComment($partNumber->getComment());
		return $this;
	}

	public function toArray() : array{
		$arr = parent::toArray();
		unset($arr["pn"]);
		foreach($arr as $k => $v){
			if($v == -1 or $v == null){
				unset($arr[$k]);
			}
		}
		return $arr;
	}
}
