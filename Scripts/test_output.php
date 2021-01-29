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

//Test Output

require_once "env.php";

use iTXTech\FlashDetector\FlashDetector;
use iTXTech\SimpleFramework\Util\Util;

const PNS = [
	"29F64G08AAMF1",
	"MT29F32G08AFABA",
	"K9HDGD8U5M",
	"SDTNPMCHEM-032G",
	"SDIN5C4-32G",//iNAND
	"H27UDG8M2MTR",
	"H2JTDG8UD2MBR",//E2NAND
	"TH58TEG8D2HTA20",
	"THGVX1G7D2GLA08",//E2NAND
	"FNNL06B256G1KDBABWP"
];

foreach(PNS as $pn){
	$data = FlashDetector::detect($pn);
	Util::println(json_encode($data->toArray(),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
