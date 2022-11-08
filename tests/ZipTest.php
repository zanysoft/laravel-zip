<?php


namespace ZanySoft\Zip\Tests;


use Orchestra\Testbench\TestCase;
use ZanySoft\Zip\Facades\Zip;

class ZipTest extends TestCase
{
    private $testFilePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->testFilePath = __DIR__ . '/TestFiles';
    }

    public function tearDown(): void
    {
        parent::tearDown();

//        array_map('unlink', glob($this->filesPath('Zips/*')) ?: []);
        $this->deleteTestFiles();
    }

    public function testCreateZipFileWithoutAddingFiles()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'), true);
        $zip->close();

        $this->assertFalse(file_exists($this->filesPath('Zips/TestZip.zip')));
    }

    public function testCreateZipWithAddingFiles()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->add($this->filesPath('file1.txt'));
        $zip->close();

        $this->assertTrue(file_exists($this->filesPath('Zips/TestZip.zip')));
    }

    public function testSetSkipThrowsExceptionOnInvalidMode()
    {
        $this->expectException(\Exception::class);

        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->setSkipped('doesntexist');
    }

    public function testSetValidSkipMode()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->setSkipped('hidden');
        $zip->close();

        $this->assertEquals('HIDDEN', $zip->getSkipped());
    }

    public function testListFiles()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->add($this->filesPath('file1.txt'));
        $zip->add($this->filesPath('file2.txt'));

        $this->assertEquals(2, count($zip->listFiles()));
        $this->assertEquals('file1.txt', $zip->listFiles()[0]);
        $this->assertEquals('file2.txt', $zip->listFiles()[1]);

        $zip->close();
    }

    public function testHasFile()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->add($this->filesPath('file1.txt'));

        $this->assertTrue($zip->has('file1.txt'));

        $zip->close();
    }

    public function testHasFileDoesNotExist()
    {
        $zip = Zip::create($this->filesPath('Zips/TestZip.zip'));
        $zip->add($this->filesPath('file1.txt'));

        $this->assertFalse($zip->has('file2.txt'));

        $zip->close();
    }

    public function testExtract()
    {
        $zip = Zip::open($this->filesPath('password.zip'));
        $zip->setPassword('password');
        $zip->extract($this->filesPath('Zips'));

        $this->assertTrue(file_exists($this->filesPath('Zips/file1.txt')));

        $zip->close();
    }

    public function deleteTestFiles()
    {
        $directoryPath = $this->filesPath('Zips');

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $removeFunction = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $removeFunction($fileinfo->getRealPath());
        }

        return true;
    }

    private function filesPath($file)
    {
        return $this->testFilePath . '/' . $file;
    }
}