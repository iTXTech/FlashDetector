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

namespace iTXTech\FlashDetector\FDBGen;

use iTXTech\FlashDetector\FDBGen\Generator\Generator;
use iTXTech\FlashDetector\FDBGen\Generator\Innostor;
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
		self::registerGenerator(SiliconMotionUFD::class);
		self::registerGenerator(Innostor::class);
	}

	public static function generate(string $version, string $db) : array{
		if(!StringUtil::endsWith($db, DIRECTORY_SEPARATOR)){
			$db .= DIRECTORY_SEPARATOR;
		}
		$fdb = [
			"info" => [
				"name" => "iTXTech FlashDetector Flash Database",
				"website" => "https://github.com/iTXTech/FlashDetector",
				"version" => $version,
				"time" => date("r"),
				"controllers" => []
			]
		];
		$dirs = scandir($db);
		foreach($dirs as $dir){
			if(isset(self::$generators[strtolower($dir)])){
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
		return $fdb;
	}
}
