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

interface Constants{
	public const LEAD_FREE = "leadFree";
	public const HALOGEN_FREE = "halogenFree";
	public const WAFER = "wafer";
	public const OPERATION_TEMPERATURE = "opTemp";
	public const BAD_BLOCK = "badBlock";
	public const SKU = "sku";
	public const ENTERPRISE = "enterprise";

	//base = 1 MBits
	public const DENSITY_GBITS = 1024;
	public const DENSITY_TBITS = 1024 * 1024;

	public const VENDOR_INTEL = "intel";
	public const VENDOR_MICRON = "micron";
	public const VENDOR_SAMSUNG = "samsung";
	public const VENDOR_WESTERN_DIGITAL = "westerndigital";
	public const VENDOR_SKHYNIX = "skhynix";
	public const VENDOR_SPECTEK = "spectek";
	public const VENDOR_KIOXIA = "kioxia";
	public const VENDOR_TOSHIBA = "toshiba";
	public const VENDOR_SANDISK = "sandisk";
	public const VENDOR_YANGTZE = "ymtc";

	public const NAND_TYPE_NAND = "nand";
	public const NAND_TYPE_INAND = "inand";
	public const NAND_TYPE_ISSD = "issd";
	public const NAND_TYPE_CON = "nandcon";

	public const UNKNOWN = "Unknown";

	public const UNSUPPORTED_REASON = "unsupportedReason";

	//SanDisk iNAND/NAND not supported
	public const SANDISK_INAND_NOT_SUPPORTED = "sandisk_inand_not_supported";
	public const SANDISK_ISSD_NOT_SUPPORTED = "sandisk_issd_not_supported";
	public const SANDISK_CODE = "sandisk_code";

	//SKHynix
	public const SKHYNIX_UNSUPPORTED = "skhynix_unsupported";
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
	public const TOSHIBA_UNSUPPORTED = "toshiba_unsupported";
	public const PAGE_SIZE = "pageSize";
	public const BLOCK_SIZE = "blockSize";

	//Samsung
	public const SAMSUNG_NONE = "samsung_none";

	public const SAMSUNG_CU = "cu";

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
	public const INTEL_SKU_S = "intel_sku_s";

	//Micron
	public const MICRON_PN = "micronPartNumber";
	public const MICRON_WEBSITE = "micron_website";

	public const MICRON_OTR_AAT = "micron_otr_aat";
	public const MICRON_OTR_AIT = "micron_otr_ait";
	public const MICRON_OTR_IT = "micron_otr_it";
	public const MICRON_OTR_WT = "micron_otr_wt";
	public const MICRON_OTR_C = "micron_otr_c";

	public const MICRON_F_E = "micron_f_e";
	public const MICRON_F_M = "micron_f_m";
	public const MICRON_F_R = "micron_f_r";
	public const MICRON_F_S = "micron_f_s";
	public const MICRON_F_T = "micron_f_t";
	public const MICRON_F_X = "micron_f_x";
	public const MICRON_F_Z = "micron_f_z";

	public const MICRON_P = "micron_p";
	public const MICRON_P_ES = "micron_p_es";
	public const MICRON_P_QS = "micron_p_qs";
	public const MICRON_P_MS = "micron_p_ms";

	public const SPEED_GRADE = "speed_grade";
	public const FEATURES = "features";
	public const PROD_STATUS = "prod_status";
	public const DESIGN_REV = "design_rev";
	public const PROD_DATE = "prod_date";

	public const USA = "cty_us";
	public const SINGAPORE = "cty_sg";
	public const ITALY = "cty_it";
	public const JAPAN = "cty_jp";
	public const CHINA = "cty_cn";
	public const TAIWAN = "cty_tw";
	public const KOREA = "cty_kr";
	public const MIXED = "cty_mixed";
	public const ISRAEL = "cty_il";
	public const IRELAND = "cty_ie";
	public const MALAYSIA = "cty_my";
	public const PHILIPPINES = "cty_ph";

	public const DIFFUSION = "diffusion_loc";
	public const ENCAPSULATION = "encapsulation_loc";
}
