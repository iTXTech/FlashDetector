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

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;

class PeachPieHelper{
	public static function load() : void{
		require_once "sf/sfloader.php";

		Initializer::initTerminal(true);
		Logger::$logLevel = 4;

		try{
			$moduleManager = new ModuleManager(Initializer::getClassLoader(), __DIR__ . DIRECTORY_SEPARATOR, "");
			//ModuleManager::loadModuleDirectly loads the specified module directly from the folder without check
			//No JSON file should be provided in PeachPie Env
			$moduleManager->readModule("FlashDetector");
		}catch(\Throwable $e){
			Logger::logException($e);
		}
	}

	public static function decode(string $query, string $remote, string $ua, ?string $lang, ?string $pn) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->decode($query, $remote, $ua, $lang, $pn, $c)){
				break;
			}
		}

		return json_encode($c);
	}

	public static function index(string $query, string $remote, string $ua) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->index($query, $remote, $ua, "FDWebServer", $c)){
				break;
			}
		}

		return json_encode($c);
	}

	public static function info(string $query, string $remote, string $ua) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->info($query, $remote, $ua, $c)){
				break;
			}
		}

		return json_encode($c);
	}

	public static function searchId(string $query, string $remote, string $ua, ?string $lang, ?string $id) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->searchId($query, $remote, $ua, $lang, $id, $c)){
				break;
			}
		}

		return json_encode($c);
	}

	public static function searchPn(string $query, string $remote, string $ua, ?string $lang, ?string $pn, int $limit = 0) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->searchPn($query, $remote, $ua, $lang, $pn, $limit, $c)){
				break;
			}
		}

		return json_encode($c);
	}

	public static function summary(string $query, string $remote, string $ua, ?string $lang, ?string $pn) : string{
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->summary($query, $remote, $ua, $lang, $pn, $c)){
				break;
			}
		}

		return json_encode($c);
	}
}
