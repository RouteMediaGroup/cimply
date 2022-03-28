<?php
namespace
{
	/**
	 * @Author Michael Eckebrecht
	 */
	trait Secure
    {
        static function Add($secureFiles = []): void {
            foreach($secureFiles as $files) {
                (is_file($files)) 
                ? \Files::DeCryptFile($files) 
                : self::AddFiles($files);
            }            
        }
        static function AddFiles($secureDirectory = ''): void {
            if(!(empty($secureDirectory))) {
                $secureFiles  = \array_diff(\scandir($secureDirectory), ['..', '.']);
                foreach($secureFiles as $file) {
                    if(!(strpos($file,'.map'))) {
                        $filePath = $secureDirectory.DIRECTORY_SEPARATOR.$file;
                        self::Add([$filePath]);
                    }
                }
            }
        }
    }
}