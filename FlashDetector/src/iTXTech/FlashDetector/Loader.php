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

namespace iTXTech\FlashDetector;

use iTXTech\FlashDetector\Processor\DefaultProcessor;
use iTXTech\SimpleFramework\Module\Module;
use iTXTech\SimpleFramework\Module\ModuleInfo;

class Loader extends Module{
	private static $instance;

	public function load(){
		self::$instance = $this;
		FlashDetector::initialize();
		$info = new \ReflectionClass(ModuleInfo::class);
		$prop = $info->getProperty("version");
		$prop->setAccessible(true);
		$prop->setValue($this->getInfo(), FlashDetector::getVersion() . "." . $this->getInfo()->getVersion());

		FlashDetector::registerProcessor(new DefaultProcessor());
	}

	public function unload(){
	}

	public static function getInstance() : ?Loader{
		return self::$instance;
	}
}
