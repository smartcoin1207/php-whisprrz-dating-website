<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Mp3
{

    private static $mp3HeaderLength = 4;
    private static $id3TagHeaderLength = 10;
    private static $id3v1TagLength = 128;
    private static $apeTagHeaderLength = 32;
    private static $fileHandle = null;
    private static $mp3headerInfo = array();
    private static $version = array(
        0x0 => '2.5', 0x1 => '0', 0x2 => '2', 0x3 => '1',
    );
    private static $layer = array(
        0x0 => '0', 0x1 => '3', 0x2 => '2', 0x3 => '1',
    );
    private static $bitrate = array(
        'V1L1' => array(0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448),
        'V1L2' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384),
        'V1L3' => array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320),
        'V2L1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256),
        'V2L2' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
        'V2L3' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
    );
    private static $frequency = array(
        '1' => array(44100, 48000, 32000),
        '2' => array(22050, 24000, 16000),
        '2.5' => array(11025, 12000, 8000),
    );
    private static $samples = array(
        1 => array(1 => 384, 2 => 1152, 3 => 1152,),
        2 => array(1 => 384, 2 => 1152, 3 => 576,),
    );

    public static function getDuration($file)
    {
        $duration = 0;

        if (!custom_file_exists($file) || !(self::$fileHandle = @fopen($file, 'rb'))) {
            return $duration;
        }

        $bitrates = array();
        $bitratesCount = 0;

        $audioLastByte = self::getAudioLastByte(filesize($file));
        self::setFilePointerToOffset(0);

        $id3v2TagHeader = fread(self::$fileHandle, self::$id3TagHeaderLength);
        $audioStart = self::skipID3v2Tag($id3v2TagHeader);

        self::setFilePointerToOffset($audioStart);

        $offset = self::getStartOffset($audioStart);

        while (ftell(self::$fileHandle) <= $audioLastByte && !feof(self::$fileHandle)) {

            $mp3Header = fread(self::$fileHandle, self::$mp3HeaderLength);

            if (strlen($mp3Header) < self::$mp3HeaderLength) {
                break;
            } elseif (self::isValidMp3Header($mp3Header)) {

                $mp3HeaderInfo = self::$mp3headerInfo;

                $offset = $mp3HeaderInfo['frame_size'] - self::$mp3HeaderLength;

                self::setFilePointerFromCurrentOffset($offset);

                $mp3HeaderNext = fread(self::$fileHandle, self::$mp3HeaderLength);

                if(ftell(self::$fileHandle) >= $audioLastByte) {
                    break;
                }

                if(self::isValidMp3Header($mp3HeaderNext)) {
                    self::setFilePointerFromCurrentOffset(-self::$mp3HeaderLength);
                } else {
                    continue;
                }

                if($mp3HeaderInfo['bitrate'] > 0) {
                    $bitrates[] = $mp3HeaderInfo['bitrate'];
                    $bitratesCount++;
                }
            } elseif (substr($mp3Header, 0, 3) == 'TAG') {
                break;
            } else {
                self::searchMp3HeaderFromNextByte();
            }
        }

        fclose(self::$fileHandle);

        if($bitratesCount) {
            $bitrate = 1000 * array_sum($bitrates) / $bitratesCount;
            if($bitrate > 0) {
                $duration = ($audioLastByte - $audioStart) * 8 / $bitrate;
            }
        }

        return $duration;
    }

    private static function isValidMp3Header($header)
    {
        $isValid = false;

        if($header[0] === "\xFF" && (ord($header[1]) & 0xE0)) {
            self::$mp3headerInfo = self::getFrameHeaderInfo($header);
            $isValid = (self::$mp3headerInfo['bitrate'] !== -1 && self::$mp3headerInfo['frequency'] !== -1 && !empty(self::$mp3headerInfo['frame_size']) && self::$mp3headerInfo['frame_size'] !== self::$mp3HeaderLength && self::$mp3headerInfo['samples'] !== -1);
        }

        return $isValid;
    }

    private static function getStartOffset($startOffset = 0)
    {
        // find 2 correct frames
        self::setFilePointerToOffset($startOffset);

        while (!feof(self::$fileHandle)) {

            $mp3Header = fread(self::$fileHandle, self::$mp3HeaderLength);

            if (strlen($mp3Header) < self::$mp3HeaderLength) {
                break;
            } else if (self::isValidMp3Header($mp3Header)) {

                $startOffset = ftell(self::$fileHandle) - self::$mp3HeaderLength;

                $offset = self::$mp3headerInfo['frame_size'] - self::$mp3HeaderLength;
                self::setFilePointerFromCurrentOffset($offset);

                if(!feof(self::$fileHandle)) {
                    $mp3Header = fread(self::$fileHandle, self::$mp3HeaderLength);

                    if(self::isValidMp3Header($mp3Header)) {
                        self::setFilePointerToOffset($startOffset);
                        break;
                    } else {
                        $newOffset = 1 - $offset - 2 * self::$mp3HeaderLength;
                        self::setFilePointerFromCurrentOffset($newOffset);
                    }
                }
            } else if (substr($mp3Header, 0, 3) == 'TAG') {
                break;
            } else {
                self::searchMp3HeaderFromNextByte();
            }
        }

        return $startOffset;
    }

    private static function searchMp3HeaderFromNextByte()
    {
        self::setFilePointerFromCurrentOffset(1 - self::$mp3HeaderLength);
    }

    private static function setFilePointerFromCurrentOffset($offset)
    {
        fseek(self::$fileHandle, $offset, SEEK_CUR);
    }

    private static function setFilePointerToOffset($offset)
    {
        fseek(self::$fileHandle, $offset, SEEK_SET);
    }

    private static function bytesToInteger($bytes, $zeroFirstBit = false)
    {
        $integer = 0;

        $bytesLength = strlen($bytes);

		for ($i = 0; $i < $bytesLength; $i++) {
			if ($zeroFirstBit) {
				$integer += (ord($bytes[$i]) & 0x7F) * pow(2, ($bytesLength - 1 - $i) * 7);
			} else {
				$integer += ord($bytes[$i]) * pow(256, ($bytesLength - 1 - $i));
			}
		}

        return $integer;
    }

    private static function skipID3v2Tag($tag)
    {
        $tagEndPosition = 0;

        if (strlen($tag) === self::$id3TagHeaderLength && substr($tag, 0, 3) === 'ID3') {

            $tagSize = self::bytesToInteger(substr($tag, 6), true);

            $id3v2Flags = ord($tag[5]);
            $id3FooterSize = ($id3v2Flags & 0x10) ? 10 : 0;

            $tagEndPosition = self::$id3TagHeaderLength + $tagSize + $id3FooterSize;

        }
        return $tagEndPosition;
    }

    public static function getFrameHeaderInfo($header)
    {
        $byteVersionLayer = ord($header[1]);
        $byteBitrateFrequencyPaddingBit = ord($header[2]);

        $version = self::$version[($byteVersionLayer & 0x18) >> 3];
        $versionOfficial = ($version == '2.5' ? 2 : $version);

        $layer = self::$layer[($byteVersionLayer & 0x06) >> 1];

        $bitrateKey = "V{$versionOfficial}L{$layer}";
        $bitrateIndex = ($byteBitrateFrequencyPaddingBit & 0xf0) >> 4;
        $bitrate = isset(self::$bitrate[$bitrateKey][$bitrateIndex]) ? self::$bitrate[$bitrateKey][$bitrateIndex] : -1;

        $frequencyIndex = ($byteBitrateFrequencyPaddingBit & 0x0c) >> 2;
        $frequency = isset(self::$frequency[$version][$frequencyIndex]) ? self::$frequency[$version][$frequencyIndex] : -1;
        $paddingBit = ($byteBitrateFrequencyPaddingBit & 0x02) >> 1;

        $frameHeaderInfo = array();
        $frameHeaderInfo['bitrate'] = $bitrate;
        $frameHeaderInfo['frequency'] = $frequency;
        $frameHeaderInfo['frame_size'] = self::getFrameSize($layer, $bitrate, $frequency, $paddingBit);
        $frameHeaderInfo['samples'] = isset(self::$samples[$versionOfficial][$layer]) ? self::$samples[$versionOfficial][$layer] : -1;

        return $frameHeaderInfo;
    }

    private static function getFrameSize($layer, $bitrate, $frequency, $paddingBit)
    {
        $frameSize = 0;

        if($frequency) {
            if ($layer == 1) {
                $frameSize = intval(((12 * $bitrate * 1000 / $frequency) + $paddingBit) * 4);
            } else {
                $frameSize = intval(((144 * $bitrate * 1000) / $frequency) + $paddingBit);
            }
        }

        return $frameSize;
    }

    private static function getAudioLastByte($audioLastByte)
    {
        if(self::isId3v1TagAtOffset(-self::$id3v1TagLength)) {
            $audioLastByte -= self::$id3v1TagLength;
            if(self::isId3v1TagAtOffset(-self::$id3v1TagLength * 2)) {
                $audioLastByte -= self::$id3v1TagLength;
            }
        }

        $apeTagStart = self::getApeTagStart($audioLastByte);

        if($apeTagStart != $audioLastByte) {
            $audioLastByte = $apeTagStart;
        }

        return $audioLastByte;
    }

    private static function isId3v1TagAtOffset($offset)
    {
        $isId3v1TagAtOffset = false;

        fseek(self::$fileHandle, $offset, SEEK_END);
        $tag = fread(self::$fileHandle, 3);
        if($tag === 'TAG') {
            $isId3v1TagAtOffset = true;
        }

        return $isId3v1TagAtOffset;
    }

    private static function getApeTagStart($position)
    {
        $tagHeaderOffset = $position - self::$apeTagHeaderLength;

        fseek(self::$fileHandle, $tagHeaderOffset, SEEK_SET);

        $apeTagFooter = fread(self::$fileHandle, self::$apeTagHeaderLength);

        if ($apeTagFooter && strlen($apeTagFooter) === self::$apeTagHeaderLength && substr($apeTagFooter, 0, 8) === 'APETAGEX') {
            $apeTagLengthBytes = strrev(substr($apeTagFooter, 12, 4));
            $apeTagLength = self::bytesToInteger($apeTagLengthBytes);
            $position -= $apeTagLength + self::$apeTagHeaderLength;
        }

        return $position;
    }

}
