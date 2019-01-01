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
	public const LANG_TRANSLATION_NOT_FOUND = "Translation not found.";

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

	public const UNKNOWN = "Unknown";

	public const NOT_SUPPORTED_REASON = "notSupportedReason";

	//SanDisk iNAND/NAND not supported
	public const SANDISK_INAND_NOT_SUPPORTED = "sandisk_inand_not_supported";
	public const SANDISK_NAND_NOT_SUPPORTED = "sandisk_nand_not_supported";

	//SKHynix
	public const SKHYNIX_E2NAND_NOT_SUPPORTED = "skhynix_e2nand_not_supported";
	public const SKHYNIX_OLD_NUMBERING = "skhynix_old_numbering";
	public const SKHYNIX_PM_WAFER = "skhynix_pm_wafer";
	public const SKHYNIX_PM_LEAD_FREE = "skhynix_pm_lead_free";
	public const SKHYNIX_PM_LEADED = "skhynix_pm_leaded";
	public const SKHYNIX_PM_LEAD_AND_HALOGEN_FREE = "skhynix_pm_lead_and_halogen_free";

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
}
