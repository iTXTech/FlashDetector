# FlashDetector for [PeachPie](https://github.com/peachpiecompiler/peachpie/)

**Use `iTXTech FlashDetector` in `.NET Core / .NET Framework` projects.**

See [SharpFlashDetector](https://github.com/iTXTech/SharpFlashDetector)

## How to

1. Generate FlashDetector source folder for PeachPie Compiler: `php generate.php`
1. Copy `sf` folder (which contains `SimpleFramework src`, `autoload.php` and `sfloader.php`) into your .NET project root folder, see [SimpleFramework for PeachPie](https://github.com/iTXTech/SimpleFramework/tree/peachpie)
1. Copy `FlashDetector` and `fd.php` into your `.NET` project root folder
1. Configure PeachPie to compile these files
1. Import FlashDetector in your `.NET` project

## Load FlashDetector

```csharp
var context = Context.CreateEmpty();
PeachPieHelper.load(context);
```
