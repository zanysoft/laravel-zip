<?php

namespace ZanySoft\Zip;

use Exception;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * ZanySoft\Zip - ZipArchive toolbox
 *
 * This class provide methods to handle single zip archive
 *
 * @package     ZanySoft\Zip
 * @author      ZanySoft <info@zanysoft.co>
 * @license     MIT
 *
 */
class Zip
{

    /**
     * Select files to skip
     *
     * @var string
     */
    private string $skip_mode = 'NONE';

    /**
     * Supported skip modes
     *
     * @var array
     */
    private array $supported_skip_modes = ['HIDDEN', 'ZANYSOFT', 'ALL', 'NONE'];

    /**
     * Mask for the extraction folder (if it should be created)
     *
     * @var int
     */
    private int $mask = 0777;

    /**
     * ZipArchive internal pointer
     *
     * @var \ZipArchive
     */
    private $zip_archive = null;

    /**
     * zip file name
     *
     * @var string|null
     */
    private ?string $zip_file = null;

    /**
     * zip file password (only for extract)
     *
     * @var string|null
     */
    private ?string $password = null;

    /**
     * Current base path
     *
     * @var string|null
     */
    private ?string $path = null;

    /**
     * Array of well known zip status codes
     *
     * @var array
     */
    private static array $zip_status_codes = [
        ZipArchive::ER_OK => 'No error',
        ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
        ZipArchive::ER_RENAME => 'Renaming temporary file failed',
        ZipArchive::ER_CLOSE => 'Closing zip archive failed',
        ZipArchive::ER_SEEK => 'Seek error',
        ZipArchive::ER_READ => 'Read error',
        ZipArchive::ER_WRITE => 'Write error',
        ZipArchive::ER_CRC => 'CRC error',
        ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
        ZipArchive::ER_NOENT => 'No such file',
        ZipArchive::ER_EXISTS => 'File already exists',
        ZipArchive::ER_OPEN => 'Can\'t open file',
        ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
        ZipArchive::ER_ZLIB => 'Zlib error',
        ZipArchive::ER_MEMORY => 'Malloc failure',
        ZipArchive::ER_CHANGED => 'Entry has been changed',
        ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
        ZipArchive::ER_EOF => 'Premature EOF',
        ZipArchive::ER_INVAL => 'Invalid argument',
        ZipArchive::ER_NOZIP => 'Not a zip archive',
        ZipArchive::ER_INTERNAL => 'Internal error',
        ZipArchive::ER_INCONS => 'Zip archive inconsistent',
        ZipArchive::ER_REMOVE => 'Can\'t remove file',
        ZipArchive::ER_DELETED => 'Entry has been deleted'
    ];

    /**
     * Class constructor
     *
     * @param string $zip_file ZIP file name
     *
     */
    public function __construct($zip_file = null)
    {
        if ($zip_file) {
            $this->open($zip_file);
        }
    }

    /**
     * Open a zip archive
     *
     * @param string $zip_file ZIP file name
     *
     * @return  Zip
     */
    public function open(string $zip_file)
    {
        try {
            $this->setArchive(self::openZipFile($zip_file));
        } catch (\Exception $ze) {
            throw $ze;
        }

        return $this;
    }

    /**
     * Check a zip archive
     *
     * @param string $zip_file ZIP file name
     *
     * @return  bool
     */
    public function check($zip_file): bool
    {
        try {
            $zip = self::openZipFile($zip_file, ZipArchive::CHECKCONS);

            $zip->close();
        } catch (Exception $ze) {
            return false;
        }

        return true;
    }

    /**
     * Create a new zip archive
     *
     * @param string $zip_file ZIP file name
     * @param bool $overwrite overwrite existing file (if any)
     *
     * @return  Zip
     */
    public function create(string $zip_file, bool $overwrite = false)
    {
        if ($overwrite and !$this->check($zip_file)) {
            $overwrite = false;
        }

        $overwrite = filter_var($overwrite, FILTER_VALIDATE_BOOLEAN, [
            'options' => [
                'default' => false
            ]
        ]);

        try {
            if ($overwrite) {
                $this->setArchive(self::openZipFile($zip_file, ZipArchive::OVERWRITE));
            } else {
                $this->setArchive(self::openZipFile($zip_file, ZipArchive::CREATE));
            }
        } catch (Exception $ze) {
            throw $ze;
        }

        return $this;
    }

    /**
     * Set files to skip
     *
     * @param string $mode [HIDDEN, ZANYSOFT, ALL, NONE]
     *
     * @return  Zip
     */
    final public function setSkipped($mode)
    {
        $mode = strtoupper($mode);

        if (!in_array($mode, $this->supported_skip_modes)) {
            throw new Exception('Unsupported skip mode');
        }

        $this->skip_mode = $mode;

        return $this;
    }

