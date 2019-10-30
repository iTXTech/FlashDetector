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

	public function __construct(string $file){
		$this->file = new Config($file, Config::JSON, [
			"micron" => [],
			"spectek" => []
		]);
		$this->data = $this->file->getAll();
		$this->file->save(0);
	}

	public function save(int $option = 0){
		$this->file->setAll($this->data);
		$this->file->save($option);
	}

	public function update(int $option = 0){
		foreach(self::MICRON_HEADER as $h){
			for($i = self::START_FROM[$h]; $i < 1000; $i++){
				if(!isset($this->data["micron"][$code = self::getCode($h, $i)])){
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
