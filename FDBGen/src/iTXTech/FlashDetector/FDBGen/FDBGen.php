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
use iTXTech\SimpleFramework\Console\Logger;
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
		// Full Flash Database
		self::registerGenerator(SiliconMotionForceFlash::class);
		// Have 6Bytes FlashId
		self::registerGenerator(SiliconMotionUFD::class);
		self::registerGenerator(SiliconMotionSSD::class);
		// May not have complete FlashId
		self::registerGenerator(JMicron::class);
		self::registerGenerator(Maxiotek::class);
		self::registerGenerator(Maxio::class);
		// No flash id
		self::registerGenerator(SandForce::class);
		self::registerGenerator(AlcorMicro::class);
		// Unreliable Part Number
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
						Logger::debug("Merging " . @end(explode("\\", $generator)) . " => " .
							@end(explode(DIRECTORY_SEPARATOR, $f)));
						$generator::merge($fdb, file_get_contents($f), $file);
					}
				}
			}
		}
		if($extra){
			Logger::debug("Merging extra.json");
			Extra::merge($fdb, file_get_contents($db . DIRECTORY_SEPARATOR . "extra.json"));
		}

		Logger::debug("Add Part Numbers to IDDB");
		$iddb = $fdb->getIddb();
		foreach($fdb->getVendors() as $vendor){
			foreach($vendor->getPartNumbers() as $partNumber){
				foreach($partNumber->getFlashIds() as $id){
					$iddb->getFlashId($id, true)->addPartNumber($vendor->getName() . " " . $partNumber->getPartNumber());
				}
			}
		}

		Logger::debug("FDB has been generated.");

		return $fdb->toArray();
	}
}
