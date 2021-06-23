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

namespace iTXTech\FlashDetector\FlashId;

use iTXTech\FlashDetector\FlashIdInfo;

abstract class Decoder{
	public $def;
	public $vendorName;
	public $vendorId;

	public function __construct(string $vendorName, int $vendorId, array $def){
		$this->vendorName = $vendorName;
		$this->vendorId = $vendorId;
		$this->def = $def;
	}

	public function check(int $id) : bool{
		if(($id > 0x100000000000 and $id < 0x1000000000000) and $id >> 40 == $this->vendorId){
			return true;
		}
		return false;
	}

	public function decode(string $id) : FlashIdInfo{
		return self::decodeIdDef($id, $this->def, (new FlashIdInfo($id))->setVendor($this->vendorName));
	}

	public function decodeIdDef(int $id, array $def, FlashIdInfo $info) : FlashIdInfo{
		foreach($def as $offset => $rules){
			$byte = ($id >> (8 * (6 - $offset))) & 0xff;
			foreach($rules as $name => $rule){
				$data = 0;
				foreach($rule["dq"] as $dq){
					$data = ($data << 1) + (($byte >> $dq) & 0b1);
				}
				if(isset($rule["def"][$data])){
					$info->{"set" . ucfirst($name)}($rule["def"][$data]);
				}
			}
		}
		return $info;
	}
}
