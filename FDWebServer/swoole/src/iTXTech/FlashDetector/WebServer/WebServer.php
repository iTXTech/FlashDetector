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

use iTXTech\FlashDetector\WebServer\Page\DecodePage;
use iTXTech\FlashDetector\WebServer\Page\IndexPage;
use iTXTech\FlashDetector\WebServer\Page\InfoPage;
use iTXTech\FlashDetector\WebServer\Page\SearchIdPage;
use iTXTech\FlashDetector\WebServer\Page\SearchPnPage;
use iTXTech\FlashDetector\WebServer\Page\SummaryPage;
use iTXTech\SimpleSwFw\Http\Server;

class WebServer{
	/** @var Server */
	private $server;

	public function __construct(array $config){
		$this->server = new Server();
		$this->server->registerPage("/", IndexPage::class);
		$this->server->registerPage("/index", IndexPage::class);
		$this->server->registerPage("/decode", DecodePage::class);
		$this->server->registerPage("/searchId", SearchIdPage::class);
		$this->server->registerPage("/searchPn", SearchPnPage::class);
		$this->server->registerPage("/summary", SummaryPage::class);
		$this->server->registerPage("/info", InfoPage::class);
		$this->server->load($config);
	}

	public function start(){
		$this->server->start();
	}
}
