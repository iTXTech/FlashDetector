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
use iTXTech\FlashDetector\Decoder\Yangtze;
use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\FlashDetector\Processor\Processor;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\SimpleFramework\Util\StringUtil;

abstract class FlashDetector{
	public const LANGUAGES = ["chs", "eng"];

	/** @var Decoder[] */
	private static $decoders = [];
	/** @var Fdb */
	private static $fdb;
	private static $mdb = [];
	private static $lang = [];
	private static $fallbackLang = "chs";
	private static $info;
	/** @var Processor[] */
	private static $processors = [];

	public static function registerProcessor(Processor $processor){
		self::$processors[] = $processor;
	}

	/**
	 * @return Processor[]
	 */
	public static function getProcessors() : array{
		return self::$processors;
	}

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

	public static function setFallbackLang(string $fallbackLang){
		self::$fallbackLang = $fallbackLang;
	}

	public static function initialize(){
		if(Loader::getInstance() !== null){
			$fdb = json_decode(Loader::getInstance()->getResourceAsText("fdb.json"), true);
			self::$fdb = new Fdb($fdb);
			self::$mdb = json_decode(Loader::getInstance()->getResourceAsText("mdb.json"), true);
			foreach(self::LANGUAGES as $l){
				self::$lang[$l] = json_decode(Loader::getInstance()->getResourceAsText("lang/$l.json"), true);
			}
		}
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
		self::registerDecoder(Yangtze::class);
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
				break;
			}
		}
		if(!isset($info)){
			$info = (new FlashInfo($partNumber))->setVendor(Constants::UNKNOWN);
		}
		foreach(self::$processors as $processor){
			$processor->flashInfo($info);
		}
		return $info;
	}

	public static function getSummary(string $partNumber, ?string $lang) : string{
		$info = self::detect($partNumber, true)->toArray($lang);
		$base = self::translateString("summary", $lang);
		$unknown = self::translateString(Constants::UNKNOWN, $lang);
		if($info["interface"] == null){
			$sync = $unknown;
			$async = $unknown;
		}elseif(isset($info["interface"]["toggle"])){
			$async = self::translate(true, $lang);
			$sync = self::translate($info["interface"]["toggle"], $lang);
		}else{
			$async = self::translate($info["interface"]["async"], $lang);
			$sync = self::translate($info["interface"]["sync"], $lang);
		}

		$i = "";
		if(is_array($info["extraInfo"])){
			foreach($info["extraInfo"] as $k => $v){
				$i .= $k . ": " . $v . ", ";
			}
			$i = substr($i, 0, strlen($i) - 2);
		}

		$trans = [$info["partNumber"], $info["vendor"], $info["type"], $info["density"], $info["deviceWidth"],
			$info["cellLevel"], $info["processNode"], $info["generation"], $sync, $async,
			$info["classification"]["ce"] ?? $unknown, $info["classification"]["ch"] ?? $unknown,
			$info["classification"]["die"] ?? $unknown, $info["classification"]["rb"] ?? $unknown,
			$info["voltage"], $info["package"], @implode(", ", $info["controller"]), $info["remark"],
			$i, @implode(", ", $info["flashId"])];
		for($i = 0; $i < count($trans); $i++){
			$base = str_replace("{" . $i . "}", $trans[$i], $base);
		}
		return $base;
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
			$info->setRemark($data->getRemark());
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

	public static function searchFlashId(string $id, bool $partMatch, ?string $lang) : ?array{
		$id = strtoupper($id);
		if($partMatch){
			$result = [];
			foreach(self::$fdb->getIddb()->getFlashIds() as $flashId){
				if(StringUtil::startsWith($flashId->getFlashId(), $id)){
					$pageSize = $flashId->getPageSize();
					if($pageSize != -1){
						$pageSize = $pageSize < 1 ? ($pageSize * 1024) . "B" : $pageSize . "K";
					}
					$data = [
						"partNumbers" => $flashId->getPartNumbers(),
						"pageSize" => $pageSize,
						"pagesPerBlock" => $flashId->getPagesPerBlock(),
						"blocks" => $flashId->getBlocks(),
						"controllers" => $flashId->getControllers()
					];
					$result[$flashId->getFlashId()] = self::translateArray($data, false, $lang);
				}
			}
			return $result;
		}
		return self::$fdb->getIddb()->getFlashIds()[$id] ?? null;
	}

	public static function searchPartNumber(string $pn, bool $partMatch, ?string $lang, int $limit = 0) : ?array{
		$pn = strtoupper($pn);
		$result = [];
		foreach(self::$fdb->getVendors() as $vendor){
			foreach($vendor->getPartNumbers() as $partNumber){
				if($limit > 0 and count($result) >= $limit){
					break 2;
				}elseif(($partMatch and StringUtil::contains($partNumber->getPartNumber(), $pn)) or
					(!$partNumber and $partNumber->getPartNumber() == $pn)){
					$result[] = self::translateString($vendor->getName(), $lang) . " " . $partNumber->getPartNumber();
				}
			}
		}

		foreach(self::$mdb["micron"] as $c => $p){
			if($limit > 0 and count($result) >= $limit){
				break;
			}elseif(StringUtil::contains($p, $pn)){
				$result[] = self::translateString(Micron::getName(), $lang) . " " . $c . " " . $p;
			}
		}

		foreach(self::$mdb["spectek"] as $c => $p){
			if($limit > 0 and count($result) >= $limit){
				break;
			}else{
				foreach($p as $specPn){
					if(StringUtil::contains($specPn, $pn)){
						$result[] = self::translateString(SpecTek::getName(), $lang) . " " . $c . " " . $specPn;
					}
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
	 * @param        $var
	 * @param string $lang
	 *
	 * @return array|string|bool|null
	 */
	public static function translate($var, ?string $lang = "chs"){
		if(is_bool($var)){
			return self::translateString($var ? "true" : "false", $lang);
		}elseif(is_array($var)){
			return self::translateArray($var, true, $lang);
		}elseif(is_string($var)){
			return self::translateString($var, $lang);
		}elseif($var == -1){
			return self::translateString(Constants::UNKNOWN, $lang);
		}
		return $var;
	}

	public static function translateArray(array $arr, bool $translateKey, ?string $lang = "chs") : array{
		$a = [];
		foreach($arr as $k => $v){
			$a[$translateKey ? self::translateString($k, $lang) : $k] = self::translate($v, $lang);
		}
		return $a;
	}

	public static function translateString(string $key, ?string $lang = "chs") : string{
		if($lang == null){
			$lang = self::$fallbackLang;
		}
		if(isset(self::$lang[$lang][$key])){
			return self::$lang[$lang][$key];
		}
		return self::$lang[self::$fallbackLang][$key] ?? $key;
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
