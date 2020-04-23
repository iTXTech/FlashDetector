# FDWebServer

High performance HTTP server for FlashDetector

## Requirements

### swoole-powered

* [swoole 4.4+](https://github.com/swoole/swoole-src)
* Windows 10 should use WSL
* Windows 7/8/8.1 can use cygwin/Msys2

### WorkerManEE-powered

* [WorkerManEE 17.10.1](https://github.com/EaseCation/WorkerManEE)
* Compatible with all Operating System

### CGI

* Any WebServer which supports CGI (apache, nginx and etc.)

## Startup

```
php ws.php -a 0.0.0.0 -p 8080 -s // -s for swoole, -w for WorkerManEE
```

## API

* Protocol: `HTTP(S)`
* Method: `GET`
* Supported Language: `chs for Simplified Chinese`, `eng for English`

### / - *Get FDWebServer information*

### /info - *Get FlashDetector information*

### /decode - *Decode Part Number*

|Argument|Type|Description|
|---|---|---|
|pn|String|Part Number|
|lang|String|Language|

### /searchId - *Search Flash Id in Flash Database*

|Argument|Type|Description|
|---|---|---|
|id|String|Flash Id|

### /searchPn - *Search Part Number in Flash DB*

|Argument|Type|Description|
|---|---|---|
|pn|String|Part Number|
|lang|String|Language|

### /summary - *Generate summary for Part Number*

|Argument|Type|Description|
|---|---|---|
|pn|String|Part Number|
|lang|String|Language|

## License

    Copyright (C) 2018-2020 iTX Technologies

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
