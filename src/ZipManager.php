<?php namespace ZanySoft\Zip;

use \ZanySoft\Zip\Zip;
use \Exception;

/**
 * Multiple ZipArchive manager
 *
 * @package     ZanySoft/Zip
 * @author      ZanySof <info@zanysoft.co>
 * @license     MIT
 *
 */

class ZipManager {

    /**
     * Array of managed zip files
     *
     * @var array
     */
    private $zip_archives = array();

    /**
     * Add a \Coodojo\Zip\Zip object to manager
     *
     * @param   \ZanySoft\Zip\Zip  $zip
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function addZip(\ZanySoft\Zip\Zip $zip) {

        $this->zip_archives[] = $zip;

        return $this;

    }

    /**
     * Remove a \Coodojo\Zip\Zip object from manager
     *
     * @param   \ZanySoft\Zip\Zip  $zip
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function removeZip(\ZanySoft\Zip\Zip $zip) {

        $archive_key = array_search($zip, $this->zip_archives, true);

        if ( $archive_key === false ) throw new Exception("Archive not found");

        unset($this->zip_archives[$archive_key]);

        return $this;

    }

    /**
     * Get a list of managed Zips
     *
     * @return  array
     */
    public function listZips() {

        $list = array();

        foreach ( $this->zip_archives as $key=>$archive ) $list[$key] = $archive->getZipFile();

        return $list;

    }

    /**
     * Get a  a \Coodojo\Zip\Zip object
     *
     * @param   int    $zipId    The zip id from self::listZips()
     *
     * @return  \ZanySoft\Zip\Zip
     */
    public function getZip($zipId) {

        if ( array_key_exists($zipId, $this->zip_archives) === false ) throw new Exception("Archive not found");

        return $this->zip_archives[$zipId];

    }

    /**
     * Set current base path (just to add relative files to zip archive)
     * for all zip files
     *
     * @param   string  $path
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function setPath($path) {

        try {

            foreach ( $this->zip_archives as $archive ) $archive->setPath($path);

        } catch (Exception $ze) {

            throw $ze;

        }

        return $this;

    }

    /**
     * Get a list of paths used by Zips
     *
     * @return  array
     */
    public function getPath() {

        $paths = array();

        foreach ( $this->zip_archives as $key=>$archive ) $paths[$key] = $archive->getPath();

        return $paths;

    }

    /**
     * Set default file mask for all Zips
     *
     * @param   int  $mask
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function setMask($mask) {

        try {

            foreach ( $this->zip_archives as $archive ) $archive->setMask($mask);

        } catch (Exception $ze) {

            throw $ze;

        }

        return $this;

    }

    /**
     * Get a list of masks from Zips
     *
     * @return  array
     */
    public function getMask() {

        $masks = array();

        foreach ( $this->zip_archives as $key=>$archive ) $masks[$key] = $archive->getMask();

        return $masks;

    }

    /**
     * Get a list of files in Zips
     *
     * @return  array
     */
    public function listFiles() {

        $files = array();

        try {

            foreach ( $this->zip_archives as $key=>$archive ) $files[$key] = $archive->listFiles();

        } catch (Exception $ze) {

            throw $ze;

        }

        return $files;

    }

    /**
     * Extract Zips to common destination
     *
     * @param   string  $destination    Destination path
     * @param   bool    $separate       Specify if files should be placed in different directories
     * @param   array   $files          Array of files to extract
     *
     * @return  bool
     */
    public function extract($destination, $separate = true, $files = null) {

        try {

            foreach ( $this->zip_archives as $archive ) {

                $local_path = substr($destination, -1) == '/' ? $destination : $destination.'/';

                $local_file = pathinfo($archive->getZipFile());

                $local_destination = $separate ? ($local_path.$local_file['filename']) : $destination;

                $archive->extract($local_destination, $files = null);

            }

        } catch (Exception $ze) {

            throw $ze;

        }

        return true;

    }

    /**
     * Merge multiple Zips into one
     *
     * @param   string  $output_zip_file    Destination zip
     * @param   bool    $separate           Specify if files should be placed in different directories
     *
     * @return  bool
     */
    public function merge($output_zip_file, $separate = true) {

        $pathinfo = pathinfo($output_zip_file);

        $temporary_folder = $pathinfo['dirname']."/".self::getTemporaryFolder();

        try {

            $this->extract($temporary_folder, $separate, null);

            $zip = Zip::create($output_zip_file);

            $zip->add($temporary_folder, true)->close();

            self::recursiveUnlink($temporary_folder);

        } catch (Exception $ze) {

            throw $ze;

        } catch (Exception $e) {

            throw $e;

        }

        return true;

    }

    /**
     * Add a file to zip
     *
     * @param   mixed   $file_name_or_array     filename to add or an array of filenames
     * @param   bool    $flatten_root_folder    in case of directory, specify if root folder should be flatten or not
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function add($file_name_or_array, $flatten_root_folder = false) {

        try {

            foreach ( $this->zip_archives as $archive ) $archive->add($file_name_or_array, $flatten_root_folder);

        } catch (Exception $ze) {

            throw $ze;

        }

        return $this;

    }

    /**
     * Delete a file from Zips
     *
     * @param   mixed   $file_name_or_array     filename to add or an array of filenames
     *
     * @return  \ZanySoft\Zip\ZipManager
     */
    public function delete($file_name_or_array) {

        try {

            foreach ( $this->zip_archives as $archive ) $archive->delete($file_name_or_array);

        } catch (Exception $ze) {

            throw $ze;

        }

        return $this;

    }

    /**
     * Close Zips
     *
     * @return  bool
     */
    public function close() {

        try {

            foreach ( $this->zip_archives as $archive ) $archive->close();

        } catch (Exception $ze) {

            throw $ze;

        }

        return true;

    }

    private static function removeExtension($filename) {

        $file_info = pathinfo($filename);

        return $file_info['filename'];

    }

    private static function getTemporaryFolder() {

        return "zip-temp-folder-".md5(uniqid(rand(), true), 0);

    }

    /**
     * @param string $folder
     */
    private static function recursiveUnlink($folder) {

        foreach ( new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path ) {

            $pathname = $path->getPathname();

            if ( $path->isDir() ) {

                $action = rmdir($pathname);

            } else {

                $action = unlink($pathname);

            }

            if ( $action === false ) throw new Exception("Error deleting ".$pathname." during recursive unlink of folder ".$folder);

        }

        $action = rmdir($folder);

        if ( $action === false ) throw new Exception("Error deleting folder ".$folder);

    }

}
