<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Image
{
	var $image = null;
	var $image_type = 2;
    var $transformation = true;
    var $width = 0;
    var $height = 0;
    var $imageCopy = false;
    var $saveImageResourceCopy = false;
    var $firstImageResource = true;

    public static $mimeTypesImg = 'image';

    public static function isValid($path)
    {
        if ($path == '' || (!file_exists($path) && !url_file_exists($path))) {
            return false;
        }

        $img_sz = @getimagesize($path);

        if (!is_array($img_sz)) {
            return false;
        }

        $mime = false;
        $isMimeExt = false;

        if (version_compare(PHP_VERSION, '5.3.0', '>=') && extension_loaded('fileinfo')){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $path);
            $isMimeExt = true;
        } else {
            if (function_exists('mime_content_type')){
                $mime  = mime_content_type($path);
                $isMimeExt = true;
            }
        }

        if ($isMimeExt && (!$mime || !preg_match('/' . self::$mimeTypesImg . '/', $mime))){

            return false;
        }

        return true;
    }

	function loadImage($path)
	/*
	This function loads the image you want to resize.
	The function will return TRUE on a succes and FALSE on failure
	*/
	{
	    if ( $this->image ) {
	        imagedestroy($this->image);
	    }
		//popcorn modified s3 bucket photo 2024-05-05
        if (!file_exists($path) && !url_file_exists($path)) {
            return false;
        }

	    $img_sz = custom_getimagesize($path);

        if (!is_array($img_sz)) {
            return false;
        }

        $this->transformation = true;

	    switch( $img_sz[2] ){
	        case 1:
	            $this->image_type = "GIF";
	            if ( !($this->image = imageCreateFromGif($path)) ) {
	                return FALSE;
	            } else {
	                return TRUE;
	            }
	            break;
	        case 2:
	            $this->image_type = "JPG";
	            if ( !($this->image = imageCreateFromJpeg($path)) ) {
	                return FALSE;
	            } else {

                    if(function_exists('exif_read_data')) {
                        $exif = @exif_read_data($path);
                        if($exif && isset($exif['Orientation'])) {
                            switch($exif['Orientation']) {
                                case 2:
                                    imageflip($this->image, IMG_FLIP_HORIZONTAL);
                                    break;
                                case 3:
                                    $this->image = imagerotate($this->image, 180, 0);
                                    break;
                                case 4:
                                    imageflip($this->image, IMG_FLIP_VERTICAL);
                                    break;
                                case 5:
                                    imageflip($this->image, IMG_FLIP_HORIZONTAL);
                                    $this->image = imagerotate($this->image, 90, 0);
                                    break;
                                case 6:
                                    $this->image = imagerotate($this->image, 270, 0);
                                    break;
                                case 7:
                                    imageflip($this->image, IMG_FLIP_HORIZONTAL);
                                    $this->image = imagerotate($this->image, 270, 0);
                                    break;
                                case 8:
                                    $this->image = imagerotate($this->image, 90, 0);
                                    break;
                            }
                        }
                    }

	                return TRUE;
	            }
	            break;
	        case 3:
	            $this->image_type = "PNG";
	            if ( !($this->image = imageCreateFromPng($path)) ) {
	                return FALSE;
	            } else {
	                return TRUE;
	            }
	            break;
	        case 4:
	            $this->image_type = "SWF";
	            if ( !($this->image = imageCreateFromSwf($path)) ) {
	                return FALSE;
	            } else {
	                return TRUE;
	            }
	            break;
                case 17:
	            $this->image_type = "ICO";
                    return FALSE;
	            break;
	        default:
	            return FALSE;
	    }
	}

	function saveImage($path, $quality, $type = 'jpeg')
	/*
	Save the image, to either a new or an already existing file.
	The quality setting determines the quality of the image (0 to 100).
	The function will return TRUE on a succes and FALSE on failure
	*/
	{
	    if (!$this->image) {
            return FALSE;
	    }
        if ($this->transformation) {
            $imageNew = ImageCreateTrueColor(imageSX($this->image), imageSY($this->image));
            $white = imagecolorallocate($imageNew, 255, 255, 255);
            imagefill($imageNew, 0, 0, $white);
            imageCopy($imageNew, $this->image, 0, 0, 0, 0, imageSX($this->image), imageSY($this->image));
            imageDestroy($this->image);
            $this->image = $imageNew;
        }
	    $fp = fopen($path, "w");
	    if (!$fp) {
	        return FALSE;
	    } else {
	        fclose($fp);
	        @chmod($path, 0777);
			if ($type == 'gif' && $this->image_type == 'GIF') {
                if (!imagegif($this->image, $path)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
            /*if ($this->image_type == 'PNG') {
                $quality = round($quality/10, 0);
                if ($quality == 10) {
                    $quality = 9;
                }
                if (!imagepng($this->image, $path, $quality)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {*/
                if (!imageJpeg($this->image, $path, $quality)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            //}
			}
	    }
	}

	function clearImage()
	/*
	Clears the memory used for the image (if it is loaded)
	This function is required to ensure that there are no memory leaks!
	you have to run this function after your resizing is complete.
	The function always returns TRUE
	*/
	{
	    if ($this->image) {
	        imagedestroy($this->image);
	        $this->image = FALSE;
	        return TRUE;
	    } else {
	        return TRUE;
	    }
	}

	function getWidth()
	/*
	returns the width of the loaded image
	*/
	{
	    if (!$this->image) {
	        return 0;
	    }
	    return imageSX($this->image);
	}

	function getHeight()
	/*
	returns the Height of the loaded image
	*/
	{
	    if (!$this->image) {
	        return 0;
	    }
	    return imageSY($this->image);
	}

    function setPngParams($imageNew)
    {
        $white = imagecolorallocate($imageNew, 255, 255, 255);
		$width = imagesx($imageNew);
		$height = imagesy($imageNew);

		imagefill($imageNew, 0, 0, $white);
    }

	function resizeW($newWidth)
	/*
	resizes an image accordingly to fit the newWidth and remain proportions
	*/
	{
	    if (!$this->image) {
	        return FALSE;
	    }
	    $oldWidth = imageSX($this->image);
	    $oldHeight = imageSY($this->image);
	    $newHeight = ($oldHeight / ($oldWidth / $newWidth));

	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
	    imageDestroy($this->image);
	    $this->image = $imageNew;
        $this->transformation = false;
	    return TRUE;
	}

	function resizeH($newHeight)
	/*
	resizes an image accordingly to fit the newHeight and remain proportions
	*/
	{
	    if (!$this->image) {
	        return FALSE;
	    }
	    $oldWidth = imageSX($this->image);
	    $oldHeight = imageSY($this->image);
	    $newWidth = ($oldWidth / ($oldHeight / $newHeight));

	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
	    imageDestroy($this->image);
	    $this->image = $imageNew;
        $this->transformation = false;
	    return TRUE;
	}

	function resizeWH($maxWidth, $maxHeight, $stretch=FALSE, $str = "", $font_size = 0, $wm_image = '', $wm_position = '', $watermark = true)
	/*
	resizes an image accordingly to fit the maxWidth and maxHeight.
	By default it retains proportions. If however stretch is set to TRUE it
	will stretch the image to the maxWidth and maxHeight.
	*/
	{
        global $g;

	    if (!$this->image) {
	        return FALSE;
	    }
	    $oldWidth = imageSX($this->image);
	    $oldHeight = imageSY($this->image);
		$newWidth= $oldWidth;
	    $newHeight= $oldHeight;

		if ($oldWidth > $maxWidth || $oldHeight > $maxHeight) {

			$newWidth= $maxWidth;
			$newHeight= $maxHeight;

			if (!$stretch) {
				$ratio = $oldWidth / $oldHeight;
				if (($maxWidth  / $maxHeight) < $ratio) {
					$newHeight = ($oldHeight / ($oldWidth / $maxWidth));
				} else {
					$newWidth = ($oldWidth / ($oldHeight / $maxHeight));
				}
			}
		}

        $this->width = $newWidth;
        $this->height = $newHeight;

	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		$dest=$imageNew;

        if($this->saveImageResourceCopy) {
            $this->imageCopy = $this->copyImageResource($dest);
        }

		$w_dest = $newWidth;
		$h_dest = $newHeight;

        if (Common::getOption('watermark_type','image')=='image' && $watermark){
            $watermarkFile = '';
            $watermarkPosition = '';
            if(Common::getOption('watermark_type','image')=='image'){
                $watermark_font_size=0;
                $watermarkFile = $g['path']['dir_files'].'watermark.png';
                if(!file_exists($watermarkFile)){
                    $watermarkFile = '';
                }else{
                    $watermarkPosition = Common::getOption('watermark_position', 'image');
                }
            }
            if($watermarkFile != ''){
                $font_size = 0;
                $wm_image = $watermarkFile;
                $wm_position = $watermarkPosition;
            }
        }

		if ($font_size>0)
		{
			$size = $font_size; // ������ ������
			$x_text = $w_dest-imagefontwidth($size)*strlen($str)-3;
			$y_text = $h_dest-imagefontheight($size)-3;

			// ���������� ����� ������ �� ����� ���� �������� �����
			$white = imagecolorallocate($dest, 255, 255, 255);
			$black = imagecolorallocate($dest, 0, 0, 0);
			$gray = imagecolorallocate($dest, 127, 127, 127);

            $color = $white;

			if ($x_text > 0 and $y_text > 0) {
				if (imagecolorat($dest,$x_text,$y_text)>$gray) {
                    $color = $black;
                }
				if (imagecolorat($dest,$x_text,$y_text)<$gray) {
                    $color = $white;
                }
			}

			// ������� �����
			imagestring($dest, $size, $x_text-1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text+1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text-1, $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text-1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text,   $y_text,   $str,$color);
		}

        if($wm_image!='' && file_exists($wm_image)){
            $filter = @imagecreatefrompng($wm_image);
            $wmX=0;
            $wmY=0;
            $wmSize = custom_getimagesize($wm_image);
            if($wm_position=='bottom_right'){
                $wmX = $newWidth - $wmSize[0]-10;
                $wmY = $newHeight - $wmSize[1]-10;
            } elseif($wm_position=='top_right'){
                $wmX = $newWidth - $wmSize[0]-10;
                $wmY = 10;
            } elseif($wm_position=='bottom_left'){
                $wmX = 10;
                $wmY = $newHeight - $wmSize[1]-10;
            } elseif($wm_position=='top_left'){
                $wmX = 10;
                $wmY = 10;
            }


            if($filter){
                $watermarkWidth = $wmSize[0];
                $watermarkHeight = $wmSize[1];
                imagealphablending($dest, true);
                imagecopyresampled($dest, $filter, $wmX, $wmY, 0, 0, $watermarkWidth,$watermarkHeight, $watermarkWidth, $watermarkHeight);
                //imagecopymerge($dest, $filter, $wmX, $wmY, 0, 0, $watermarkWidth,$watermarkHeight, 70);
            }
        }

	    imageDestroy($this->image);
	    $this->image = $imageNew;
        $this->transformation = false;
	    return TRUE;
	}

	function resizeCropped($width, $height, $str = '', $font_size = 0, $originXY = false, $detectFace=false)
	/*
	resizes an image to the width and height arguments.
	If needed parts of the image will be cropped to remain proportions
	** note this is a function I quickly hacked in, I hope it works ok,
	    but I haven't had a chance to properly test it, or to review the code I wrote here yet
	*/
	{
	    if (!$this->image) {
	        return FALSE;
	    }
	    $oldWidth = imageSX($this->image);
	    $oldHeight = imageSY($this->image);

		$newWidth = $oldWidth;
		$newHeight = $oldHeight;
		$srcX = 0;
		$srcY = 0;

		if ($oldWidth > $width || $oldHeight > $height) {
			$ratioW = $oldWidth / $width;
			$ratioH = $oldHeight / $height;
            $srcX=0;

            if(!$originXY && $detectFace){

                include_once('./_include/lib/face_detection/FaceDetector.php');

                $detector = new svay\FaceDetector('detection.dat');
                $detector->faceDetect($this->image);
                $face=$detector->getFace();
                if(is_array($face)){
                    $srcX=round(($face['x']+$face['w']/2) - $oldWidth/2);
                }
            }

			if ($ratioH > $ratioW) {
				// some parts from the height will have to be cut off
				$newWidth = $oldWidth;
				$newHeight = $height * $ratioW;
				//$srcX = 0;
				//$srcY = 0;
				#$srcY = +($oldHeight - $newHeight) / 2;
			} else {
				// some parts from the width will have to be cut off
				$newWidth = $width * $ratioH;
				$newHeight = $oldHeight;
				#$srcX = 0;
				//$srcX = ($originXY == false) ? +($oldWidth - $newWidth) / 2 : 0;

                if($srcX>($oldWidth - $newWidth) / 2){
                    $srcX=($oldWidth - $newWidth) / 2;
                } elseif($srcX<-(($oldWidth - $newWidth) / 2)){
                    $srcX=0;
                }
                $srcX+=($oldWidth - $newWidth) / 2;

				$srcY = 0;
			}
            if($originXY){
                $srcX = 0;
            }
		}
	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, $srcX, $srcY, $oldWidth, $oldHeight, $oldWidth, $oldHeight);

		if ($oldWidth > $width || $oldHeight > $height) {
			imageDestroy($this->image);
			$this->image = $imageNew;
			// Now we are actually going to resample the image to the correct size
			$oldWidth = $newWidth;
			$oldHeight = $newHeight;
			$newWidth = $width;
			$newHeight = $height;

			$imageNew = ImageCreateTrueColor($newWidth, $newHeight);
			$this->setPngParams($imageNew);
			imageCopyResampled($imageNew, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
		}

		$dest=$imageNew;

		$w_dest = $newWidth;
		$h_dest = $newHeight;

		if ($font_size>0)
		{
			$size = $font_size; // ������ ������
			$x_text = $w_dest-imagefontwidth($size)*strlen($str)-3;
			$y_text = $h_dest-imagefontheight($size)-3;

			// ���������� ����� ������ �� ����� ���� �������� �����
			$white = imagecolorallocate($dest, 255, 255, 255);
			$black = imagecolorallocate($dest, 0, 0, 0);
			$gray = imagecolorallocate($dest, 127, 127, 127);
			if (imagecolorat($dest,$x_text,$y_text)>$gray) $color = $black;
			if (imagecolorat($dest,$x_text,$y_text)<$gray) $color = $white;

			// ������� �����
			imagestring($dest, $size, $x_text-1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text+1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text-1, $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text-1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text,   $y_text,   $str,$color);
		}

	    imageDestroy($this->image);
	    $this->image = $imageNew;
        $this->transformation = false;
	    return TRUE;
	}
	function resizeCroppedMiddle($width, $height, $str = '', $font_size = 0)
	/*
	resizes an image to the width and height arguments.
	If needed parts of the image will be cropped to remain proportions
	** note this is a function I quickly hacked in, I hope it works ok,
	    but I haven't had a chance to properly test it, or to review the code I wrote here yet
	*/
	{
	    if (!$this->image) {
	        return FALSE;
	    }
	    $oldWidth = imageSX($this->image);
	    $oldHeight = imageSY($this->image);

	    $ratioW = $oldWidth / $width;
	    $ratioH = $oldHeight / $height;
	    if ($ratioH > $ratioW) {
	        // some parts from the height will have to be cut off
	        $newWidth = $oldWidth;
	        $newHeight = $height * $ratioW;
	        $srcX = 0;
	        //$srcY = 0;
	        $srcY = +($oldHeight - $newHeight) / 2;
	    } else {
	        // some parts from the width will have to be cut off
	        $newWidth = $width * $ratioH;
	        $newHeight = $oldHeight;
	        #$srcX = 0;
	        $srcX = +($oldWidth - $newWidth) / 2;
	        $srcY = 0;
	    }
	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, $srcX, $srcY, $oldWidth, $oldHeight, $oldWidth, $oldHeight);
	    imageDestroy($this->image);
	    $this->image = $imageNew;

	    // Now we are actually going to resample the image to the correct size
	    $oldWidth = $newWidth;
	    $oldHeight = $newHeight;
	    $newWidth = $width;
	    $newHeight = $height;

	    $imageNew = ImageCreateTrueColor($newWidth, $newHeight);
        $this->setPngParams($imageNew);
	    imageCopyResampled($imageNew, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		$dest=$imageNew;

        if($this->saveImageResourceCopy) {
            $this->imageCopy = $this->copyImageResource($dest);
        }

		$w_dest = $newWidth;
		$h_dest = $newHeight;

		if ($font_size>0)
		{
			$size = $font_size; // ������ ������
			$x_text = $w_dest-imagefontwidth($size)*strlen($str)-3;
			$y_text = $h_dest-imagefontheight($size)-3;

			// ���������� ����� ������ �� ����� ���� �������� �����
			$white = imagecolorallocate($dest, 255, 255, 255);
			$black = imagecolorallocate($dest, 0, 0, 0);
			$gray = imagecolorallocate($dest, 127, 127, 127);
			if (imagecolorat($dest,$x_text,$y_text)>$gray) $color = $black;
			if (imagecolorat($dest,$x_text,$y_text)<$gray) $color = $white;

			// ������� �����
			imagestring($dest, $size, $x_text-1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text+1, $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text-1, $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text-1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text+1, $y_text,   $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text-1, $str,$white-$color);
			imagestring($dest, $size, $x_text,   $y_text+1, $str,$white-$color);

			imagestring($dest, $size, $x_text,   $y_text,   $str,$color);
		}

	    imageDestroy($this->image);
	    $this->image = $imageNew;
        $this->transformation = false;
	    return TRUE;
	}
    function cropped($width, $height, $originXY = true)
    {
        $this->resizeCropped($width, imageSY($this->image), '', 0, $originXY);
        $this->resizeCropped($width, $height, '', 0, $originXY);
    }

    static function imageReplaceColor(&$src, array  $rgb){
        imageAlphaBlending($src, false);
        imageSaveAlpha($src, true);
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        for($x = 0; $x < $srcW; $x++){
            for($y = 0; $y < $srcH; $y++){
                $srcColor = imagecolorsforindex($src, imagecolorat ($src, $x, $y));
                $srcColor = imagecolorallocatealpha($src, $rgb[0], $rgb[1], $rgb[2], $srcColor['alpha']);
                imagesetpixel ($src, $x, $y, $srcColor);
            }
        }
    }

    static function copyImageResource($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $copy = imagecreatetruecolor($width, $height);
        imagecopy($copy, $image, 0, 0, 0, 0, $width, $height);

        return $copy;
    }

    function prepareImageResource($file)
    {
        if($this->firstImageResource) {
            if((memory_get_usage() * 2 + 1024 * 1024 * 16) < getMemoryLimitInBytes()) {
                $this->imageCopy = self::copyImageResource($this->image);
            } else {
                // use only if need prevent loading image from drive when low memory
                // $image->saveImageResourceCopy = true;
            }
        } else {
            if($this->imageCopy) {
                $this->image = $this->imageCopy;
            } else {
                $this->loadImage($file);
            }
            $this->saveImageResourceCopy = true;
        }
        $this->firstImageResource = false;
    }

    function cleanFirstImageResource()
    {
        $this->firstImageResource = true;
        $this->imageCopy = false;
    }

	static function isAnimatedGif($file, $chekGif = false)
	{
		$fp = null;

		if (is_string($file)) {
			$fp = fopen($file, "rb");
		} else {
			$fp = $file;
			fseek($fp, 0);
		}

        $frames = 0;

        if($fp) {

            if (fread($fp, 3) !== "GIF") {
                fclose($fp);
                return false;
            }

            if ($chekGif) {
                // disabled - incorrect detect for animated gif
                //return true;
            }

            //an animated gif contains multiple "frames", with each frame having a
            //header made up of:
            // * a static 4-byte sequence (\x00\x21\xF9\x04)
            // * 4 variable bytes
            // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
            // We read through the file til we reach the end of the file, or we've found
            // at least 2 frame headers
            $chunk = false;
            while (!feof($fp) && $frames < 2) {
                //add the last 20 characters from the previous string, to make sure the searched pattern is not split.
                $chunk = ($chunk ? substr($chunk, -20) : '') . fread($fp, 1024 * 100); //read 100kb at a time
                $frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
            }

            fclose($fp);
        }

		return $frames > 1;
	}


}