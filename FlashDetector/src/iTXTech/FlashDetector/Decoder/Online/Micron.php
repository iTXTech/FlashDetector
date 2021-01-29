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

namespace iTXTech\FlashDetector\Decoder\Online;

use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Util;
use simplehtmldom\HtmlDocument;

class Micron{
	private const MICRON_API_ADDR = "https://www.micron.com/support/tools-and-utilities/fbga";

	private static function query(string $param, string $value){
		$response = Curl::newInstance()->setUserAgent(Util::USER_AGENT)
			->setUrl(self::MICRON_API_ADDR)
			->setGet([$param => $value])
			->exec();
		if($response->isSuccessful()){
			$r = [];
			$dom = new HtmlDocument($response->getBody());
			$results = $dom->find("table[id=theResults]", 0);
			if($results !== null){
				foreach($results->children(1)->find("tr") as $node){
					$n = $node->find("td", 0);
					if(($a = $n->find("a", 0)) !== null){
						$info = $a->innertext;
					}else{
						$info = $n->innertext;
					}
					$r[$info] = $node->find("td", 1)->innertext;
				}
				return $r;
			}
		}
		return null;
	}

	public static function fbgaToPartNumber(string $code) : ?array{
		return self::query("fbga", $code);
	}

	public static function partNumberToFbga(string $partNumber) : ?array{
		return self::query("matpart", $partNumber);
	}
}
