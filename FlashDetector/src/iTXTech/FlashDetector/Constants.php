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

namespace iTXTech\FlashDetector;

interface Constants{
	public const LEAD_FREE = "leadFree";
	public const HALOGEN_FREE = "halogenFree";
	public const WAFER = "wafer";
	public const OPERATION_TEMPERATURE = "opTemp";
	public const BAD_BLOCK = "badBlock";
	public const SKU = "sku";

	//base = 1 MBits
	public const DENSITY_GBITS = 1024;
	public const DENSITY_TBITS = 1024 * 1024;

	public const MANUFACTURER_INTEL = "intel";
	public const MANUFACTURER_MICRON = "micron";
	public const MANUFACTURER_SAMSUNG = "samsung";
	public const MANUFACTURER_SANDISK = "sandisk";
	public const MANUFACTURER_SKHYNIX = "skhynix";
	public const MANUFACTURER_SPECTEK = "spectek";
	public const MANUFACTURER_TOSHIBA = "toshiba";

	public const NAND_TYPE_NAND = "nand";
	public const NAND_TYPE_E2NAND = "e2nand";
	public const NAND_TYPE_INAND = "inand";
	public const NAND_TYPE_ISSD = "issd";

	public const UNKNOWN = "Unknown";

	public const UNSUPPORTED_REASON = "unsupportedReason";

	//SanDisk iNAND/NAND not supported
	public const SANDISK_INAND_NOT_SUPPORTED = "sandisk_inand_not_supported";
	public const SANDISK_ISSD_NOT_SUPPORTED = "sandisk_issd_not_supported";

	//SKHynix
	public const SKHYNIX_E2NAND_NOT_SUPPORTED = "skhynix_e2nand_not_supported";
	public const SKHYNIX_OT_C = "skhynix_ot_c";
	public const SKHYNIX_OT_E = "skhynix_ot_e";
	public const SKHYNIX_OT_M = "skhynix_ot_m";
	public const SKHYNIX_OT_I = "skhynix_ot_i";

	//SpecTek
	public const SPECTEK_OLD_NUMBERING = "spectek_old_numbering";
	public const SPECTEK_DENSITY_GRADE_ZERO = "spectek_density_grade_zero";
	//packageFunctionalityPartialType
	public const SPECTEK_PFPT_A = "spectek_pfpt_a";
	public const SPECTEK_PFPT_B = "spectek_pfpt_b";
	public const SPECTEK_PFPT_C = "spectek_pfpt_c";
	public const SPECTEK_PFPT_D = "spectek_pfpt_d";
	//interface
	public const SPECTEK_IF_E = "spectek_if_e";
	public const SPECTEK_IF_F = "spectek_if_f";
	public const SPECTEK_IF_G = "spectek_if_g";
	public const SPECTEK_IF_M = "spectek_if_m";
	public const SPECTEK_IF_N = "spectek_if_n";

	//Toshiba
	public const TOSHIBA_E2NAND_NOT_SUPPORTED = "toshiba_e2nand_not_supported";

	//Samsung
	public const SAMSUNG_NONE = "samsung_none";

	public const SAMSUNG_TEMP_C = "samsung_temp_c";
	public const SAMSUNG_TEMP_S = "samsung_temp_s";
	public const SAMSUNG_TEMP_B = "samsung_temp_b";
	public const SAMSUNG_TEMP_I = "samsung_temp_i";

	public const SAMSUNG_CBB_B = "samsung_cbb_b";
	public const SAMSUNG_CBB_D = "samsung_cbb_d";
	public const SAMSUNG_CBB_K = "samsung_cbb_k";
	public const SAMSUNG_CBB_L = "samsung_cbb_l";
	public const SAMSUNG_CBB_N = "samsung_cbb_n";
	public const SAMSUNG_CBB_S = "samsung_cbb_s";

	//Intel
	public const INTEL_SKU_S = " intel_sku_s";
}
