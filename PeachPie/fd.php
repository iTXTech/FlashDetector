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
