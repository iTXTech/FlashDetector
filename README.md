# iTXTech FlashDetector

[![License](https://img.shields.io/github/license/iTXTech/FlashDetector.svg)](https://github.com/iTXTech/FlashDetector/blob/master/LICENSE)

Universal NAND Flash Part Number Decoder and Search Engine.

Universal NAND Flash ID Decoder and Search Engine.

## Launch Now

Public `FlashDetector` Web Server (with `iTXTech ChipXpert™ Insight`): [https://fd.sakuracg.com](https://fd.sakuracg.com)

Public [FlashMaster](https://github.com/iTXTech/FlashMaster) Frontend: [https://flashm.cf](https://flashm.cf)

## 声明

`iTXTech FlashDetector`从**版本69**开始，采用`AGPLv3`许可证开源，衍生项目（包括服务端使用）必须遵循该许可证开源。

如果您需要完整的商业授权，可以[联系作者](mailto:peratx@itxtech.org)。

## Frontend

* [FlashMaster (Vue)](https://github.com/iTXTech/FlashMaster) - *Modern JavaScript Client*
* [FlashMasterAndroid](https://github.com/iTXTech/FlashMasterAndroid) - *Android Wrapper of FlashMaster*
* [FlashMasteriOS](https://github.com/iTXTech/FlashMasteriOS) - *iOS Wrapper of FlashMaster*
* [FlashMaster (Elang)](https://github.com/PeratX/FlashMaster) - *Legacy Windows Client*

## Requirements

* [PHP >= 7.2](https://www.php.net)
* [SimpleFramework 2.2](https://github.com/iTXTech/SimpleFramework)
* [Composer](https://github.com/composer/composer)

## Setup

```bash
$ git clone https://github.com/iTXTech/FlashDetector.git
$ cd FlashDetector/FlashDetector
$ composer install
```

## Supported

### Flash Vendors

1. [Intel](https://www.intel.com/)
1. [Micron](https://www.micron.com/)
1. [Western Digital](https://www.westerndigital.com/) - *Formerly SanDisk*
1. [KIOXIA](https://www.kioxia.com/) - *Formerly Toshiba Memory*
1. [Samsung](https://www.samsung.com/)
1. [SK hynix](https://www.skhynix.com/)
1. [YMTC](http://www.ymtc.com/)

### Controller Vendors

1. [Silicon Motion](https://www.siliconmotion.com/)
2. [ASolid](https://www.asolid-tek.com/)
3. [JMicron](https://www.jmicron.com/)
4. [Maxio](http://www.maxio-tech.com/)
5. [SandForce](https://www.seagate.com/) - *Now Seagate*
6. [Chipsbank](http://www.chipsbank.com/)
7. [Alcor Micro](https://www.alcormicro.com/)
8. [Phison](https://www.phison.com/)

## Web Server

There are 4 implementations of `FDWebServer`:

1. [FDWebServer-CGI](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/CGI) - Compatible with Apache and PHP-FPM
1. [FDWebServer-Swoole](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/swoole) - Extreme High Performance, using [swoole](https://github.com/swoole/swoole-src)
1. [FDWebServer-WorkerManEE](https://github.com/iTXTech/FlashDetector/tree/master/FDWebServer/WorkerManEE) - Single
   Thread Server for Any OS
1. [SharpFlashDetector](https://github.com/iTXTech/SharpFlashDetector) - `C#` implementation of `FlashDetector`

## Usage

See files in [Scripts](Scripts).

## Flash Database

[FlashDetector RAW Flash Database (fdfdb)](https://github.com/iTXTech/fdfdb)

[iTXTech FlashDetector Flash Database Documentation](FlashDatabase.md)

## License

    Copyright (C) 2018-2022 iTX Technologies

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
