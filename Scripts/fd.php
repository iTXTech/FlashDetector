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

//Flash Detector

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Util\Util;

if(!isset($argv[1])){
	Util::println("Usage: \"" . PHP_BINARY . "\" \"" . $argv[0] . "\" <part number>");
	exit(1);
}

$info = FlashDetector::detect($argv[1], true);
Util::println($info);
