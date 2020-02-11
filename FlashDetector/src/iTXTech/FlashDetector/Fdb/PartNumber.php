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

	public function getRemark() : string{
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

	public function setDie(int $die, bool $force = false) : PartNumber{
		if($force or $this->d == null){
			$this->d = $die;
		}
		return $this;
	}

	public function setCe(int $ce, bool $force = false) : PartNumber{
		if($force or $this->e == null){
			$this->e = $ce;
		}
		return $this;
	}

	public function setRb(int $rb, bool $force = false) : PartNumber{
		if($force or $this->r == null){
			$this->r = $rb;
		}
		return $this;
	}

	public function setCh(int $ch, bool $force = false) : PartNumber{
		if($force or $this->n == null){
			$this->n = $ch;
		}
		return $this;
	}

	public function setRemark(string $remark, bool $force = false) : PartNumber{
		if($force or ($this->m == null and $remark != "")){
			$this->m = $remark;
		}
		return $this;
	}

	public function setProcessNode(string $processNode, bool $force = false) : PartNumber{
		if($force or $this->l == null){
			$this->l = trim(str_replace("toggle", "", $processNode));
		}
		return $this;
	}

	public function setCellLevel(string $cellLevel, bool $force = false) : PartNumber{
		if($force or $this->c == null){
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

	public function setFlashIds(array $ids) : PartNumber{
		$this->id = $ids;
		return $this;
	}

	public function merge(PartNumber $partNumber, bool $force = false) : PartNumber{
		$this->setCe($partNumber->getCe(), $force);
		$this->setCh($partNumber->getCh(), $force);
		$this->setDie($partNumber->getDie(), $force);
		$this->setRb($partNumber->getRb(), $force);
		$this->setProcessNode($partNumber->getProcessNode(), $force);
		$this->setCellLevel($partNumber->getCellLevel(), $force);
		$this->setRemark($partNumber->getRemark(), $force);
		$this->addController($partNumber->getControllers());
		$this->addFlashId($partNumber->getFlashIds());
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
