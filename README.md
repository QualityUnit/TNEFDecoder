# TNEFDecoder


Simple PHP library to decode TNEF files (winmail.dat).

It is based on [SquirellMail plugin](https://squirrelmail.org/plugin_view.php?id=62)

#Requirements:
- PHP >= 7.0
- PHP-iconv extension

#Usage
Include via composer:
```
composer require qualityunit/tnef-decoder
```
Create object to parse attachment file
```
$attachment = new TNEFAttachment();
```
Parse string with attachment file and receive array of extracted file objects. 
```
$attachment->decodeTnef($buffer);
$files = $attachment->getFiles();
```