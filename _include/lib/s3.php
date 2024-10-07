<?php

//popcorn created new library 2024-04-30
require 'vendor/autoload.php'; // Assuming you installed the AWS SDK for PHP using Composer

use Aws\S3\S3Client;
use PhpParser\Node\Stmt\TryCatch;

class S3ClientClass {
    static $bucketName = 'whisprrz_files';

    private static $s3;

    public static function getS3Client(): S3Client
    {
        if (self::$s3 === null) {
            self::$s3 = new S3Client([
                'endpoint'   => Common::getOption('api_endpoint', 'edge_media_files_settings'),
                'region'     => 'us-east-1', // Replace with the appropriate region for your S3 bucket
                'version'    => 'latest',
                'credentials' => [
                    'key'    => Common::getOption('access_key', 'edge_media_files_settings'),
                    'secret' => Common::getOption('secret_key', 'edge_media_files_settings'),
                ],
                'request.options' => [
                    'timeout' => 60, // Increase the timeout value (in seconds)
                ],
            ]);

            self::$bucketName = Common::getOption('bucket_name', 'edge_media_files_settings');
        }
        return self::$s3;
    }
    
    public static function getFileObject() {
        global $g;
        $fileName = $_GET['file'];

        $file_parts = explode("/", $fileName);

        if(Common::isOptionActive($file_parts[0] . '_directory', 'edge_media_files_settings')) {
            try {
                $objectKey = '_files/' . $fileName;
    
                $result = self::getS3Client()->getObject([
                    'Bucket' => self::$bucketName,
                    'Key'    => $objectKey,
                ]);
    
                // Set the content type header
                header('Content-Type: ' . $result['ContentType']);
    
                // Output the image data to the browser
                echo $result['Body'];
            } catch (\Throwable $th) {
                echo '';
            }
        } else {
            $filePath = $g['path']['dir_files'] . $fileName;
            $fileData = file_get_contents($filePath);

            // Set the appropriate content type header
            $contentType = mime_content_type($filePath);
            header("Content-Type: $contentType");

            // Echo the file data
            echo $fileData;
        }
    }

    public static function getFileDirectUrl(string $objectKey) {
        try {
            $cmd = self::getS3Client()->getCommand('GetObject', [
                'Bucket' => self::$bucketName,
                'Key'    => $objectKey,
            ]);

            $request = self::getS3Client()->createPresignedRequest($cmd, '+20 minutes'); // Generate a pre-signed URL valid for 10 minutes
            $url = $request->getUri();
            return $url;
        } catch (\Throwable $th) {
            return '';
        }
    }

    public static function get_file_direct_url($fileName) {
        $parts = explode('_files/', $fileName);
        if(isset($parts[1]) && !empty($parts[1])) {

            $objectKey = "_files/" . $parts[1];
            return self::getFileDirectUrl($objectKey);
        } else {
            return $fileName;
        }
    }

    public static function isObjectExists(string $objectKey): bool
    {
        try {
            self::getS3Client()->headObject([
                'Bucket' => self::$bucketName,
                'Key'    => $objectKey,
            ]);

            // echo $objectKey; 

            return true;
        } catch (\Aws\S3\Exception\S3Exception $e) {
            if ($e->getStatusCode() == 404) {
                return false;
            } else {
                // echo "Aws Request Error";
                return false;
            }
        }
    }

    public static function isFileExists($fileName)
    {
        if ($fileName) {
            $parts = explode('_files/', $fileName);
            if (isset($parts[1]) && !empty($parts[1])) {
                $objectKey = "_files/" . $parts[1];
                return self::isObjectExists($objectKey);
            } else {
                return file_exists($fileName);
            }
        } else {
            return false;
        }
    }
    
    public static function getObjectLastModified(string $objectKey)
    {
        try {
            $result = self::getS3Client()->headObject([
                'Bucket' => self::$bucketName,
                'Key'    => $objectKey,
            ]);

            return $result['LastModified']->getTimestamp();
        } catch (\Aws\S3\Exception\S3Exception $e) {
            if ($e->getStatusCode() == 404) {
                return null;
            } else {
                // throw $e;
                return null;
            }
        }
    }

    public static function getFilemtime($fileName) {
        if($fileName) {
            $parts = explode('_files/', $fileName);
            if(isset($parts[1]) && !empty($parts[1])) {
                $objectKey = "_files/" . $parts[1];
                
                return self::getObjectLastModified($objectKey);
            } {
                return filemtime($fileName);
            }
        } else {
            return false;
        }
    }

    /**
     * file upload to s3 storage
     * @param string $objectKey
     * @param string $file_path //file path of website storage
     */
    public static function uploadFile($file_path, $objectKey) {
        try {
            $result = self::getS3Client()->putObject([
                'Bucket' => self::$bucketName,
                'Key'    => $objectKey,
                'Body'   => fopen($file_path, 'r'),
                'ACL'    => 'public-read',
            ]);
            
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function uploadFilePrepare($file_path, $filename) {
        // var_dump($file_path, $filename); die();
        $parts = explode('_files/', $file_path);
        if(isset($parts[1]) && !empty($parts[1])) {
            if($filename) {
                $objectKey = "_files/" . $filename;
            } else {
                $objectKey = "_files/" . $parts[1];
            }

            $isUploaded =  self::uploadFile($file_path, $objectKey);
            if($isUploaded) {
                @unlink($file_path);
            }
        } else {
            return false;
        }
    }

    /**
     * delete file from s3 storage
     * @param $objectkey
     * @return boolean
     */

     public static function deleteFileFromS3($objectKey) {
        try {
            // $result = self::getS3Client()->deleteObject(self::$bucketName, $objectKey);

            $result = self::getS3Client()->deleteObject([
                'Bucket' => self::$bucketName,
                'Key' => $objectKey
            ]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
     }

     public static function deleteFileFromS3Prepare($filename) {
        $objectKey = "_files/" . $filename;
        return self::deleteFileFromS3($objectKey);
     }

    #copy s3 storage object
    public static function CopyObjectS3($source_key, $target_key) {
        try {
            $bucketName  = self::$bucketName;
            $result = self::getS3Client()->copyObject([
                'Bucket' => self::$bucketName,
                'Key' => $target_key,
                'CopySource' => "{$bucketName}/{$source_key}"
            ]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function CopyObjectS3Prepare($source_filename, $target_filename) {
        $source_parts = explode('_files/', $source_filename);
        $target_parts = explode('_files/', $target_filename);

        if((isset($source_parts[1]) && !empty($source_parts[1])) && (isset($target_parts[1]) && !empty($target_parts[1]))) {
            return self::CopyObjectS3( "_files/" . $source_parts[1], "_files/" . $target_parts[1]);
        }
    }

    #download file
    public static function downloadTempFile($objectKey, $target_path) {
        try {
            $result = self::getS3Client()->getObject([
                'Bucket' => self::$bucketName,
                'Key'    => $objectKey,
                'SaveAs' => $target_path
            ]);
            
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function downloadTempFilePrepare($filename) {
        $parts = explode('_files/', $filename);
        if(isset($parts[1]) && !empty($parts[1])) {
            $sub_parts = explode('/', $parts[1]);
            $objectKey = "_files/" . $parts[1];
            $new_path = $parts[0] . '_files/' . str_replace($sub_parts[0] . '/', "temp/" . time() . '_', $parts[1]);
            self::downloadTempFile($objectKey, $new_path);
            return $new_path;
        } else {
            return $filename;
        }
    }
}