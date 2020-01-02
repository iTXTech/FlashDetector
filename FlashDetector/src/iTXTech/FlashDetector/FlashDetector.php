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

namespace iTXTech\FlashDetector;

use iTXTech\FlashDetector\Decoder\Decoder;
use iTXTech\FlashDetector\Decoder\Intel;
use iTXTech\FlashDetector\Decoder\Kioxia;
use iTXTech\FlashDetector\Decoder\Micron;
use iTXTech\FlashDetector\Decoder\MicronFbgaCode;
use iTXTech\FlashDetector\Decoder\Samsung;
use iTXTech\FlashDetector\Decoder\SKHynix;
use iTXTech\FlashDetector\Decoder\SKHynix3D;
use iTXTech\FlashDetector\Decoder\SKHynixLegacy;
use iTXTech\FlashDetector\Decoder\SpecTek;
use iTXTech\FlashDetector\Decoder\WesternDigital;
use iTXTech\FlashDetector\Decoder\WesternDigitalShortCode;
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\SimpleFramework\Util\StringUtil;

abstract class FlashDetector{
	/** @var Decoder[] */
	private static $decoders = [];
	/** @var Fdb */
	private static $fdb;
	private static $mdb = [];
	private static $lang = [];
	private static $fallbackLang = [];
	private static $info;

	public static function getFdb() : Fdb{
		return self::$fdb;
	}

	public static function getVersion() : int{
		return self::$fdb->getInfo()->getVersion();
	}

	public static function getInfo() : array{
		if(self::$info == null){
			$cnt = 0;
			foreach(self::$fdb->getVendors() as $vendor){
				$cnt += count($vendor->getPartNumbers());
			}
			$c = 0;
			foreach(self::$mdb as $v){
				$c += count($v);
			}
			self::$info = [
				"fdb" => self::$fdb->getInfo()->toArray(),
				"flash_cnt" => $cnt,
				"id_cnt" => count(self::$fdb->getIddb()->getFlashIds()),
				"mdb_cnt" => $c
			];
		}
		return self::$info;
	}

	public static function getMdb() : array{
		return self::$mdb;
	}

	public static function getLang() : array{
		return self::$lang;
	}

	public static function loadDatabase(){
		if(Loader::getInstance() !== null){
			$fdb = json_decode(Loader::getInstance()->getResourceAsText("fdb.json"), true);
			self::$fdb = new Fdb($fdb);
			self::$mdb = json_decode(Loader::getInstance()->getResourceAsText("mdb.json"), true);
		}
	}

	public static function init(string $lang = "eng", string $fallbackLang = "eng"){
		self::registerDecoder(Micron::class);
		self::registerDecoder(SKHynix3D::class);
		self::registerDecoder(SKHynix::class);
		self::registerDecoder(SKHynixLegacy::class);
		self::registerDecoder(Kioxia::class);
		self::registerDecoder(Samsung::class);
		self::registerDecoder(Intel::class);
		self::registerDecoder(MicronFbgaCode::class);
		self::registerDecoder(SpecTek::class);
		self::registerDecoder(WesternDigital::class);
		self::registerDecoder(WesternDigitalShortCode::class);
		if(Loader::getInstance() !== null){
			self::$lang = json_decode(Loader::getInstance()->getResourceAsText("lang/$lang.json"), true);
			self::$fallbackLang = json_decode(Loader::getInstance()
				->getResourceAsText("lang/$fallbackLang.json"), true);
		}
	}

	public static function registerDecoder(string $decoder) : bool{
		if(is_a($decoder, Decoder::class, true)){
			/** @var $decoder Decoder */
			self::$decoders[] = $decoder;
			return true;
		}
		return false;
	}

	public static function detect(string $partNumber, bool $combineFdb = true) : FlashInfo{
		$partNumber = str_replace([" ", ",", "&", ".", "|"], "", strtoupper($partNumber));
		foreach(self::$decoders as $decoder){
			if($decoder::check($partNumber)){
				$info = $decoder::decode($partNumber);
				if($combineFdb){
					self::combineDataFromFdb($info, $decoder);
				}
				return $info;
			}
		}
		return (new FlashInfo($partNumber))->setVendor(Constants::UNKNOWN);
	}

	public static function getVendor(string $pn) : string{
		$pn = str_replace([" ", ",", "&", ".", "|"], "", strtoupper($pn));
		foreach(self::$decoders as $decoder){
			if($decoder::check($pn)){
				return $decoder::getName();
			}
		}
		return Constants::UNKNOWN;
	}

