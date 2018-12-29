<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

namespace iTXTech\FlashDetector;

use iTXTech\FlashDetector\Decoder\Decoder;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\FlashDetector\Property\FlashInterface;

class FlashInfo{
	private $partNumber;
	private $manufacturer;//Intel/Samsung
	private $type;//NAND/iNAND/E2NAND
	private $density;//256Gb
	private $deviceWidth;//x8 x16 x4
	private $processNode;//22nm 19nm 1ynm 1znm
	private $cellLevel;//SLC MLC TLC QLC
	private $classification;//CE, Ch, Die, R/nB
	private $voltage;//3.3V/1.8V
	private $generation;//1 2 3 4
	private $interface;//Async/Sync ToggleDDR
	private $package;//TSOP48 BGA152 LGA52

	private $extraInfo;
	//data from Flash Database
	private $flashId;
	private $controller;
	private $comment;

	public function __construct(string $partNumber = ""){
		$this->partNumber = strtoupper($partNumber);
	}

	public function getPartNumber() : string{
		return $this->partNumber;
	}

	public function getManufacturer() : string{
		return $this->manufacturer;
	}

	public function getCellLevel() : ?string{
		return $this->cellLevel;
	}

	public function setManufacturer(string $m) : FlashInfo{
		$this->manufacturer = $m;
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

	public function setComment($comment) : FlashInfo{
		$this->comment = $comment;
		return $this;
	}

	public function toArray(bool $raw = true) : array{
		$reflectionClass = new \ReflectionClass($this);
		$properties = $reflectionClass->getProperties();
		$info = [];
		foreach($properties as $property){
			if(is_object($this->{$property->getName()})){
				/** @var Arrayable $prop */
				$prop = $this->{$property->getName()};
				$info[$property->getName()] = $prop->toArray();
			}else{
				$info[$property->getName()] = $this->{$property->getName()};
			}
		}

		if(!$raw){
			$density = $info["density"];
			if($density !== null){
				$unit = ["Mb", "Gb", "Tb"];
				$i = 0;
				while($density >= 1024 and isset($unit[$i + 1])){
					$density /= 1024;
					$i++;
				}
				$info["density"] = $density . " " . $unit[$i];
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

			$info = FlashDetector::translate($info);
		}
		return $info;
	}

	public function __toString(){
		return json_encode($this->toArray(false), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
}
