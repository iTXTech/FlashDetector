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

namespace iTXTech\FlashDetector\FDBGen;

abstract class IDDBGen{
	public static function generate(array $fdb) : array{
		$iddb = [
			"info" => [
				"name" => "iTXTech FlashDetector Flash Id Database",
				"website" => "https://github.com/iTXTech/FlashDetector",
				"version" => $fdb["info"]["version"],
				"time" => date("r")
			]
		];
		unset($fdb["info"]);
		foreach($fdb as $k => $v){
			foreach($v as $partNumber => $i){
				foreach($i["id"] as $id){
					if(!isset($iddb[$id])){
						$iddb[$id] = [$k . " " . $partNumber];
					}else{
						$iddb[$id][] = $k . " " . $partNumber;
					}
				}
			}
		}
		return $iddb;
	}
}
