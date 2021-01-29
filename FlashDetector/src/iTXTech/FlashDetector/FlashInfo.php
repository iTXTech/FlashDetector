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
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;
use iTXTech\FlashDetector\Property\Url;

class FlashInfo extends Arrayable{
	protected $partNumber;
	protected $vendor;//Intel/Samsung
	protected $type;//NAND/iNAND/E2NAND
	protected $density;//256Gb
	protected $deviceWidth;//x8 x16 x4
	protected $processNode;//22nm 19nm 1ynm 1znm
	protected $cellLevel;//SLC MLC TLC QLC
	protected $classification;//CE, Ch, Die, R/nB
	protected $voltage;//3.3V/1.8V
	protected $generation;//1 2 3 4
	protected $interface;//Async/Sync ToggleDDR
	protected $package;//TSOP48 BGA152 LGA52

	protected $extraInfo = [];
	//data from Flash Database
	protected $flashId = [];
	protected $controller = [];
	protected $remark;
	protected $url = [];
	/** @var Url[] */
	protected $urls = [];

	public function __construct(string $partNumber = ""){
		$this->partNumber = strtoupper($partNumber);
	}

	public function getPartNumber() : string{
		return $this->partNumber;
	}

	public function getVendor() : string{
		return $this->vendor;
	}

	public function getCellLevel() : ?string{
		return $this->cellLevel;
	}

	public function getExtraInfo(){
		return $this->extraInfo;
	}

	public function getClassification() : ?Classification{
		return $this->classification;
	}

	public function getProcessNode() : ?string{
		return $this->processNode;
	}

	public function getDensity() : ?int{
		return $this->density;
	}

	public function getPackage() : ?string{
		return $this->package;
	}

	public function getFlashId() : ?array{
		return $this->flashId;
	}

	public function setPartNumber(string $partNumber) : FlashInfo{
		$this->partNumber = $partNumber;
		return $this;
	}

	public function setVendor(string $m) : FlashInfo{
		$this->vendor = $m;
		return $this;
	}

	public function setType(string $type) : FlashInfo{
		$this->type = $type;
		return $this;
	}

	public function setDensity(int $density) : FlashInfo{
		$this->density = $density;
		return $this;
	}

	public function setDeviceWidth(int $deviceWidth) : FlashInfo{
		$this->deviceWidth = $deviceWidth;
		return $this;
	}

	public function setClassification(Classification $classification) : FlashInfo{
		$this->classification = $classification;
		return $this;
	}

	public function setCellLevel($cellLevel) : FlashInfo{
		$this->cellLevel = is_string($cellLevel) ? $cellLevel : Decoder::CELL_LEVEL[$cellLevel];
		return $this;
	}

	public function setGeneration(string $generation) : FlashInfo{
		$this->generation = $generation;
		return $this;
	}

	public function setInterface(FlashInterface $interface) : FlashInfo{
		$this->interface = $interface;
		return $this;
	}

	public function setPackage(string $package) : FlashInfo{
		$this->package = $package;
		return $this;
	}

	public function setVoltage(string $voltage) : FlashInfo{
		$this->voltage = $voltage;
		return $this;
	}

	public function setExtraInfo(array $extraInfo) : FlashInfo{
		$this->extraInfo = $extraInfo;
		return $this;
	}

	public function setProcessNode(string $processNode) : FlashInfo{
		$this->processNode = $processNode;
		return $this;
	}

	public function setFlashId(array $flashId) : FlashInfo{
		$this->flashId = $flashId;
		return $this;
	}

	public function setController(array $controller) : FlashInfo{
		$this->controller = $controller;
		return $this;
	}

	public function setRemark($remark) : FlashInfo{
		$this->remark = $remark;
		return $this;
	}

	public function addUrl(Url $url) : FlashInfo{
		if(isset($this->url[$url->desc])){
			for($i = 0; $i < count($this->urls); $i++){
				if($this->urls[$i]->desc == $url->desc){
					unset($this->urls[$i]);
				}
			}
		}
		$this->url[$url->desc] = $url->url;
		$this->urls[] = $url;
		return $this;
	}

	public function toArray(?string $lang = "chs", bool $raw = false) : array{
		$this->urls = array_values($this->urls);
		$info = parent::toArray();

		if(!$raw){
			$interface = $info["interface"];
			$density = $info["density"];
			if($density !== null and $density > 0){
				$info["density"] = FlashDetector::getHumanReadableDensity($info["density"]);
			}else{
				$info["density"] = Constants::UNKNOWN;
			}
			$deviceWidth = $info["deviceWidth"];
			if($deviceWidth === -1){
				$info["deviceWidth"] = Constants::UNKNOWN;
			}elseif($deviceWidth !== null){
				$info["deviceWidth"] = "x" . $deviceWidth;
			}

			foreach($info as $k => $v){
				if($v === null){
					$info[$k] = Constants::UNKNOWN;
				}
			}

			$info = FlashDetector::translate($info, $lang);
			$info["interface"] = $interface;//hack!
		}
		$info["rawVendor"] = $this->vendor;
		return $info;
	}

	public function __toString(){
		return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
}
