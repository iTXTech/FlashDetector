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

namespace iTXTech\FlashDetector\WebServer\Page;

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleSwFw\Http\Page\AbstractPage;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SearchPnPage extends AbstractPage{
	public static function process(Request $request, Response $response, Server $server){
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->searchPn(self::getQuery($request), self::getClientIp($request),
				$request->get["lang"] ?? null, $request->get["pn"] ?? null, $request->get["limit"] ?? 0, $c)){
				break;
			}
		}

		self::sendJsonData($response, $c);
	}
}
