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
