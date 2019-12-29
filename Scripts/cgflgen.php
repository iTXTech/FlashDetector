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

//ChipGenius Flash List Generator

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\FlashDetector\Property\Classification;
use iTXTech\SimpleFramework\Util\StringUtil;

$data = "";

foreach(FlashDetector::getFdb()->getIddb()->getFlashIds() as $id){
	if(count($id->getPartNumbers()) > 0){
		$pn = explode(" ", $id->getPartNumbers()[0])[1];
		$info = FlashDetector::detect($pn, true);
		if(StringUtil::endsWith($info->getCellLevel() ?? "", "LC")){
			$d = "";
			if($id->getPageSize() == Classification::UNKNOWN_PROP){
				continue;
			}elseif($id->getPageSize() < 1){
				$d .= substr($id->getFlashId(), 0, 8) . "," . $info->getCellLevel() . "-" . $id->getPageSize() * 1024 . ",";
			}else{
				$d .= substr($id->getFlashId(), 0, 8) . "," . $info->getCellLevel() . "-" . $id->getPageSize() . "K,";
			}
			$hasPn = false;
			foreach($id->getPartNumbers() as $pn){
				$pn = explode(" ", $pn)[1];
				$info = FlashDetector::detect($pn);
				if($info->getClassification() != null and
					$info->getClassification()->getCe() > 0){
					$d .= "[" . $info->getClassification()->getCe() . "CE]" . $pn . "#";
					$hasPn = true;
				}
			}
			if($hasPn){
				$data .= substr($d, 0, strlen($d) - 1) . "\r\n";
			}
		}
	}
}

if(file_exists("CGFlashList_ORIG.csv")){
	$index = [];
	foreach(explode("\r\n", $data) as $flash){
		$id = explode(",", $flash)[0];
		$index[$id] = $flash;
	}
	foreach(explode("\r\n", file_get_contents("CGFlashList_ORIG.csv")) as $flash){
		$id = explode(",", $flash)[0];
		if(!isset($index[$id])){
			$index[$id] = $flash;
		}
	}
	$data = implode("\r\n", $index);
}

file_put_contents("CGFlashList.csv", $data);
