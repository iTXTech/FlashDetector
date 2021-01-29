# iTXTech FlashDetector

[![License](https://img.shields.io/github/license/iTXTech/FlashDetector.svg)](https://github.com/iTXTech/FlashDetector/blob/master/LICENSE)

Universal NAND Flash Part Number Decoder and Flash Id Search Engine.

## 声明

`iTXTech FlashDetector`从**版本69**开始，采用`AGPLv3`许可证开源，衍生项目（包括服务端使用）必须遵循该许可证开源。

如果您需要完整的商业授权，可以[联系作者](mailto:peratx@itxtech.org)。

## Requirements

* [PHP 7.2 ~ 7.4](https://secure.php.net)
* [SimpleFramework 2.2](https://github.com/iTXTech/SimpleFramework)
* [Composer](https://github.com/composer/composer)

## Setup

```shell script
$ git clone https://github.com/iTXTech/FlashDetector.git
$ cd FlashDetector/FlashDetector
$ composer install
```

## Supported

### Flash Vendors

1. `Intel`/`Micron (SpecTek)`
1. `Samsung`
1. `Western Digital (SanDisk)`/`Kioxia (Toshiba)`
1. `SK hynix`

### Controllers

1. `SiliconMotion` (`SM321AC, SM321BB, SM321BC, SM324BB, SM324BC, SM3252A, SM3252B, SM3252C, SM3254AE, SM3255AA, SM3255AB, SM3255ENA1, SM3255ENAA, SM3257AA, SM3257ENAA, SM3257ENAA_8CE, SM3257ENBA, SM3257ENBB, SM3257ENLT, SM3259AA, SM3259AB, SM325AB, SM325AC, SM3260AA, SM3260AB, SM3260AD, SM3261AA, SM3261AB, SM3263AA, SM3263AB, SM3267AA, SM3267AB, SM3267AB_COB, SM3267AC, SM3267AE, SM3268AA, SM3268AB, SM3269AA, SM3269AA_COB, SM3270AA, SM3270AB, SM3270AC, SM3271AB, SM3280AB, SM3280BA, SM3280BB, SM3281AB, SM3281BA, SM3281BB, SM3282BB, SM2231, SM2232, SM2240, SM2242, SM2244LT, SM2246EN, SM2246XT, SM2250, SM2256, SM2258XT, SM2258, SM2259XT, SM2263XT, SM2263EN, SM2259, SM2262EN`)
1. `Innostor` (`IS902E, IS902, IS903, IS916EN, IS916, IS917`)
1. `JMicron` (`JMF606, JMF608, JMF612, JMF616, JMF667, JMF670H`)
1. `Maxiotek` (`MK8115`)
1. `Maxio` (`MAS0902`)
1. `SandForce` (`SF2141, SF2181, SF2281, SF2282, SF2382, SF2481, SF2241, SF2582, SF2581, SF2682`)
1. `ChipsBank` (`CBM2092, CBM2093, CBM2093P, CBM2095, CBM2096P, CBM2096, CBM2096PT, CBM2096T, CBM2098P, CBM2098E, CBM2093E, CBM2098S, CBM2099, CBM2099E, CBM2099S, CBM2199, CBM2199S`)
1. `AlcorMicro` (`AU6987/AN, AU6989SNL, AU6989SNL-B, AU6989SN, AU6989SN-G, AU6989SN-GT, AU6989SN-GTA/B/C/D/E`)

-----------

#### Controllers with combined support

1. `SM3271AC` -> `SM3271AB`
1. `SM3281BA` -> `SM3281AB`

## Web Server

There are three types of FDWebServer:

1. [FDWebServer-CGI](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/CGI) - Compatible with Apache and PHP-FPM
1. [FDWebServer-Swoole](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/swoole) - Extreme High Performance, using [swoole](https://github.com/swoole/swoole-src)
1. [FDWebServer-WorkerManEE](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/WorkerManEE) - Single Thread Server for Any OS

## Todo

1. Add Support for NAND with controller (eMMC, E2NAND, iSSD, etc.)
1. Add Support for DRAM (DDR2/3/4/5, GDDR3/4/5/5X/6)
1. Improve Western Digital (SanDisk)

## Usage

See files in [Scripts](https://github.com/iTXTech/FlashDetector/tree/master/Scripts).

## Flash Database

[FlashDetector RAW Flash Database (fdfdb)](https://github.com/iTXTech/fdfdb)

[iTXTech FlashDetector Flash Database Documentation](https://github.com/iTXTech/FlashDetector/blob/master/FlashDatabase.md)

## License

    Copyright (C) 2018-2021 iTX Technologies

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
