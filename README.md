# comodojo/zip

[![Build Status](https://api.travis-ci.org/comodojo/zip.png)](http://travis-ci.org/comodojo/zip) [![Latest Stable Version](https://poser.pugx.org/comodojo/zip/v/stable)](https://packagist.org/packages/comodojo/zip) [![Total Downloads](https://poser.pugx.org/comodojo/zip/downloads)](https://packagist.org/packages/comodojo/zip) [![Latest Unstable Version](https://poser.pugx.org/comodojo/zip/v/unstable)](https://packagist.org/packages/comodojo/zip) [![License](https://poser.pugx.org/comodojo/zip/license)](https://packagist.org/packages/comodojo/zip) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comodojo/zip/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/comodojo/zip/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/comodojo/zip/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/comodojo/zip/?branch=master)

ZipArchive toolbox

This library was written to simplify and automate Zip files management using [PHP ZipArchive](http://php.net/manual/en/class.ziparchive.php).

From version 2.0.0, it supports multiple Zip files (extract, add file, remove file, merge, ...) via `\Comodojo\Zip\ZipManager` class.

## Installation

Install [composer](https://getcomposer.org/), then:

`` composer require comodojo/zip ``

## Zip usage

The `\Comodojo\Zip\Zip` class is made to handle a single zip file.

### Basic operations

- Open zip file:

    ```php    
    $zip = \Comodojo\Zip\Zip::open('file.zip');

    ```

- Create zip file:

    ```php    
    $zip = \Comodojo\Zip\Zip::create('file.zip');

    ```

- Check zip file:

    ```php    
    $is_valid = \Comodojo\Zip\Zip::check('file.zip');

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

The `\Comodojo\Zip\ZipManager` can handle multiple `\Comodojo\Zip\Zip` objects.

### Basic operations

- Init the manager and register Zips:

    ```php    
    // init manager
    $manager = new \Comodojo\Zip\ZipManager();

    // register existing zips
    $manager->addZip( \Comodojo\Zip\Zip::open('/path/to/my/file1.zip') )
            ->addZip( \Comodojo\Zip\Zip::open('/path/to/my/file2.zip') );

    // register a new zip
    $manager->addZip( \Comodojo\Zip\Zip::create('/path/to/my/file3.zip') );

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
    $manager-> = new \Comodojo\Zip\ZipManager();

    // register existing zips
    $manager->addZip( \Comodojo\Zip\Zip::open('/path/to/my/file1.zip') )
            ->addZip( \Comodojo\Zip\Zip::open('/path/to/my/file2.zip') );

    // register a new zip
    $manager->addZip( \Comodojo\Zip\Zip::create('/path/to/my/file3.zip') );

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

## Documentation

- [API](https://api.comodojo.org/libs/Comodojo/Zip.html)

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

`` comodojo/zip `` is released under the MIT License (MIT). Please see [License File](LICENSE) for more information.
