<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2019 iTX Technologies
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

namespace iTXTech\FlashDetector\WebServer;

use EaseCation\WorkerManEE\RequestHandler;
use iTXTech\FlashDetector\WebServer\Page\DecodePage;
use iTXTech\FlashDetector\WebServer\Page\IndexPage;
use iTXTech\FlashDetector\WebServer\Page\InfoPage;
use iTXTech\FlashDetector\WebServer\Page\SearchControllerPage;
use iTXTech\FlashDetector\WebServer\Page\SearchIdPage;
use iTXTech\FlashDetector\WebServer\Page\SearchPnPage;
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
		RequestHandler::registerPage("/searchController", SearchControllerPage::class);
		RequestHandler::registerPage("/info", InfoPage::class);
		$this->webServer = new WNWS("http://" . $config["address"] . ":" . $config["port"]);
		$this->webServer->onReceive = function (TcpConnection $connection){
			Http::header("Access-Control-Allow-Origin: *");
			Http::header("Access-Control-Allow-Headers: *");
			Http::header("Content-Type: application/json");
			Http::header("X-SimpleFramework: " . Framework::PROG_VERSION);
			Logger::info("Got request " . $_SERVER["REQUEST_URI"] . " from " . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"]);
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
}
