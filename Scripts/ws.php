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

//FlashDetector WebServer

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\WebServer\WebServer;
use iTXTech\SimpleFramework\Console\Logger;

Logger::$logLevel = 0;

global $moduleManager;
loadModule($moduleManager, "FDWebServer");

FlashDetector::init("chs");
$server = new WebServer([
	"address" => "0.0.0.0",
	"port" => 8080,
	"swoole" => [
		"worker_num" => 4
	]
]);
$server->start();
