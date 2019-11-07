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

require_once "env.php";

use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Util;

$fd = __DIR__ . "/FlashDetector.phar";

$ver = 0;
if(file_exists($fd)){
	$phar = new Phar($fd);
	$ver = (float) $phar->getMetadata()["version"];
	unset($phar);
}

Util::println("Checking PHAR " . $fd);
Util::println("iTXTech FlashDetector version: " . $ver);

Util::println(PHP_EOL . "Fetching version information");

$response = Curl::newInstance()
	->setUrl("https://api.github.com/repos/iTXTech/FlashDetector/releases")
	->setUserAgent("iTXTech-FlashDetector-Updater")
	->exec();
if($response->isSuccessful()){
	$info = json_decode($response->getBody(), true)[0];
	$newVer = (float) substr($info["tag_name"], 1);
	if($newVer > $ver){
		Util::println("New version found: " . $newVer);
		Util::println("Downloading...");
		@unlink($fd);
		$url = "";
		foreach($info["assets"] as $asset){
			if($asset["name"] == "FlashDetector.phar"){
				$url = $asset["browser_download_url"];
			}
		}
		Util::downloadFile($fd, $url);
	}else{
		Util::println("No new version found.");
	}
}else{
	Util::println("Fetch failed.");
}