	public static function combineDataFromFdb(FlashInfo $info, string $decoder){
		/** @var Decoder $decoder */
		if(($data = $decoder::getFlashInfoFromFdb($info)) !== null){
			$info->setFlashId($data->getFlashIds());
			$info->setController($data->getControllers());
			if($data->getProcessNode() !== "" and ($info->getProcessNode() == Constants::UNKNOWN or
					$info->getProcessNode() == null)){
				$info->setProcessNode($data->getProcessNode());
			}
			$info->setComment($data->getComment());
			if($info->getCellLevel() === null and $data->getCellLevel() !== ""){
				$info->setCellLevel($data->getCellLevel());
			}

			$c = $info->getClassification() ?? new Classification();
			if($data->getDie() != Classification::UNKNOWN_PROP){
				$c->setDie($data->getDie());
			}
			if($data->getCe() != Classification::UNKNOWN_PROP){
				$c->setCe($data->getCe());
			}
			if($data->getRb() != Classification::UNKNOWN_PROP){
				$c->setRb($data->getRb());
			}
			if($data->getCh() != Classification::UNKNOWN_PROP){
				$c->setCh($data->getCh());
			}
			$info->setClassification($c);
		}
	}

	public static function searchFlashId(string $id, bool $partMatch = false) : ?array{
		$id = strtoupper($id);
		if($partMatch){
			$result = [];
			foreach(self::$fdb->getIddb()->getFlashIds() as $flashId){
				if(StringUtil::startsWith($flashId->getFlashId(), $id)){
					$result[$flashId->getFlashId()] = [
						"partNumbers" => $flashId->getPartNumbers(),
						"pageSize" => $flashId->getPageSize(),
						"pagesPerBlock" => $flashId->getPagesPerBlock(),
						"blocks" => $flashId->getBlocks(),
						"controllers" => $flashId->getControllers()
					];
				}
			}
			return $result;
		}
		return self::$fdb->getIddb()->getFlashIds()[$id] ?? null;
	}

	public static function searchPartNumber(string $pn, bool $partMatch = false) : ?array{
		$pn = strtoupper($pn);
		$result = [];
		foreach(self::$fdb->getVendors() as $vendor){
			foreach($vendor->getPartNumbers() as $partNumber){
				if(($partMatch and StringUtil::contains($partNumber->getPartNumber(), $pn)) or
					(!$partNumber and $partNumber->getPartNumber() == $pn)){
					$result[] = $vendor->getName() . " " . $partNumber->getPartNumber();
				}
			}
		}
		if(Micron::check($pn)){
			foreach(self::$mdb["micron"] as $c => $p){
				if(StringUtil::startsWith($p, $pn)){
					$result[] = Micron::getName() . " " . $c . " " . $p;
				}
			}
		}
		return $result;
	}

	public static function searchMicronFbgaCode(string $code) : array{
		$code = strtoupper($code);
		foreach(self::$mdb["micron"] as $c => $pn){
			if($code == $c){
				return [$pn];
			}
		}
		foreach(self::$mdb["spectek"] as $c => $pn){
			if($code == $c){
				return $pn;
			}
		}
		return [];
	}

	/**
	 * @param $var
	 *
	 * @return array|string|bool|null
	 */
	public static function translate($var){
		if(is_bool($var)){
			return self::translateString($var ? "true" : "false");
		}elseif(is_array($var)){
			return self::translateArray($var);
		}elseif(is_string($var)){
			return self::translateString($var);
		}
		return $var;
	}

	public static function translateArray(array $arr) : array{
		$a = [];
		foreach($arr as $k => $v){
			$a[self::translateString($k)] = self::translate($v);
		}
		return $a;
	}

	public static function translateString(string $key) : string{
		if(isset(self::$lang[$key])){
			return self::$lang[$key];
		}
		if(isset(self::$fallbackLang[$key])){
			return self::$fallbackLang[$key];
		}
		return $key;
	}

	public static function getHumanReadableDensity(int $density, bool $useByte = false){
		if($useByte){
			$density /= 8;
			$unit = ["MB", "GB", "TB"];
		}else{
			$unit = ["Mb", "Gb", "Tb"];
		}
		$i = 0;
		while($density >= 1024 and isset($unit[$i + 1])){
			$density /= 1024;
			$i++;
		}
		return $density . $unit[$i];
	}
}
