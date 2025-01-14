<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

function resize_then_crop( $filein,$fileout,$imagethumbsize_w,$imagethumbsize_h,$red,$green,$blue)
{

// Get new dimensions
list($width, $height) = custom_getimagesize($filein);
$new_width = $width * $percent;
$new_height = $height * $percent;

   if(preg_match("/.jpg/i", "$filein"))
   {
       $format = 'image/jpeg';
   }
   if (preg_match("/.gif/i", "$filein"))
   {
       $format = 'image/gif';
   }
   if(preg_match("/.png/i", "$filein"))
   {
       $format = 'image/png';
   }

       switch($format)
       {
           case 'image/jpeg':

            if(@$image = imagecreatefromjpeg($filein)){
             }else{
              @$image = imagecreatefromgif($filein);
             }

//           $image = imagecreatefromjpeg($filein);
           break;
           case 'image/gif';
           $image = imagecreatefromgif($filein);
           break;
           case 'image/png':
           $image = imagecreatefrompng($filein);
           break;
       }

$width = $imagethumbsize_w ;
$height = $imagethumbsize_h ;
list($width_orig, $height_orig) = custom_getimagesize($filein);

if ($width_orig < $height_orig) {
  $height = ($imagethumbsize_w / $width_orig) * $height_orig;
} else {
   $width = ($imagethumbsize_h / $height_orig) * $width_orig;
}

if ($width < $imagethumbsize_w)
//if the width is smaller than supplied thumbnail size
{
$width = $imagethumbsize_w;
$height = ($imagethumbsize_w/ $width_orig) * $height_orig;;
}

if ($height < $imagethumbsize_h)
//if the height is smaller than supplied thumbnail size
{
$height = $imagethumbsize_h;
$width = ($imagethumbsize_h / $height_orig) * $width_orig;
}

$thumb = imagecreatetruecolor($width , $height);
$bgcolor = imagecolorallocate($thumb, $red, $green, $blue);
ImageFilledRectangle($thumb, 0, 0, $width, $height, $bgcolor);
imagealphablending($thumb, true);

imagecopyresampled($thumb, $image, 0, 0, 0, 0,
$width, $height, $width_orig, $height_orig);
$thumb2 = imagecreatetruecolor($imagethumbsize_w , $imagethumbsize_h);
// true color for best quality
$bgcolor = imagecolorallocate($thumb2, $red, $green, $blue);
ImageFilledRectangle($thumb2, 0, 0,
$imagethumbsize_w , $imagethumbsize_h , $white);
imagealphablending($thumb2, true);

$w1 =($width/2) - ($imagethumbsize_w/2);
$h1 = ($height/2) - ($imagethumbsize_h/2);

imagecopyresampled($thumb2, $thumb, 0,0, $w1, $h1,
$imagethumbsize_w , $imagethumbsize_h ,$imagethumbsize_w, $imagethumbsize_h);

// Output
//header('Content-type: image/gif');
//imagegif($thumb); //output to browser first image when testing

if ($fileout !="") imagejpeg($thumb2, $fileout); //write to file

}
function draw_pages($pagesize, $totalitems, $pagename, $file_name, $params=''){
//global $$pagename;
	$page = get_param($pagename);
	if (empty($page)) $page = 0;
	if($pagesize){

		if($totalitems){
			$totalpages = (int)(($totalitems-1)/$pagesize);
			if($totalpages > 0){
				echo '<b>��������:</b> ';

				if( $totalpages <= 11){
					for( $i = 0; $i <= $totalpages ; $i++){
						if($i == $page ){
							echo ($i+1);
						}else{
							echo ' <a href="'.$file_name.'?';
							echo $pagename.'='.($i).'">'.($i+1).'</a> ';
						}
					}

				}else{
					if($page<=5){
						for( $i = 0; ($i <= $page+1)||($page==1 && $i <= 3) ; $i++){
							if($i == $page ){
								echo ($i+1);
							}else{
								echo ' <a href="'.$file_name.'?';
								echo $pagename.'='.($i).'">'.($i+1).'</a> ';
							}
						}
						echo ' ... ';
						for( $i = $totalpages - 2 ; $i <= $totalpages ; $i++){
								echo ' <a href="'.$file_name.'?';
								echo $pagename.'='.($i).'">'.($i+1).'</a> ';
						}
					}else{
						if($page >= $totalpages-4){
							for( $i = 0;  $i <= 2 ; $i++){
									echo ' <a href="'.$file_name.'?';
									echo $pagename.'='.($i).'">'.($i+1).'</a> ';
							}
							echo ' ... ';
							if ($page==$totalpages)
								$i = $page - 2;
							else
								$i = $page - 1;
							for( ; $i <= $totalpages ; $i++){
									if($i == $page ){
										echo ($i+1);
									}else{
										echo ' <a href="'.$file_name.'?';
										echo $pagename.'='.($i).'">'.($i+1).'</a> ';
									}
							}
						}else{
							for( $i = 0;  $i <= 3 ; $i++){
									echo ' <a href="'.$file_name.'?';
									echo $pagename.'='.($i).'">'.($i+1).'</a> ';
							}
							echo ' ... ';

							for( $i = $page -1 ; $i <= $page +1 ; $i++){
									if($i == $page ){
										echo ($i+1);
									}else{
										echo ' <a href="'.$file_name.'?';
										echo $pagename.'='.($i).'">'.($i+1).'</a> ';
									}
							}
							echo ' ... ';
							for( $i = $totalpages - 2 ; $i <= $totalpages ; $i++){
									echo ' <a href="'.$file_name.'?';
									echo $pagename.'='.($i).'">'.($i+1).'</a> ';
							}

						}
					}
				}
				echo "<br>\n";
			}
		}
	}
}
