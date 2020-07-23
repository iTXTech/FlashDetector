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

namespace iTXTech\FlashDetector\Processor;

use iTXTech\FlashDetector\FlashDetector;

class DefaultProcessor extends Processor{
	public function index(string $query, string $remote, string $ua, string $name, array &$c) : bool{
		$c = [
			"result" => true,
			"time" => time(),
			"server" => $name
		];
		return true;
	}

	public function decode(string $query, string $remote, string $ua, ?string $lang, ?string $pn, array &$c) : bool{
		$c = $pn != null ? [
			"result" => true,
			"data" => FlashDetector::detect($pn)->toArray($lang)
		] : [
			"result" => false,
			"message" => "Missing part number"
		];
		return true;
	}

	public function info(string $query, string $remote, string $ua, array &$c) : bool{
		$c = [
			"result" => true,
			"ver" => FlashDetector::getVersion(),
			"info" => FlashDetector::getInfo()
		];
		return true;
	}

	public function searchId(string $query, string $remote, string $ua, ?string $lang, ?string $id, array &$c) : bool{
		$c = $id != null ? [
			"result" => true,
			"data" => FlashDetector::searchFlashId($id, true, $lang)
		] : [
			"result" => false,
			"message" => "Missing Flash Id"
		];
		return true;
	}

	public function searchPn(string $query, string $remote, string $ua, ?string $lang, ?string $pn, int $limit, array &$c) : bool{
		$c = $pn != null ? [
			"result" => true,
			"data" => FlashDetector::searchPartNumber($pn, true, $lang, $limit)
		] : [
			"result" => false,
			"message" => "Missing part number"
		];
		return true;
	}

	public function summary(string $query, string $remote, string $ua, ?string $lang, ?string $pn, array &$c) : bool{
		$c = $pn != null ? [
			"result" => true,
			"data" => FlashDetector::getSummary($pn, $lang)
		] : [
			"result" => false,
			"message" => "Missing part number"
		];
		return true;
	}
}

