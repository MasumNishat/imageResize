<?php
namespace MasumNishat\imageResize;
//todo: final size is wrong
class imageResize {
    public static $targetSize = 250000;
    public static $tempDir = '';
    public static $dimension = [];
    public static $originalSize;
    public static $size;

    private static $minPercent = 20;
    private static $currentPercent;
    private static $intervalPercent = 20;
    private static $originalFile = '';
    private static $targetFile = '';
    private static $ext;
    private static $testFile = [
        'tmp-1',
        'tmp-2',
        'tmp-3'
    ];



    /**
     * @param $file
     * @param $target
     */
    public static function convert ($file, $target){
        if (self::$tempDir == ''){
            $tempDirDelete = true;
        } else {
            $tempDirDelete = false;
        }
        self::$originalFile = $file;
        self::$tempDir = microtime().rand(0, 1000);
        self::$targetFile = $target;
        @mkdir(self::$tempDir);
        self::$originalSize = filesize(self::$originalFile);
        if (self::$originalSize>self::$targetSize){
            self::$currentPercent = self::$minPercent;
            self::compress();
        } else {
            self::$dimension = getimagesize(self::$originalFile);
            self::getExt();
            copy(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2].self::$ext, self::$targetFile.self::$ext);
        }
        if ($tempDirDelete){
            self::delete_files( self::$tempDir );
        }
    }

    private static function delete_files($target) {
        if(is_dir($target)){
            $files = glob( $target .DIRECTORY_SEPARATOR. '*', GLOB_MARK );
            foreach( $files as $file ){
                self::delete_files( $file );
            }
            rmdir( $target );
        } elseif(is_file($target)) {
            unlink( $target );
        }
    }

    private static function getExt(){
        $parts = explode('.', self::$targetFile);
        $ext = array_pop($parts);
        self::$ext = (self::$dimension['mime'] == 'image/jpeg'? ".jpg" : (self::$dimension['mime'] == 'image/png'? '.png':'.gif'));
        //check if there is extension and also use case sensitivity
        if ('.'.strtolower($ext) == self::$ext){
            self::$ext = '';
        }
    }

    private static function compress (){
        for (self::$currentPercent;self::$currentPercent<100; self::$currentPercent=self::$currentPercent+self::$intervalPercent){
            clearstatcache();
            self::$dimension = getimagesize(self::$originalFile);
            self::getExt();
            $width = self::$dimension[0]*(100-self::$currentPercent)/100;
            self::resize($width, self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[0], self::$originalFile);
            self::$size = filesize(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[0].self::$ext);
            if (self::$size<self::$targetSize){
                clearstatcache();
                self::$currentPercent = self::$currentPercent - (self::$intervalPercent/2);
                self::$dimension = getimagesize(self::$originalFile);
                $width = self::$dimension[0]*(100-self::$currentPercent)/100;
                self::resize($width, self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[1], self::$originalFile);
                self::$size = filesize(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[1].self::$ext);
                if (self::$size < self::$targetSize){
                    clearstatcache();
                    self::$currentPercent = self::$currentPercent - (self::$intervalPercent/4);
                    self::$dimension = getimagesize(self::$originalFile);
                    $width = self::$dimension[0]*(100-self::$currentPercent)/100;
                    self::resize($width, self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2], self::$originalFile);
                    self::$size = filesize(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2].self::$ext);
                    if (self::$size < self::$targetSize){
                        copy(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2].self::$ext, self::$targetFile.self::$ext);
                        return true;
                    } else {
                        copy(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[1].self::$ext, self::$targetFile.self::$ext);
                        return true;
                    }
                } else {
                    clearstatcache();
                    self::$currentPercent = self::$currentPercent - (self::$intervalPercent*3/4);
                    self::$dimension = getimagesize(self::$originalFile);
                    $width = self::$dimension[0]*(100-self::$currentPercent)/100;
                    self::resize($width, self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2], self::$originalFile);
                    self::$size = filesize(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2].self::$ext);
                    if (self::$size < self::$targetSize){
                        copy(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[2].self::$ext, self::$targetFile.self::$ext);
                        return true;
                    } else {
                        copy(self::$tempDir.DIRECTORY_SEPARATOR.self::$testFile[0].self::$ext, self::$targetFile.self::$ext);
                        return true;
                    }
                }
            } else {
                continue;
            }
        }
        return false;
    }

    private static function resize ($newWidth, $targetFile, $originalFile) {
        list($width, $height) = self::$dimension;
        $newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        $mime = self::$dimension['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $c_param = [
                    $originalFile
                ];
                $image_save_func = 'imagejpeg';
                $param = [
                    $tmp,
                    $targetFile.self::$ext,
                    100
                ];
                break;
            case 'image/png':
                //todo: transparency support
                $image_create_func = 'imagecreatefrompng';
                $c_param = [
                    $originalFile
                ];
                $image_save_func = 'imagepng';
                $param = [
                    $tmp,
                    $targetFile.self::$ext,
                    9,
                    PNG_ALL_FILTERS
                ];
                break;
            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $c_param = [
                    $originalFile
                ];
                $image_save_func = 'imagegif';
                $param = [
                    $tmp,
                    $targetFile.self::$ext,
                ];
                break;
            default:
                print_r('Unknown image type.');
        }
        $img = call_user_func_array($image_create_func, $c_param);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        call_user_func_array ($image_save_func, $param);
    }
}