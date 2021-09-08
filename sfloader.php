<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

const SF_LOADER_VERSION = 3;
const SF_PHAR_FILENAME = "SimpleFramework.phar";

if (isset($argv[1]) and substr($argv[1], 0, 2) == "sf") {
	$f = explode("=", $argv[1], 2)[1];
	array_splice($argv, 1, 1);
	if (is_file($f)) {
		$phar = new Phar($f);
		if (($phar->getMetadata()["name"]) ?? "" == "SimpleFramework") {
			require_once "phar://" . $f . DIRECTORY_SEPARATOR . "autoload.php";
		} else {
			require_once $f;
		}
	} else {
		require_once $f . DIRECTORY_SEPARATOR . "autoload.php";
	}
} elseif (Phar::running() == "" and file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . SF_PHAR_FILENAME) or
	Phar::running() != "" and file_exists($f = getcwd() . DIRECTORY_SEPARATOR . SF_PHAR_FILENAME)) {
	require_once "phar://" . $f . DIRECTORY_SEPARATOR . "autoload.php";
} elseif (file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "autoload.php")) {
	require_once $f;
} elseif (file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf") and is_dir($f)) {
	require_once $f . DIRECTORY_SEPARATOR . "autoload.php";
} elseif (isset($_ENV["SF_HOME"])) {
	require_once $_ENV["SF_HOME"] . DIRECTORY_SEPARATOR . "autoload.php";
} elseif (isset($_ENV["SF_ARCHIVE"])) {
	require_once "phar://" . $_ENV["SF_ARCHIVE"] . DIRECTORY_SEPARATOR . "autoload.php";
} elseif (file_exists($f = __DIR__ . DIRECTORY_SEPARATOR . "sf.json")) {
	$config = json_decode(file_get_contents($f), true);
	if (file_exists($config["home"])) {
		require_once $config["home"] . DIRECTORY_SEPARATOR . "autoload.php";
	}
} else {
	if (count($_ENV) == 0) {
		echo '$_ENV is empty, please check your php.ini' . PHP_EOL;
	}
	echo "SimpleFramework Loader (version: " . SF_LOADER_VERSION . ") cannot locate SimpleFramework autoload.php" . PHP_EOL;
	echo "Run \"git clone https://github.com/iTXTech/SimpleFramework.git --depth=1 sf\"" . PHP_EOL;
}

function isSimpleFrameworkLoaded(): bool {
	return class_exists("iTXTech\SimpleFramework\Initializer");
}
