<?php

/*===============================================*\
|| ############################################# ||
|| # CLARICOM.CA                                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2016 CLARICOM All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/
 
//    Unsharp Mask for PHP - version 2.1.1   
//   
//    Unsharp mask algorithm by Torstein Hønsi 2003-07.   
//    thoensi_at_netcom_dot_no.

function Image_Sharpen($img, $amount, $radius, $threshold) {
	if ($amount > 500) { $amount = 500; }
	$amount = $amount * 0.016;
	if ($radius > 50) { $radius = 50; }
	$radius = $radius * 2;
	if ($threshold > 255) { $threshold = 255; }

	$radius = abs(round($radius));
	if ($radius == 0) { return $img; imagedestroy($img); }

	$w = imagesx($img); $h = imagesy($img);
	$imgCanvas = imagecreatetruecolor($w, $h);
	$imgBlur = imagecreatetruecolor($w, $h);

	if (function_exists('imageconvolution')) {
		$matrix = array(
			array( 1, 2, 1 ),
			array( 2, 4, 2 ),
			array( 1, 2, 1 )
		);
		imagecopy($imgBlur, $img, 0, 0, 0, 0, $w, $h);
		imageconvolution($imgBlur, $matrix, 16, 0);
	} else {
		for ($i = 0; $i < $radius; $i++)    { 
			imagecopy($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h);
			imagecopymerge($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50);
			imagecopymerge($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50);
			imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 

			imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 );
			imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25);
		}
	}

	if($threshold>0) {
		for ($x = 0; $x < $w-1; $x++) {
			for ($y = 0; $y < $h; $y++) {

				$rgbOrig = ImageColorAt($img, $x, $y);
				$rOrig = (($rgbOrig >> 16) & 0xFF);
				$gOrig = (($rgbOrig >> 8) & 0xFF);
				$bOrig = ($rgbOrig & 0xFF);

				$rgbBlur = ImageColorAt($imgBlur, $x, $y);

				$rBlur = (($rgbBlur >> 16) & 0xFF);
				$gBlur = (($rgbBlur >> 8) & 0xFF);
				$bBlur = ($rgbBlur & 0xFF);

				$rNew = (abs($rOrig - $rBlur) >= $threshold)
					? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
					: $rOrig;
				$gNew = (abs($gOrig - $gBlur) >= $threshold)
					? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
					: $gOrig;
				$bNew = (abs($bOrig - $bBlur) >= $threshold)
					? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
					: $bOrig;

				if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
					$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
					ImageSetPixel($img, $x, $y, $pixCol);
				}
			}
		}
	}
	else {
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$rgbOrig = ImageColorAt($img, $x, $y);
				$rOrig = (($rgbOrig >> 16) & 0xFF);
				$gOrig = (($rgbOrig >> 8) & 0xFF);
				$bOrig = ($rgbOrig & 0xFF);

				$rgbBlur = ImageColorAt($imgBlur, $x, $y);

				$rBlur = (($rgbBlur >> 16) & 0xFF);
				$gBlur = (($rgbBlur >> 8) & 0xFF);
				$bBlur = ($rgbBlur & 0xFF);

				$rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
				
				if($rNew > 255) { $rNew = 255; }
				else if($rNew < 0) { $rNew = 0; }
				
				$gNew = ($amount * ($gOrig - $gBlur)) + $gOrig; 
				
				if($gNew > 255) {$gNew = 255;}
				else if($gNew < 0) { $gNew = 0; }
				
				$bNew = ($amount * ($bOrig - $bBlur)) + $bOrig; 

				if( $bNew > 255) { $bNew = 255; }
				else if ( $bNew < 0 ) { $bNew = 0; }

				$rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew; 
				ImageSetPixel($img, $x, $y, $rgbNew); 
			}
		}
	}
	imagedestroy($imgCanvas);
	imagedestroy($imgBlur);

	return $img;
}

function create_thumbnail($targetPath, $targetFile, $sourceFile, $widthNew, $heightNew, $qualityNew)

{

$imgsize = getimagesize($targetFile);
switch(strtolower(substr($targetFile, -3))){
    case "jpg":
        $image = imagecreatefromjpeg($targetFile);    
    break;
    case "png":
        $image = imagecreatefrompng($targetFile);
    break;
    case "gif":
        $image = imagecreatefromgif($targetFile);
    break;
    default:
        exit;
    break;
}

$width = $widthNew; // New width of image    
$height = $heightNew; // New height of image

// Original size
$src_w = $imgsize[0];
$src_h = $imgsize[1];

// Create new size
if ($widthNew && ($src_w < $src_h)) {
    $width = ($heightNew / $src_h) * $src_w;
} else {
    $height = ($widthNew / $src_w) * $src_h;
}

$picture = imagecreatetruecolor($width, $height);
imagealphablending($picture, false);
imagesavealpha($picture, true);
$bool = imagecopyresampled($picture, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h);

// Sharpen Image
$picture = Image_Sharpen($picture, 80, 0.5, 3);

if($bool){
    switch(strtolower(substr($targetFile, -3))){
        case "jpg":
            $bool2 = imagejpeg($picture,$targetPath."/".$sourceFile,$qualityNew);
        break;
        case "png":
            imagepng($picture,$targetPath."/".$sourceFile);
        break;
        case "gif":
            imagegif($picture,$targetPath."/".$sourceFile);
        break;
    }
}

imagedestroy($picture);
imagedestroy($image);

}

function create_thumbnail_watermark($targetPath, $targetFile, $sourceFile, $widthNew, $heightNew, $qualityNew, $wmposition, $wmfile)
{

$imgsize = getimagesize($targetFile);
switch(strtolower(substr($targetFile, -3))){
    case "jpg":
        $image = imagecreatefromjpeg($targetFile);    
    break;
    case "png":
        $image = imagecreatefrompng($targetFile);
    break;
    case "gif":
        $image = imagecreatefromgif($targetFile);
    break;
    default:
        exit;
    break;
}

$width = $widthNew; //New width of image    
$height = $heightNew; //New height of image

// Original size
$src_w = $imgsize[0];
$src_h = $imgsize[1];

// Create new size
if ($widthNew && ($src_w < $src_h)) {
    $width = ($heightNew / $src_h) * $src_w;
} else {
    $height = ($widthNew / $src_w) * $src_h;
}

imagealphablending($image, true);

$picture = imagecreatetruecolor($width, $height);
imagealphablending($picture, true);
imagesavealpha($picture, true);
imagecopyresampled($picture, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h);

// Sharpen Image
$picture = Image_Sharpen($picture, 80, 0.5, 3);

// Logo check and var

    if (stripos($wmfile, ".png") !== false) {
        $lpLogo = imagecreatefrompng($wmfile);
    } elseif (stripos($wmfile, ".jpg") !== false) {
        $lpLogo = imagecreatefromjpeg($wmfile);
    } elseif (stripos($wmfile, ".gif") !== false) {
        $lpLogo = imagecreatefromgif($wmfile);
    }
    $width_logo = imagesx($lpLogo);
    $height_logo = imagesy($lpLogo);

        if ($wmposition == 1) {
            $j = 0;
            $i = 0;
        } elseif ($wmposition == 2) {
            $j = ($width - $width_logo) / 2;
            $i = 0;
        } elseif ($wmposition == 3) {
            $j = $width - $width_logo;
            $i = 0;
        } elseif ($wmposition == 4) {
            $j = 0;
            $i = ($height - $height_logo) / 2;
        } elseif ($wmposition == 5) {
            $j = ($width - $width_logo) / 2;
            $i = ($height - $height_logo) / 2;
        } elseif ($wmposition == 6) {
            $j = $width - $width_logo;
            $i = ($height - $height_logo) / 2;
        } elseif ($wmposition == 7) {
            $j = 0;
            $i = $height - $height_logo;
        } elseif ($wmposition == 8) {
            $j = ($width - $width_logo) / 2;
            $i = $height - $height_logo;
        } else {
            $j = $width - $width_logo;
            $i = $height - $height_logo;
        }
       $bool = imagecopy($picture, $lpLogo, $j, $i, 0, 0, $width_logo, $height_logo);

if($bool){
    switch(strtolower(substr($targetFile, -3))){
        case "jpg":
            $bool2 = imagejpeg($picture,$targetPath."/".$sourceFile,$qualityNew);
        break;
        case "png":  
            imagepng($picture,$targetPath."/".$sourceFile);
        break;
        case "gif":
            imagegif($picture,$targetPath."/".$sourceFile);
        break;
    }
}

imagedestroy($picture);
imagedestroy($image);

}
?>