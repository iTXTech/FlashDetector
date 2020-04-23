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

require_once "../sfloader.php";

use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\StringUtil;

Initializer::initTerminal();

$exclude = ["Online", "vendor", "composer", "resource", "json", "stub.php", "Packer"];

foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($p = "../FlashDetector")) as $file){
	$path = ltrim(str_replace(["\\", $p], ["/", ""], $file), "/");
	if($path{0} === "." or strpos($path, "/.") !== false){
		continue;
	}
	foreach($exclude as $e){
		if(StringUtil::contains($file, $e)){
			continue 2;
		}
	}
	$a = explode("/", $path = "FlashDetector/$path");
	unset($a[count($a) - 1]);
	@mkdir(implode("/", $a), 666, true);
	copy($file, $path);
	if(StringUtil::endsWith($path, "FlashDetector.php")){
		file_put_contents($path, str_replace([
			'Loader::getInstance()->getResourceAsText("fdb.json")',
			'Loader::getInstance()->getResourceAsText("mdb.json")',
			'self::$lang[$l] = json_decode(Loader::getInstance()->getResourceAsText("lang/$l.json"), true);',
			'["chs", "eng"]'
		], [
			getJson("../FlashDetector/resources/fdb.json"),
			getJson("../FlashDetector/resources/mdb.json"),
			'self::$lang = ["chs"=>json_decode(' . getJson("../FlashDetector/resources/lang/chs.json") .
			',true),"eng"=>json_decode(' .
			getJson("../FlashDetector/resources/lang/eng.json") . ',true)];',
			'[""]'
		], file_get_contents($path)));
	}
}

function getJson(string $f) : string{
	return "'" . json_encode(json_decode(file_get_contents($f), true)) . "'";
}
