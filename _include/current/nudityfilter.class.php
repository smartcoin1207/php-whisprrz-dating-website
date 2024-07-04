<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

/**
* @author FreebieVectors.com
*
* Image nudity detertor based on flesh color quantity.
* Source: http://www.naun.org/multimedia/NAUN/computers/20-462.pdf
* J. Marcial-Basilio (2011), Detection of Pornographic Digital Images, International Journal of Computers
*/

class NudityFilter
{
    /**
    * Full path to the image file
    *
    * @var String
    */
	var $file;

    /**
     * Image GD PHP resource
     *
     * @var resource
     */
    var $resource;

    /**
    * Image information
    *
    * @var mixed
    */
	var $info;

    /**
     * Threshold of flesh color in image to consider in pornographic,
     * see page 302.
     *
     * @var float
     */
    var $threshold = .5;

    /**
     * Pixel count to iterate over. Too increase speed, set it higher and it will
     * skip some pixels.
     *
     * @var int
     */
    var $iteratorIncrement = 1;

    /**
     * Cb and Cr value bounds. See page 300
     *
     * @var array
     */
    var $boundsCbCr = array(80, 120, 133, 173);

    /**
     * Exclude white colors above this RGB color intensity
     *
     * @var int
     */
    var $excludeWhite = 250;

    /**
     * Exclude dark and black colors below this value
     *
     * @var int
     */
    var $excludeBlack = 5;

    /**
     * Quantify flesh color amount using YCbCr color model
     *
     * @return float
     */

    /**
     * Constructor
     *
     * @param string $file File path
     * @return Image
     */
    function __construct($file, $threshold, $resource = false)
    {
        $threshold = intval(trim($threshold));
        if($threshold > 100) {
            $threshold = 100;
        }
        if($threshold < 0) {
            $threshold = 100;
        }
        $this->threshold = $threshold / 100;
        if($resource) {
            $this->resource = $resource;
        } else {
            $this->file = $file;
            $this->info = getimagesize($file);
            $this->create();
        }
    }

    public function quantifyYCbCr()
    {

        // Init some vars
        $inc = $this->iteratorIncrement;
        $width = $this->width();
        $height = $this->height();
        list($Cb1, $Cb2, $Cr1, $Cr2) = $this->boundsCbCr;
        $white = $this->excludeWhite;
        $black = $this->excludeBlack;
        $total = $count = 0;

        for ($x = 0; $x < $width; $x += $inc)
            for ($y = 0; $y < $height; $y += $inc) {
                list($r, $g, $b) = $this->rgbXY($x, $y);

                // Exclude white/black colors from calculation, presumably background
                if ((($r > $white) && ($g > $white) && ($b > $white)) ||
                        (($r < $black) && ($g < $black) && ($b < $black)))
                    continue;

                // Converg pixel RGB color to YCbCr, coefficients already divided by 255
                $Cb = 128 + (-0.1482 * $r) + (-0.291 * $g) + (0.4392 * $b);
                $Cr = 128 + (0.4392 * $r) + (-0.3678 * $g) + (-0.0714 * $b);

                // Increase counter, if necessary
                if (($Cb >= $Cb1) && ($Cb <= $Cb2) && ($Cr >= $Cr1) && ($Cr <= $Cr2))
                    $count++;
                $total++;
            }

        return $count / $total;
    }

    /**
     * Check if image is of pornographic content
     *
     * @param float $threshold
     */
    public function isPorn($threshold = FALSE)
    {
        return $threshold === FALSE ? $this->quantifyYCbCr() >= $this->threshold : $this->quantifyYCbCr() >= $threshold;
    }

    /**
     * Create an image resource
     *
     */
    public function create()
    {
        switch ($this->info[2]) {
            case IMAGETYPE_JPEG:
                $this->resource = imagecreatefromjpeg($this->file);
                break;
            case IMAGETYPE_GIF:
                $this->resource = imagecreatefromgif($this->file);
                break;
            case IMAGETYPE_PNG:
                $this->resource = imagecreatefrompng($this->file);
                break;
            default:
                throw new Exception('Image type is not supported');
                break;
        }
    }

    /**
     * Get image width
     *
     * @return int Image width
     */
    public function width()
    {
        return imagesx($this->resource);
    }

    /**
     * Get image heights
     *
     * @return int Image height
     */
    public function height()
    {
        return imagesy($this->resource);
    }

    /**
     * Get color of a pixel
     *
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @return int
     */
    public function colorXY($x, $y)
    {
        return imagecolorat($this->resource, $x, $y);
    }

    /**
     * Returns RGB array of pixel's color
     *
     * @param int $x
     * @param int $y
     */
    public function rgbXY($x, $y)
    {
        $color = $this->colorXY($x, $y);
        return array(($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF);
    }

    /**
     * Destroy the image resource / close the file
     *
     */
    public function close()
    {
        imagedestroy($this->resource);
    }

}
