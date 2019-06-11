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

namespace iTXTech\FlashDetector;

use iTXTech\FlashDetector\Decoder\Decoder;
use iTXTech\FlashDetector\Decoder\Intel;
use iTXTech\FlashDetector\Decoder\Micron;
use iTXTech\FlashDetector\Decoder\MicronFbgaCode;
use iTXTech\FlashDetector\Decoder\Samsung;
use iTXTech\FlashDetector\Decoder\SanDisk;
use iTXTech\FlashDetector\Decoder\SanDiskShortCode;
use iTXTech\FlashDetector\Decoder\SKHynix;
use iTXTech\FlashDetector\Decoder\SKHynixLegacy;
use iTXTech\FlashDetector\Decoder\SpecTek;
use iTXTech\FlashDetector\Decoder\Toshiba;
use iTXTech\SimpleFramework\Util\StringUtil;

abstract class FlashDetector{
	/** @var Decoder[] */
	private static $decoders = [];
	private static $fdb = [];
	private static $iddb = [];
	private static $mdb = [];
	private static $lang = [];
	private static $fallbackLang = [];

	public static function getFdb() : array{
		return self::$fdb;
	}

	public static function getIddb() : array{
		return self::$iddb;
	}

	public static function getMdb() : array{
		return self::$mdb;
	}

	public static function getLang() : array{
		return self::$lang;
	}

	public static function init(string $lang = "eng", string $fallbackLang = "eng"){
		self::registerDecoder(Micron::class);
		self::registerDecoder(SKHynix::class);
		self::registerDecoder(SKHynixLegacy::class);
		self::registerDecoder(Toshiba::class);
		self::registerDecoder(Samsung::class);
		self::registerDecoder(Intel::class);
		self::registerDecoder(MicronFbgaCode::class);
		self::registerDecoder(SpecTek::class);
		self::registerDecoder(SanDisk::class);
		self::registerDecoder(SanDiskShortCode::class);
		if(Loader::getInstance() !== null){
			self::$fdb = json_decode(Loader::getInstance()->getResourceAsText("fdb.json"), true);
			self::$iddb = json_decode(Loader::getInstance()->getResourceAsText("iddb.json"), true);
			self::$mdb = json_decode(Loader::getInstance()->getResourceAsText("mdb.json"), true);
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
		$partNumber = strtoupper($partNumber);
		foreach(self::$decoders as $decoder){
			if($decoder::check($partNumber)){
				$info = $decoder::decode($partNumber);
				if($combineFdb){
					self::combineDataFromFdb($info, $decoder);
				}
				return $info;
			}
		}
		return (new FlashInfo($partNumber))->setManufacturer(Constants::UNKNOWN);
	}

	public static function combineDataFromFdb(FlashInfo $info, string $decoder){
		/** @var Decoder $decoder */
		if(($data = $decoder::getFlashInfoFromFdb($info->getPartNumber())) !== null){
			$info->setFlashId($data["id"]);
			$info->setController($data["t"]);
			if($data["l"] !== ""){
				$info->setProcessNode($data["l"]);
			}
			if(isset($data["m"])){
				$info->setComment($data["m"]);
			}
			if($info->getCellLevel() === null and $data["c"] !== ""){
				$info->setCellLevel($data["c"]);
			}
		}
	}

	public static function searchFlashId(string $id, bool $partMatch = false) : ?array{
		$id = strtoupper($id);
		if($partMatch){
			$result = [];
			foreach(self::$iddb as $fid => $partNumber){
				if(StringUtil::startsWith($fid, $id)){
					$result[$fid] = $partNumber;
				}
			}
			return $result;
		}
		return self::$iddb[strtoupper($id)] ?? null;
	}

	public static function searchPartNumber(string $pn, bool $partMatch = false) : ?array{
		$pn = strtoupper($pn);
		$result = [];
		foreach(self::$fdb as $manufacturer => $flashes){
			foreach($flashes as $partNumber => $flash){
				if(($partMatch and StringUtil::contains($partNumber, $pn)) or
					(!$partNumber and $partNumber == $pn)){
					$result[] = $manufacturer . " " . $partNumber;
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

	public static function searchSupportedControllers(string $flashId, bool $partMatch = false) : ?array{
		$ids = self::searchFlashId($flashId, $partMatch);
		$result = [];
		foreach($ids as $k => $v){
			$cons = [];
			foreach($v as $pn){
				list($vendor, $pn) = explode(" ", $pn, 2);
				foreach(self::$fdb[$vendor][$pn]["t"] as $con){
					$cons[$con] = "";
				}
			}
			$cons = array_keys($cons);
			sort($cons);
			$result[$k] = $cons;
		}
		return $result;
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
}
