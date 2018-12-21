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

namespace iTXTech\FlashDetector\Decoder\Online;

use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Util;
use PeratX\SimpleHtmlDom\SimpleHtmlDom;
use PeratX\SimpleHtmlDom\SimpleHtmlDomNode;

class Micron{
	private const MICRON_API_ADDR = "https://www.micron.com/support/tools-and-utilities/fbga";

	private static function query(string $param, string $value){
		$response = (new Curl())->setUserAgent(Util::USER_AGENT)
			->setUrl(self::MICRON_API_ADDR)
			->setGet([$param => $value])
			->exec();
		if($response->isSuccessfully()){
			$r = [];
			$dom = SimpleHtmlDom::initDomFromString($response->getBody());
			$results = $dom->find("table[id=theResults]", 0);
			if($results !== null){
				foreach($results->children(1)->find("tr") as $node){
					/** @var SimpleHtmlDomNode $node */
					$r[$node->find("td", 0)->innerText()] = $node->find("td", 1)->innerText();
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
