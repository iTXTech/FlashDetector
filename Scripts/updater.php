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
