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

namespace iTXTech\FlashDetector\WebServer\Page;

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleSwFw\Http\Page\AbstractPage;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class InfoPage extends AbstractPage{
	public static function process(Request $request, Response $response, Server $server){
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->info(self::getQuery($request), self::getClientIp($request), self::getUserAgent($request), $c)){
				break;
			}
		}

		self::sendJsonData($response, $c);
	}
}
