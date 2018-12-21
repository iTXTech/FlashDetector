# iTXTech FlashDetector

[![License](https://img.shields.io/github/license/iTXTech/FlashDetector.svg)](https://github.com/iTXTech/FlashDetector/blob/master/LICENSE)

Universal NAND Flash Part Number Decoder

## Usage

```powershell
PS X:\>git clone https://github.com/iTXTech/SimpleFramework.git --depth=1 sf

PS X:\Scripts>php fd.php TH58TEG7DDJTA20
PS X:\Scripts>php fd.php H27UDG8M2MTR
PS X:\Scripts>php fd.php K9HDGD8U5M

PS X:\Scripts>php rs.php 98D584327656
PS X:\Scripts>php rs.php 89A46432AA05

PS X:\Scripts>php mfcmd.php -m -c -v NW634
PS X:\Scripts>php mfcmd.php -s -p -v FBNL63B71KDK

PS X:\Scripts>php test_output.php -l chs
PS X:\Scripts>php test_output.php -l eng -r
```

## License

    Copyright (C) 2018 iTX Technologies

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
