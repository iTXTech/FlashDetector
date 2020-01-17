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

namespace iTXTech\FlashDetector\FDBGen;

use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\FlashDetector\FDBGen\Generator\AlcorMicro;
use iTXTech\FlashDetector\FDBGen\Generator\ChipsBank;
use iTXTech\FlashDetector\FDBGen\Generator\Extra;
use iTXTech\FlashDetector\FDBGen\Generator\Generator;
use iTXTech\FlashDetector\FDBGen\Generator\Innostor;
use iTXTech\FlashDetector\FDBGen\Generator\JMicron;
use iTXTech\FlashDetector\FDBGen\Generator\Maxio;
use iTXTech\FlashDetector\FDBGen\Generator\Maxiotek;
use iTXTech\FlashDetector\FDBGen\Generator\SandForce;
use iTXTech\FlashDetector\FDBGen\Generator\SiliconMotionForceFlash;
use iTXTech\FlashDetector\FDBGen\Generator\SiliconMotionSSD;
use iTXTech\FlashDetector\FDBGen\Generator\SiliconMotionUFD;
use iTXTech\SimpleFramework\Util\StringUtil;

abstract class FDBGen{
	/** @var Generator[] */
	private static $generators = [];

	public static function registerGenerator(string $class){
		if(is_a($class, Generator::class, true)){
			/** @var Generator $class */
			self::$generators[$class::getDirName()] = $class;
		}
	}

	public static function init(){
		//Full Flash Database
		self::registerGenerator(SiliconMotionForceFlash::class);
		//have 6Bytes FlashId
		self::registerGenerator(SiliconMotionUFD::class);
		self::registerGenerator(SiliconMotionSSD::class);
		//may not have complete id
		self::registerGenerator(JMicron::class);
		self::registerGenerator(Maxiotek::class);
		self::registerGenerator(Maxio::class);
		//no flash id
		self::registerGenerator(SandForce::class);
		self::registerGenerator(AlcorMicro::class);
		//unreliable Flash PN
		self::registerGenerator(ChipsBank::class);
		self::registerGenerator(Innostor::class);
	}

	public static function generate(string $version, string $db, bool $extra = false) : array{
		if(!StringUtil::endsWith($db, DIRECTORY_SEPARATOR)){
			$db .= DIRECTORY_SEPARATOR;
		}
		$fdb = new Fdb([
			"info" => [
				"name" => "iTXTech FlashDetector Flash Database",
				"website" => "https://github.com/iTXTech/FlashDetector",
				"version" => $version,
				"time" => date("r"),
				"controllers" => []
			],
			"iddb" => []
		]);
		$dirs = array_keys(self::$generators);
		foreach($dirs as $dir){
			if(file_exists($db . $dir)){
				$generator = self::$generators[strtolower($dir)];
				$files = scandir($db . $dir);
				foreach($files as $file){
					if(!in_array($file, [".", ".."])){
						$f = $db . $dir . DIRECTORY_SEPARATOR . $file;
						$generator::merge($fdb, file_get_contents($f), $file);
					}
				}
			}
		}
		if($extra){
			Extra::merge($fdb, file_get_contents($db . DIRECTORY_SEPARATOR . "extra.json"));
		}

		$iddb = $fdb->getIddb();
		foreach($fdb->getVendors() as $vendor){
			foreach($vendor->getPartNumbers() as $partNumber){
				foreach($partNumber->getFlashIds() as $id){
					$iddb->getFlashId($id, true)->addPartNumber($vendor->getName() . " " . $partNumber->getPartNumber());
				}
			}
		}

		return $fdb->toArray();
	}
}
