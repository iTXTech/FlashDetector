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
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class DecodePage extends AbstractPage{
	public static function process(Request $request, Response $response, Server $server){
		if(!isset($request->get["pn"])){
			self::sendJsonData($response, [
				"result" => false,
				"message" => "Missing part number"
			]);
		}else{
			self::sendJsonData($response, [
				"result" => true,
				"data" => FlashDetector::detect($request->get["pn"])
					->toArray(!(($request->get["trans"] ?? 0) == 1))
			]);
		}
	}
}