    /**
     * Get current skip mode (HIDDEN, ZANYSOFT, ALL, NONE)
     *
     * @return  string
     */
    final public function getSkipped()
    {
        return $this->skip_mode;
    }

    /**
     * Set extraction password
     *
     * @param string $password
     *
     * @return  Zip
     */
    final public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get current extraction password
     *
     * @return  string
     */
    final public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set current base path (just to add relative files to zip archive)
     *
     * @param string $path
     *
     * @return  Zip
     */
    public function setPath(string $path)
    {
        $path = rtrim(str_replace('\\', '/', $path), '/') . '/';

        if (!file_exists($path)) {
            throw new Exception('Not existent path');
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get current base path
     *
     * @return  string
     */
    final public function getPath()
    {
        return $this->path;
    }

    /**
     * Set extraction folder mask
     *
     * @param int $mask
     *
     * @return  Zip
     */
    final public function setMask($mask)
    {
        $mask = filter_var($mask, FILTER_VALIDATE_INT, [
            'options' => [
                'max_range' => 0777,
                'default' => 0777
            ], 'flags' => FILTER_FLAG_ALLOW_OCTAL
        ]);

        $this->mask = $mask;

        return $this;
    }

    /**
     * Get current extraction folder mask
     *
     * @return  int
     */
    final public function getMask()
    {
        return $this->mask;
    }

    /**
     * Set the current ZipArchive object
     *
     * @param \ZipArchive $zip
     *
     * @return  Zip
     */
    final public function setArchive(ZipArchive $zip)
    {
        $this->zip_archive = $zip;

        return $this;
    }

    /**
     * Get current ZipArchive object
     *
     * @return  \ZipArchive
     */
    final public function getArchive()
    {
        return $this->zip_archive;
    }

    /**
     * Get current zip file
     *
     * @return  string
     */
    final public function getZipFile()
    {
        return $this->zip_file;
    }

    /**
     * Get an SplFileObject for the zip file
     * @return \SplFileObject
     */
    public function getFileObject()
    {
        return new \SplFileObject($this->zip_file);
    }

    /**
     * Get a list of files in archive (array)
     *
     * @return  array
     */
    public function listFiles()
    {
        $list = [];

        for ($i = 0; $i < $this->zip_archive->numFiles; $i++) {
            $name = $this->zip_archive->getNameIndex($i);

            if ($name === false) {
                throw new Exception(self::getStatus($this->zip_archive->status));
            }

            array_push($list, $name);
        }

        return $list;
    }

    /**
     * Check if zip archive has a file
     *
     * @param string $file File
     * @param int $flags (optional) ZipArchive::FL_NOCASE, ZipArchive::FL_NODIR seperated by bitwise OR
     *
     * @return  bool
     */
    public function has($file, $flags = 0)
    {
        if (empty($file)) {
            throw new Exception('Invalid File');
        }

        return $this->zip_archive->locateName($file, $flags) !== false;
    }

    /**
     * Extract files from zip archive
     *
     * @param string $destination Destination path
     * @param mixed $files (optional) a filename or an array of filenames
     *
     * @return  bool
     */
    public function extract($destination, $files = null)
    {
        if (empty($destination)) {
            throw new Exception('Invalid destination path');
        }

        if (!file_exists($destination)) {
            $omask = umask(0);

            $action = mkdir($destination, $this->mask, true);

            umask($omask);

            if ($action === false) {
                throw new Exception('Error creating folder ' . $destination);
            }
        }

        if (!is_writable($destination)) {
            throw new Exception('Destination path not writable');
        }

        if (is_array($files) && count($files) != 0) {
            $file_matrix = $files;
        } else {
            $file_matrix = $this->getArchiveFiles();
        }

        if (!empty($this->password)) {
            $this->zip_archive->setPassword($this->password);
        }

        $extract = $this->zip_archive->extractTo($destination, $file_matrix);

        if ($extract === false) {
            throw new Exception(self::getStatus($this->zip_archive->status));
        }

        return true;
    }

    /**
     * Create file form content and add to zip archive
     *
     * @param string $name File name with extension
     * @param string $string File content
     * @return void
     */
    public function addFromString(string $name, string $string)
    {
        $this->zip_archive->addFromString($name, $string);
    }

    /**
     * Add files to zip archive
     *
     * @param mixed $file_path file path to add or an array of files path
     * @param bool $flatroot in case of directory, specify if root folder should be flatten or not
     *
     * @return  Zip
     */
    public function add($file_path, $flatroot = false)
    {
        if (empty($file_path)) {
            throw new Exception(self::getStatus(ZipArchive::ER_NOENT));
        }

        $flatroot = filter_var($flatroot, FILTER_VALIDATE_BOOLEAN, [
            'options' => [
                'default' => false
            ]
        ]);

        try {
            if (is_array($file_path)) {
                foreach ($file_path as $file) {
                    $this->addItem($file, $flatroot);
                }
            } else {
                $this->addItem($file_path, $flatroot);
            }
        } catch (Exception $ze) {
            throw $ze;
        }

        return $this;
    }

    /**
     * Delete files from zip archive
     *
     * @param mixed $filename filename to delete or an array of filenames
     *
     * @return  Zip
     */
    public function delete($filename)
    {
        if (empty($filename)) {
            throw new Exception(self::getStatus(ZipArchive::ER_NOENT));
        }

        try {
            if (is_array($filename)) {
                foreach ($filename as $file_name) {
                    $this->deleteItem($file_name);
                }
            } else {
                $this->deleteItem($filename);
            }
        } catch (Exception $ze) {
            throw $ze;
        }

        return $this;
    }

    /**
     * Close the zip archive
     *
     * @return  bool
     */
    public function close()
    {
        if ($this->zip_archive->close() === false) {
            throw new Exception(self::getStatus($this->zip_archive->status));
        }

        return true;
    }

    /**
     * Get a list of file contained in zip archive before extraction
     *
     * @return  array
     */
    private function getArchiveFiles()
    {
        $list = [];

        for ($i = 0; $i < $this->zip_archive->numFiles; $i++) {
            $file = $this->zip_archive->statIndex($i);

            if ($file === false) {
                continue;
            }

            $name = str_replace('\\', '/', $file['name']);

            if ($name[0] == '.' and in_array($this->skip_mode, ['HIDDEN', 'ALL'])) {
                continue;
            }

            if ($name[0] == '.' and @$name[1] == '_' and in_array($this->skip_mode, ['ZANYSOFT', 'ALL'])) {
                continue;
            }

            array_push($list, $name);
        }

        return $list;
    }

    /**
     * Add item to zip archive
     *
     * @param string $file File to add (realpath)
     * @param bool $flatroot (optional) If true, source directory will be not included
     * @param string|null $base (optional) Base to record in zip file
     *
     * @throws Exception
     */
    private function addItem(string $file, bool $flatroot = false, string $base = null): void
    {
        //$file = is_null($this->path) ? $file : $this->path . $file;

        if ($this->path && !Str::startsWith($file, $this->path)) {
            $file = $this->path . $file;
        }

        $real_file = str_replace('\\', '/', realpath($file));

        $real_name = basename($real_file);

        if (!is_null($base)) {
            if ($real_name[0] == '.' and in_array($this->skip_mode, ['HIDDEN', 'ALL'])) {
                return;
            }

            if ($real_name[0] == '.' and @$real_name[1] == '_' and in_array($this->skip_mode, ['ZANYSOFT', 'ALL'])) {
                return;
            }
        }

        if (is_dir($real_file)) {
            if (!$flatroot) {
                $folder_target = is_null($base) ? $real_name : $base . $real_name;

                $new_folder = $this->zip_archive->addEmptyDir($folder_target);

                if ($new_folder === false) {
                    throw new Exception(self::getStatus($this->zip_archive->status));
                }
            } else {
                $folder_target = null;
            }

            foreach (new \DirectoryIterator($real_file) as $path) {
                if ($path->isDot()) {
                    continue;
                }

                $file_real = $path->getPathname();

                $base = is_null($folder_target) ? null : ($folder_target . '/');

                try {
                    $this->addItem($file_real, false, $base);
                } catch (Exception $ze) {
                    throw $ze;
                }
            }
        } else if (is_file($real_file)) {
            $file_target = is_null($base) ? $real_name : $base . $real_name;

            $add_file = $this->zip_archive->addFile($real_file, $file_target);

            if ($add_file === false) {
                throw new Exception(self::getStatus($this->zip_archive->status));
            }
        } else {
            return;
        }
    }

    /**
     * Delete item from zip archive
     *
     * @param string $file File to delete (zippath)
     *
     */
    private function deleteItem($file)
    {
        $deleted = $this->zip_archive->deleteName($file);

        if ($deleted === false) {
            throw new \Exception(self::getStatus($this->zip_archive->status));
        }
    }

    /**
     * Open a zip file
     *
     * @param string $zip_file ZIP status code
     * @param int $flags ZIP status code
     *
     * @return  \ZipArchive
     */
    private static function openZipFile($zip_file, $flags = null)
    {
        $zip = new ZipArchive();

        if (is_null($flags)) {
            $open = $zip->open($zip_file);
        } else {
            $open = $zip->open($zip_file, $flags);
        }


        if ($open !== true) {
            throw new \Exception(self::getStatus($open));
        }

        return $zip;
    }

    /**
     * Get status from zip status code
     *
     * @param int $code ZIP status code
     *
     * @return  string
     */
    private static function getStatus(int $code): string
    {
        if (array_key_exists($code, self::$zip_status_codes)) {
            return self::$zip_status_codes[$code];
        } else {
            return sprintf('Unknown status %s', $code);
        }
    }
}
