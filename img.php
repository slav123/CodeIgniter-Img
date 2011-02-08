<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Img
*
* Author: Slawomir Jasinski
*		  slav123@gmail.com
*         @slavomirj
*
*
* Location: http://github.com/slav123/CodeIgniter-Img
*
* Created:  07-02-2011
* Last update: 08-02-2011
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
* Requirements: PHP5 or above
*
*/

class img {

    /**
    * CodeIgniter global
    *
    * @var string
    **/
    protected $ci;

    public $base = '';

    function __construct() {
	$this->ci =& get_instance();
	$this->ci->load->config('img', TRUE);
        // Do something with $params

	$this->base_path = $this->ci->config->item('base_path', 'img');
	$this->base_url = $this->ci->config->item('base_url', 'img');
    }

    function rimg($source, $params, $oi = true) {
	if (!file_exists($source))
	    return false;

	$info = getimagesize($source);

	$src['width'] = $info[0];
	$src['height'] = $info[1];

	// default $dst
	$dst = array('offset_w' => 0, 'offset_h' => 0);

	// default values, null them to avoid empty indexes later
	$def = array('longside','shortside','crop', 'width', 'height', 'sharpen');
	foreach ($def as $v) {
	    if (!isset($params[$v])) $params[$v] = null;
	}

	// if width & height -> assign them to dest
	if (!empty($params['width'])) $dst['width'] = $params['width'];
	if (!empty($params['height'])) $dst['height'] = $params['height'];

	// if alt is empty, setup file name - bad idea ;)
	if (empty($params['alt'])) $params['alt'] = basename($source);

	if (is_numeric($params['longside']))
	    if ($src['width'] < $src['height']) {
		$dst['height']	= $params['longside'];
		$dst['width']	= round($params['longside']/($src['height']/$src['width']));
	    } else {
		$dst['width']	= $params['longside'];
		$dst['height']	= round($params['longside']/($src['width']/$src['height']));
	    }

	if (is_numeric($params['shortside'])) {
	    if ($src['width'] < $src['height']) {
		$dst['width']	= $params['shortside'];
		$dst['height']	= round($params['shortside']/($dst['width']/$dst['height']));
	    } else {
		$dst['height']	= $params['shortside'];
		$dst['width']	= round($params['shortside']/($dst['height']/$dst['width']));
	    }
	}

	// crop yes / no
	if($params['crop'] == true) {
	    $width_ratio = $src['width']/$dst['width'];
	    $height_ratio = $src['height']/$dst['height'];

	    if ($width_ratio > $height_ratio) {
		$dst['offset_w'] = round(($src['width']-$dst['width']*$height_ratio)/2);
		$src['width'] = round($dst['width']*$height_ratio);
	    } elseif ($width_ratio < $height_ratio) {
		$dst['offset_h'] = round(($src['height']-$dst['height']*$width_ratio)/2);
		$src['height'] = round($dst['height']*$width_ratio);
	    }
	}



	// create destination directory
	$dir = $this->base_path;
	if (empty($params['longside']))
	    $dir .= "{$dst['width']}x{$dst['height']}";
	else
	    $dir .= "{$params['longside']}";

	if (!is_dir($dir)) mkdir($dir);

	$dst['file'] = $dir . "/" . basename($source);

	if (file_exists($dst['file'])) return "<img src=\"{$this->base_url}/" . basename($dst['file']) . "\" width=\"{$dst['width']}\" height=\"{$dst['height']}\" alt=\"{$params['alt']}\"/>";

	// create dst img
	switch ($info[2]) {
	    case 1:
		$src['image'] = imagecreatefromgif($source);
	    break;
	    case 2:
		$src['image'] = imagecreatefromjpeg($source);
	    break;
	    case 3:
		$src['image'] = imagecreatefrompng($source);
	    break;
	}

	if ($dst['width']*4 < $src['width'] AND $dst['height']*4 < $src['height']) {
	    $_TMP['width'] = round($dst['width']*4);
	    $_TMP['height'] = round($dst['height']*4);

	    $_TMP['image'] = imagecreatetruecolor($_TMP['width'], $_TMP['height']);
	    imagecopyresized($_TMP['image'], $src['image'], 0, 0, $dst['offset_w'], $dst['offset_h'], $_TMP['width'], $_TMP['height'], $src['width'], $src['height']);
	    $src['image'] = $_TMP['image'];
	    $src['width'] = $_TMP['width'];
	    $src['height'] = $_TMP['height'];

	    $dst['offset_w'] = 0;
	    $dst['offset_h'] = 0;
	    unset($_TMP['image']);
	}

	$dst['image'] = imagecreatetruecolor($dst['width'], $dst['height']);
	imagecopyresampled($dst['image'], $src['image'], 0, 0, $dst['offset_w'], $dst['offset_h'], $dst['width'], $dst['height'], $src['width'], $src['height']);
	if ($params['sharpen'] != false) $dst['image'] = $this->UnsharpMask($dst['image'],80,.5,3);

	$dst['type'] = $info[2];




	switch ($dst['type']) {
	    case 1:
		imagetruecolortopalette($src['image'], false, 256);
		imagegif($dst['image'], $dst['file']);
	    break;
	    case 2:
		Imageinterlace($dst['image'], 1);
		if (empty($params['quality'])) $params['quality'] = 80;
		imagejpeg($dst['image'], $dst['file'], $params['quality']);
	    break;
	    case 3:
		imagepng($dst['image'], $dst['file']);
	    break;
	}

	imagedestroy($dst['image']);
	imagedestroy($src['image']);

	return "<img src=\"{$this->base_url}/" . basename($dst['file']) . "\" width=\"{$dst['width']}\" height=\"{$dst['height']}\" alt=\"{$params['alt']}\"/>";

	/*
          Array
(
    [0] =&gt; 800
    [1] =&gt; 600
    [2] =&gt; 2
    [3] =&gt; width="800" height="600"
    [bits] =&gt; 8
    [channels] =&gt; 3
    [mime] =&gt; image/jpeg
)
	*/



    }

