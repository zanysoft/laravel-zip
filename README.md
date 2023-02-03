# zanysoft/laravel-zip

<a href="https://www.buymeacoffee.com/zanysoft" target="_blank"><img src="https://img.buymeacoffee.com/button-api/?text=Buy me a coffee&emoji=&slug=zanysoft&button_colour=5F7FFF&font_colour=ffffff&font_family=Cookie&outline_colour=000000&coffee_colour=FFDD00" /></a>

[![Downloads](https://img.shields.io/packagist/dt/zanysoft/laravel-zip.svg?style=flat-square)](https://packagist.org/packages/zanysoft/laravel-zip)
[![GitHub license](https://img.shields.io/badge/License-MIT-informational.svg)](https://github.com/zanysoft/laravel-zip/blob/master/LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-informational.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GitHub tag](https://img.shields.io/github/tag/ZanySoft/laravel-zip.svg?style=flat&logo=laravel&color=informational)](https://github.com/zanysoft/laravel-zip/tags)




> laravel-zip is the world's leading zip utility for file compression and backup.

This library was written to simplify and automate Zip files management using [PHP ZipArchive](http://php.net/manual/en/class.ziparchive.php).

### Installation
Require this package in your composer.json and update composer. This will download the package.

    composer require zanysoft/laravel-zip

After updating composer, add the ServiceProvider to the providers array in config/app.php

    ZanySoft\Zip\ZipServiceProvider::class,

You can optionally use the facade for shorter code. Add this to your facades:

    'Zip' => ZanySoft\Zip\ZipFacade::class,


## Zip usage

use `ZanySoft\Zip\Zip` class only use `Zip` is made to handle a zip file.

- include the Zip class at top:
```php
use Zip;

```

### Basic operations

- Open zip file:

    ```php    
    $zip = Zip::open('file.zip');

    ```

- Create zip file:

    ```php    
    $zip = Zip::create('file.zip');

    ```

- Check zip file:

    ```php    
    $is_valid = Zip::check('file.zip');

    ```

- Extract zip file:

    ```php    
    // extract whole archive
    $zip->extract('/path/to/uncompressed/files');

    // extract a file
    $zip->extract('/path/to/uncompressed/files', 'file');

    // extract multiple files
    $zip->extract('/path/to/uncompressed/files', array('file1','file2'));

    ```
	
- Check if zip has a file:

    ```php    
    // Check if archive has a file
    $zip->has('/path/to/file/in/archive');

    // Check if archive has a file case insensitively
    $zip->has('/path/to/file/in/archive', ZipArchive::FL_NOCASE);

    // Check if archive has a file ignoring directory component
    $zip->has('file', ZipArchive::FL_NODIR);

    // Check if archive has a file case insensitively ignoring directory component
    $zip->has('file', ZipArchive::FL_NOCASE|ZipArchive::FL_NODIR);

    ```

- Add a file/directory to zip:

    ```php    
    $zip->add('/path/to/my/file');

    // declaring path
    $zip->setPath('/path/to/my')->add('file');

    // add directory
    $zip->add('/path/to/my/directory');

    // add directory (only its content)
    $zip->add('/path/to/my/directory', true);

    ```

- Add content to zip:

    ```php    
    $zip->addFromString('file name with extension', 'content of file');
  
    $zip->addFromString('filename.txt', $file_content);
    $zip->addFromString('folder/file1.txt', $file_content);
    $zip->addFromString('folder/file2.txt', $file_content);


    ```

- Add multiple files/directories to zip:

    ```php    
    // using array as parameter
    $zip->add( array('/path/to/my/file1', '/path/to/my/file2');

    // chaining methods
    $zip->add('/path/to/my/file1')->add('/path/to/my/file2');

    // declaring path
    $zip->setPath('/path/to/my')->add('file1')->add('file2');

    ```

- Delete a file/directory from zip:

    ```php    
    $zip->delete('file');

    ```

- Delete multiple files/directories from zip:

    ```php    
    // using array as parameter
    $zip->delete( array('file1', 'file2') );

    // chaining methods
    $zip->delete('file1')->delete('file2');

    ```

- List content of zip file

    ```php    
    $zip->listFiles();

    ```

- Close zip file

    ```php    
    $zip->close();

    ```

### Additional methods

- Skip hidden files while adding directories:

    ```php    
    // set mode
    $zip->setSkipped('HIDDEN');

    // get mode
    $mode = $zip->getSkipped();

    ```

- Use password for zip extraction:

    ```php    
    // set password
    $zip->setPassword('slartibartfast');

    // get password
    $password = $zip->getPassword();

    ```

- Use a mask != 0777 for created folders:

    ```php    
    // set mask
    $zip->setMask(0644);

    // get mask
    $mask = $zip->getMask();

    ```

## ZipManager usage

The `\ZanySoft\Zip\ZipManager` can handle multiple `ZanySoft\Zip\Zip` objects.

- include the Zip and ZipManager class at top:
```php
use ZanySoft\Zip\ZipManager;
use Zip;
```

### Basic operations

- Init the manager and register Zips:

    ```php    
    // init manager
    $manager = new ZipManager();

    // register existing zips
    $manager->addZip( Zip::open('/path/to/my/file1.zip') )
            ->addZip( Zip::open('/path/to/my/file2.zip') );

    // register a new zip
    $manager->addZip( Zip::create('/path/to/my/file3.zip') );

    ```

- Basic zips management:

    ```php    
    // get a list of registered zips
    $list = $manager->listZips();

    // remove a zip
    $manager->removeZip($ZipObject);

    // get a Zip
    $zip = $manager->getZip(0);

    ```

- Add files to all zips:

    ```php    
    $manager-> = new ZipManager();

    // register existing zips
    $manager->addZip( Zip::open('/path/to/my/file1.zip') )
            ->addZip( Zip::open('/path/to/my/file2.zip') );

    // register a new zip
    $manager->addZip( Zip::create('/path/to/my/file3.zip') );

    ```

- Extract zips:

    ```php    
    // separate content in folders
    $extract = $manager->extract('/path/to/uncompressed/files', true);

    // use a single folder
    $extract = $manager->extract('/path/to/uncompressed/files', false);

    // extract single file
    $extract = $manager->extract('/path/to/uncompressed/files', false, 'file');

    // extract multiple files
    $extract = $manager->extract('/path/to/uncompressed/files', false, array('file1','file2'));

    ```

- Merge zips:

    ```php    
    // separate content in folders
    $manager->merge('/path/to/output/file.zip', true);

    // flatten files
    $manager->merge('/path/to/output/file.zip', false);

    ```

- Close zips:

    ```php    
    $manager->close();

    ```

### Additional methods

- Declare path from which add files:

    ```php    
    // set path
    $zip->setPath('/path/to/files');

    // get path
    $path = $zip->getPath();

    ```

- Use a mask != 0777 for created folders

    ```php    
    // set masks
    $manager->setMask(0644);

    // get masks
    $mask = $manager->getMask();

    ```
