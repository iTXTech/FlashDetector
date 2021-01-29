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

abstract class Arrayable{

	public function __construct(array $arr = null){
		if($arr != null){
			foreach($arr as $k => $v){
				$this->{$k} = $v;
			}
		}
	}

	public function toArray() : array{
		$reflectionClass = new \ReflectionClass($this);
		$properties = $reflectionClass->getProperties();
		$info = [];
		foreach($properties as $property){
			self::process($info, $name = $property->getName(), $this->{$name});
		}
		return $info;
	}

	private static function process(array &$a, string $k, $v){
		if(is_object($v)){
			/** @var Arrayable $v */
			$a[$k] = $v->toArray();
		}elseif(is_array($v)){
			$a[$k] = self::transformArray($v);
		}else{
			$a[$k] = $v;
		}
	}

	private static function transformArray(array $a) : array{
		foreach($a as $k => $v){
			self::process($a, $k, $v);
		}
		return $a;
	}
}
