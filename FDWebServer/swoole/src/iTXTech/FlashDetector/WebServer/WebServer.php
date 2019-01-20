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

use iTXTech\FlashDetector\WebServer\Page\DecodePage;
use iTXTech\FlashDetector\WebServer\Page\IndexPage;
use iTXTech\FlashDetector\WebServer\Page\SearchControllerPage;
use iTXTech\FlashDetector\WebServer\Page\SearchIdPage;
use iTXTech\FlashDetector\WebServer\Page\SearchPnPage;
use iTXTech\SimpleSwFw\Http\Server;

class WebServer{
	/** @var Server */
	private $server;

	public function __construct(array $config){
		$this->server = new Server();
		$this->server->setDefaultPage(IndexPage::class);
		$this->server->registerPage("/", IndexPage::class);
		$this->server->registerPage("/decode", DecodePage::class);
		$this->server->registerPage("/searchId", SearchIdPage::class);
		$this->server->registerPage("/searchPn", SearchPnPage::class);
		$this->server->registerPage("/searchController", SearchControllerPage::class);
		$this->server->load($config);
	}

	public function start(){
		$this->server->start();
	}
}
