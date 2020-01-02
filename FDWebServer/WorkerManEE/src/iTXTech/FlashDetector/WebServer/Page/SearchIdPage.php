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

use EaseCation\WorkerManEE\Page\AbstractPage;
use iTXTech\FlashDetector\FlashDetector;

class SearchIdPage extends AbstractPage{
	public static function onRequest(){
		if(!isset($_GET["id"])){
			return json_encode([
				"result" => false,
				"message" => "Missing Flash Id"
			]);
		}else{
			return json_encode([
				"result" => true,
				"data" => FlashDetector::searchFlashId($_GET["id"], true)
			]);
		}
	}
}
