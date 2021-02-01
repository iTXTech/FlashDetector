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

namespace iTXTech\FlashDetector\WebServer;

use EaseCation\WorkerManEE\RequestHandler;
use iTXTech\FlashDetector\WebServer\Page\DecodePage;
use iTXTech\FlashDetector\WebServer\Page\IndexPage;
use iTXTech\FlashDetector\WebServer\Page\InfoPage;
use iTXTech\FlashDetector\WebServer\Page\SearchIdPage;
use iTXTech\FlashDetector\WebServer\Page\SearchPnPage;
use iTXTech\FlashDetector\WebServer\Page\SummaryPage;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Framework;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\WebServer as WNWS;
use Workerman\Worker;

class WebServer{
	/** @var WebServer */
	private $webServer;

	public function __construct(array $config){
		RequestHandler::registerPage("/", IndexPage::class);
		RequestHandler::registerPage("/decode", DecodePage::class);
		RequestHandler::registerPage("/searchId", SearchIdPage::class);
		RequestHandler::registerPage("/searchPn", SearchPnPage::class);
		RequestHandler::registerPage("/summary", SummaryPage::class);
		RequestHandler::registerPage("/info", InfoPage::class);
		$this->webServer = new WNWS("http://" . $config["address"] . ":" . $config["port"]);
		$this->webServer->onReceive = function(TcpConnection $connection){
			Http::header("Access-Control-Allow-Origin: *");
			Http::header("Access-Control-Allow-Headers: *");
			Http::header("Content-Type: application/json");
			Http::header("X-SimpleFramework: " . Framework::PROG_VERSION);
			Logger::info("Got request " . self::getQuery() . " from " . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"]);
			return RequestHandler::process();
		};
	}

	public function start(){
		Worker::runAll();

		while(true){
			Worker::loop();
			usleep(Framework::getTickInterval());
		}
	}

	public static function getQuery() : string{
		return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}

	public static function getRemote() : string{
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return str_replace(" ", "", explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
        }
        return $_SERVER["HTTP_X_REAL_IP"] ?? $_SERVER["REMOTE_ADDR"];
    }

	public static function getUserAgent() : string{
		return $_SERVER["HTTP_USER_AGENT"] ?? "Undefined";
	}
}
