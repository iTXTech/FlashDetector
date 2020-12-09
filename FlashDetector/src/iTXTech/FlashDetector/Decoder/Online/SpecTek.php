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

namespace iTXTech\FlashDetector\Decoder\Online;

use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Util;
use simplehtmldom\HtmlDocument;

class SpecTek{
	private const SPECTEK_API_ADDR = "https://www.spectek.com/menus/mark_code.aspx";

	private static function query(string $markCode = "", string $partNumber = ""){
		$postData = [
			"__LASTFOCUS" => "",
			"__VIEWSTATE" => "/wEPDwULLTEzODY5NTg0NzMPZBYCZg9kFgQCAQ9kFgQCBg8WAh4EVGV4dAVZPGxpbmsgdHlwZT0idGV4dC9jc3MiIHJlbD0iU3R5bGVzaGVldCIgaHJlZj0iL2Nzcy9zcGVjdGVrU3R5bGVfbmV3LmNzcz8yMDIwMTEyMDA4NDEyMSIgLz5kAgcPFgIfAAVJPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiIHNyYz0iL2pzL21pc2MuanM/MjAxMzAxMDMxMDI2MzMiPjwvc2NyaXB0PmQCAw9kFgICAQ9kFgICAQ9kFgICAQ8PFgIeB1Zpc2libGVoZGQYAgUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgIFHGN0bDAwJE1haW5DUEgkTWFya0NvZGVCdXR0b24FHmN0bDAwJE1haW5DUEgkUGFydE51bWJlckJ1dHRvbgUeY3RsMDAkTWFpbkNQSCRNYXJrQ29kZUdyaWRWaWV3D2dkXBqJVPp+fJIQFs9n7yPoeWCoKWU=",
			"__VIEWSTATEGENERATOR" => "BBD0826C",
			"__EVENTTARGET" => "",
			"__EVENTARGUMENT" => "",
			'ctl00$MainCPH$MarkCodeTextBox' => $markCode,
			'ctl00$MainCPH$PartNumberTextBox' => $partNumber,
		];
		if($markCode != ""){
			$postData['ctl00$MainCPH$MarkCodeButton.x'] = 1;
			$postData['ctl00$MainCPH$MarkCodeButton.y'] = 1;
		}
		if($partNumber != ""){
			$postData['ctl00$MainCPH$PartNumberButton.x'] = 1;
			$postData['ctl00$MainCPH$PartNumberButton.y'] = 1;
		}
		$response = Curl::newInstance()->setUserAgent(Util::USER_AGENT)
			->setUrl(self::SPECTEK_API_ADDR)
			->setPost($postData)
			->exec();
		if($response->isSuccessful()){
			$r = [];
			$dom = new HtmlDocument($response->getBody());
			$results = $dom->find("table[class=bdrBlackTbl]", 0);
			if($results !== null){
				foreach($results->find("tr") as $node){
					if(count($node->find("td")) == 3){
						$m = trim($node->find("td", 0)->text());
						$pn = trim($node->find("td", 1)->text());
						$pf = trim($node->find("td", 2)->text());
						$r[$m] = [
							"partNumber" => explode(", ", $pn),
							"productFamily" => $pf
						];
					}
				}
				return $r;
			}
		}
		return null;
	}

	public static function markCodeToPartNumber(string $code) : ?array{
		return self::query($code);
	}

	public static function partNumberToMarkCode(string $partNumber) : ?array{
		return self::query("", $partNumber);
	}
}
