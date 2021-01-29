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

use iTXTech\SimpleFramework\Module\Packer as AbstractPacker;
use iTXTech\SimpleFramework\Util\StringUtil;

class Packer extends AbstractPacker{
	public function processFile(int $variant, \Phar $phar, string $file, string $path){
		if($variant == self::VARIANT_COMPOSER or
			($variant == self::VARIANT_TYPICAL
				and !StringUtil::startsWith($path, "vendor")
				and !StringUtil::endsWith($path, "composer.lock")
				and !StringUtil::endsWith($path, "composer.json"))){
			if(StringUtil::endsWith($file, ".json")){
				$phar->addFromString($path, json_encode(json_decode(file_get_contents($file))));
			}else{
				$phar->addFile($file, $path);
			}
		}
	}
}
