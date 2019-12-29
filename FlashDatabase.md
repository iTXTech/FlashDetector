# iTXTech FlashDetector Flash Database

This document presents the reference format of FlashDetector Flash Database `fdb.json`.

A property will be omitted when generating if its value is `null`, `-1`, or `[]`.

## Access Flash Database through code

```php
//Obtain iTXTech\FlashDetector\Fdb\Fdb instance through FlashDetector
$fdb = FlashDetector::getFdb();

//Load from JSON or Array
$fdb = new Fdb(json_decode(file_get_contents("path_to_your_fdb.json"), true));
```

## Flash Database JSON Format

**DO NOT USE COMMENTS WHEN EDITING `fdb.json`**

```json
{
    "info": {
        "name": "iTXTech FlashDetector Flash Database",
        "version": "version code",
        "website": "https:\/\/github.com\/iTXTech\/FlashDetector",
        "time": "FDB Generation Time",
        "controllers": [
            "Controller"
        ]
    },
    "iddb": {
        "FlashId": {
            "s": 0, //Page Size
            "p": 0, //Pages Per Block
            "b": 0, //Blocks
            "t": [
                "Controller"
            ],
            "n": [
                "Part Numbers"
            ]
        }
    },
    "Vendor": {
        "Part number": {
            "id": [
                "Flash Id",
            ],
            "l": "Process node",
            "c": "Cell level",
            "t": [
                "Controller",
            ],
            "m": "Additional Info",
            "d": 8, //Die
            "e": 2, //CE
            "r": 4, //Rb
            "n": 1 //Channel
        }
    }
}
```
