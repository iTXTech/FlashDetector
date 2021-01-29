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

namespace iTXTech\FlashDetector\FDBGen\Generator;

use iTXTech\FlashDetector\Fdb\Fdb;
use iTXTech\FlashDetector\Fdb\PartNumber;

class Extra{
	public static function merge(Fdb $fdb, string $data) : void{
		foreach(json_decode($data, true) as $vendor => $pns){
			foreach($pns as $pn => $d){
				$pn = $fdb->getPartNumber($vendor, $pn, true)->merge(new PartNumber($pn, $d), true);
				if(isset($d["fid"])){
					$pn->setFlashIds($d["fid"]);
				}
			}
		}
	}
}
