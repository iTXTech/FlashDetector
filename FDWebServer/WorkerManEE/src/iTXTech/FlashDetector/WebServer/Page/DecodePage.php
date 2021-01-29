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

use EaseCation\WorkerManEE\Page\AbstractPage;
use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\WebServer\WebServer;

class DecodePage extends AbstractPage{
	public static function onRequest(){
		$c = [];

		foreach(FlashDetector::getProcessors() as $processor){
			if(!$processor->decode(WebServer::getQuery(), WebServer::getRemote(), WebServer::getUserAgent(),
				$_GET["lang"] ?? null, $_GET["pn"] ?? null, $c)){
				break;
			}
		}

		return json_encode($c);
	}
}
