<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2023 iTX Technologies
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

	public function decodeId(string $query, string $remote, string $ua, ?string $lang, ?string $id, array &$c) : bool{
		$c = $id != null ? [
			"result" => true,
			"data" => FlashDetector::decodeFlashId($id)->toArray($lang)
		] : [
			"result" => false,
			"message" => "Missing Flash Id"
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

	public function searchId(string $query, string $remote, string $ua, ?string $lang, ?string $id, int $limit,
							 array &$c) : bool{
		$c = $id != null ? [
			"result" => true,
			"data" => FlashDetector::searchFlashId($id, true, $lang, $limit)
		] : [
			"result" => false,
			"message" => "Missing Flash Id"
		];
		return true;
	}

	public function searchPn(string $query, string $remote, string $ua, ?string $lang, ?string $pn, int $limit,
							 array &$c) : bool{
		$c = $pn != null ? [
			"result" => true,
			"data" => FlashDetector::searchPartNumber($pn, true, $lang, $limit)
		] : [
			"result" => false,
			"message" => "Missing part number"
		];
		return true;
	}

	public function summary(string $query, string $remote, string $ua, ?string $lang, ?string $pn, array &$c): bool {
		$c = $pn != null ? [
			"result" => true,
			"data" => FlashDetector::getSummary($pn, $lang)
		] : [
			"result" => false,
			"message" => "Missing part number"
		];
		return true;
	}

	public function summaryId(string $query, string $remote, string $ua, ?string $lang, ?string $id, array &$c): bool {
		$c = $id != null ? [
			"result" => true,
			"data" => FlashDetector::getIdSummary($id, $lang)
		] : [
			"result" => false,
			"message" => "Missing flash Id"
		];
		return true;
	}
}
