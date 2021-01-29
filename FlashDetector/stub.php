<?php

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Util\Util;

$m = (new Phar(__FILE__))->getMetadata();
echo "iTXTech FlashDetector version " . $m["version"] .
    (file_exists("phar://" . __FILE__ . DIRECTORY_SEPARATOR . "vendor") ?
        " [with simplehtmldom]" : " [without simplehtmldom]") . PHP_EOL .
    "Revision: " . $m["revision"] . PHP_EOL .
    "Created on " . date("r", $m["creationDate"]) . PHP_EOL .
    "Copyright (C) 2018-2021 iTX Technologies
Licensed under Apache License 2.0
https://github.com/iTXTech/FlashDetector

Powered by iTXTech SimpleFramework
https://github.com/iTXTech/SimpleFramework

";
require_once "phar://" . __FILE__ . DIRECTORY_SEPARATOR . "sfloader.php";
if(isSimpleFrameworkLoaded()){
	Initializer::initTerminal();
	Logger::$logLevel = 4;
	$manager = new ModuleManager(Initializer::getClassLoader(),
		__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR);
	$manager->tryLoadModule(__FILE__);
	if(!isset($argv[1])){
		Util::println("Usage: \"" . PHP_BINARY . "\" \"" . $argv[0] . "\" <Part Number>");
		exit(1);
	}
	$info = FlashDetector::detect($argv[1], true);
	Util::println($info);
}
__HALT_COMPILER();
