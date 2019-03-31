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

namespace iTXTech\FlashDetector\FDBGen\Generator;

class Extra{
	public static function merge(array &$db, string $data) : void{
		foreach(json_decode($data, true) as $vendor => $pns){
			$vendor = strtolower($vendor);
			foreach($pns as $pn => $data){
				$pn = strtoupper($pn);
				if(isset($db[$vendor][$pn])){
					$existedData = $db[$vendor][$pn];
					foreach($existedData as $k => $v){
						if(is_array($v)){
							foreach($data[$k] as $n){
								if(!in_array($n, $v)){
									$existedData[$k][] = $n;
								}
							}
						}elseif($v == "" and isset($data[$k])){
							$existedData[$k] = $data[$k];
						}
					}
					$db[$vendor][$pn] = $existedData;
				}else{
					$db[$vendor][$pn] = $data;
				}
			}
		}
	}
}
