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

use iTXTech\FlashDetector\Decoder\Online\Micron;
use iTXTech\FlashDetector\Decoder\Online\SpecTek;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Util\Config;

class MicronDatabase{
	public const MICRON_HEADER = ["NW", "NX", "NQ"];
	public const SPECTEK_HEADER = ["PF", "PFA", "PFB", "PFC", "PFD", "PFE", "PFF", "PFG", "PFH"];

	public const START_FROM = [
		"NW" => 101,
		"NQ" => 101,
		"NX" => 101,
	];

	/** @var Config */
	private $file;
	private $data;

	public function __construct(string $file, int $option = JSON_PRETTY_PRINT){
		$this->file = new Config($file, Config::JSON, [
			"micron" => [],
			"spectek" => []
		]);
		$this->data = $this->file->getAll();
		$this->file->save($option);
	}

	public function save(int $option = JSON_PRETTY_PRINT){
		$this->file->setAll($this->data);
		$this->file->save($option);
	}

	public function update(int $option = JSON_PRETTY_PRINT){
		foreach(self::MICRON_HEADER as $h){
			for($i = self::START_FROM[$h]; $i < 1000; $i++){
				if(!isset($this->data["micron"][$code = self::getCode($h, $i)])){
					Logger::debug("Loading data for " . $code);
					if(is_array($res = Micron::fbgaToPartNumber($code))){
						$pn = array_keys($res)[0];
						Logger::info("$code => $pn");
						$this->data["micron"][$code] = $pn;
						$this->save($option);
					}
				}
			}
		}

		foreach(self::SPECTEK_HEADER as $h){
			$end = "1" . str_repeat("0", 5 - strlen($h));
			for($i = 1; $i < (int) $end; $i++){
				if(!isset($this->data["spectek"][$code = self::getCode($h, $i)])){
					Logger::debug("Loading data for " . $code);
					if(is_array($res = SpecTek::markCodeToPartNumber($code)) and isset($res[$code]["partNumber"])){
						$pn = $res[$code]["partNumber"];
						Logger::info("$code => " . json_encode($pn));
						$this->data["spectek"][$code] = $pn;
						$this->save($option);
					}
				}
			}
		}
	}

	private static function getCode(string $h, int $n){
		return $h . str_repeat("0", 5 - strlen($h) - strlen($n)) . $n;
	}
}
