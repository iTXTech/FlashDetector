<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018 iTX Technologies
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

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;

header("Content-Type: application/json");
if(!isset($_GET["pn"])){
	echo json_encode([
		"result" => false,
		"message" => "Missing part number"
	]);
}else{
	echo json_encode([
		"result" => true,
		"data" => FlashDetector::detect($_GET["pn"])
			->toArray((($_GET["trans"] ?? 0) == 1) ? false : true)
	]);
}