    private function UnsharpMask($img, $amount, $radius, $threshold) {
			// Attempt to calibrate the parameters to Photoshop:
			if ($amount > 500) $amount = 500;
			$amount = $amount * 0.016;
			if ($radius > 50) $radius = 50;
			$radius = $radius * 2;
			if ($threshold > 255) $threshold = 255;

			$radius = abs(round($radius)); 	// Only integers make sense.
			if ($radius == 0) {	return $img; imagedestroy($img); break;	}
			$w = imagesx($img); $h = imagesy($img);
			$imgCanvas = $img;
			$imgCanvas2 = $img;
			$imgBlur = imagecreatetruecolor($w, $h);

			// Gaussian blur matrix:
			//	1	2	1
			//	2	4	2
			//	1	2	1

			// Move copies of the image around one pixel at the time and merge them with weight
			// according to the matrix. The same matrix is simply repeated for higher radii.
			for ($i = 0; $i < $radius; $i++)
				{
				imagecopy	  ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
				imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
				imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
				imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
				}
			$imgCanvas = $imgBlur;

			// Calculate the difference between the blurred pixels and the original
			// and set the pixels
			for ($x = 0; $x < $w; $x++)
				{ // each row
				for ($y = 0; $y < $h; $y++)
					{ // each pixel
					$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
					$rOrig = (($rgbOrig >> 16) & 0xFF);
					$gOrig = (($rgbOrig >> 8) & 0xFF);
					$bOrig = ($rgbOrig & 0xFF);
					$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
					$rBlur = (($rgbBlur >> 16) & 0xFF);
					$gBlur = (($rgbBlur >> 8) & 0xFF);
					$bBlur = ($rgbBlur & 0xFF);

					// When the masked pixels differ less from the original
					// than the threshold specifies, they are set to their original value.
					$rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
					$gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
					$bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

					if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
						{
						$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
						ImageSetPixel($img, $x, $y, $pixCol);
						}
					}
				}
			return $img;
			}
		}
