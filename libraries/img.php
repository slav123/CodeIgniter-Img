<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:  Img
 *
 * Author: Slawomir Jasinski
 *    slav123@gmail.com
 *
 * @slavomirj
 *
 * Location: http://github.com/slav123/CodeIgniter-Img
 *
 * Created:  07-02-2011
 * Last update: 14-04-2014
 *
 * Description:  CodeIgniter library to generate high quality thumbnails
 *
 * Library is based on excellent * Smarty plugin “Thumb” * created in 2005 by Christoph Erdmann. This version is a little bit
 * different, we are using core from Thumb, and some modification which gives more flexibility to work with it.
 *
 * Requirements: PHP5 with GD or above
 *
 */
class img
{

	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;

	/**
	 * function constructor
	 */
	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->config('img', TRUE);
	}

	/**
	 * main scale function
	 *
	 * @param string $source   url to file
	 * @param array  $params   params array
	 * @param bool   $no_cache overwrite existing files
	 *
	 * @return string
	 */
	public function rimg($source, $params, $no_cache = FALSE)
	{
		$source = trim($source);

		if (file_exists($this->ci->config->config['img']['base_path'] . '/' . $source))
		{
			$source = $this->ci->config->config['img']['base_path'] . '/' . $source;
		}
		else
		{
			$path_parts = pathinfo($source);
			$source     = $path_parts['dirname'] . '/' . $path_parts['basename'];
		}



		if (! is_file($source))
		{
			return '<img src="http://placehold.it/' . $params['width'] . 'x' . $params['height'] . '" width="'.$params['width'].'" height="'.$params['height'].'" alt="noimage">';
			die();
		}

		$info = @getimagesize($source);

		if (empty($info))
		{
			return "not an image";
			die();
		}

		$src['width']  = $info[0];
		$src['height'] = $info[1];

		// default $dst
		$dst = array('offset_w' => 0, 'offset_h' => 0);

		// default values, null them to avoid empty indexes later
		$def = array('longside', 'shortside', 'crop', 'width', 'height', 'sharpen', 'nocache', 'frame', 'return');

		if (empty($params['r'])) $params['r'] = 255;
		if (empty($params['g'])) $params['g'] = 255;
		if (empty($params['b'])) $params['b'] = 255;

		// set default paramteres
		foreach ($def as $v)
		{
			if (! isset($params[$v])) $params[$v] = NULL;
		}

		// if width & height -> assign them to dest
		if (! empty($params['width']))
		{
			$dst['width'] = intval($params['width']);
		}

		if (! empty($params['height']))
		{
			$dst['height'] = intval($params['height']);
		}

		// if alt is empty, setup file name - bad idea ;)
		if (empty($params['alt']))
		{
			$params['alt'] = basename($source);
		}
		else
		{
			$params['alt'] = htmlentities($params['alt']);
		}




		// scale to long side
		if (is_numeric($params['longside']))
		{
			if ($src['width'] < $src['height'])
			{
				$dst['height'] = $params['longside'];
				$dst['width']  = round($params['longside'] / ($src['height'] / $src['width']));
			}
			else
			{
				$dst['width']  = $params['longside'];
				$dst['height'] = round($params['longside'] / ($src['width'] / $src['height']));
			}
		}

		// scale to shortside
		if (is_numeric($params['shortside']))
		{
			if ($src['width'] < $src['height'])
			{
				$dst['width']  = $params['shortside'];
				$dst['height'] = round($params['shortside'] / ($src['width'] / $src['height']));
			}
			else
			{
				$dst['height'] = $params['shortside'];
				$dst['width']  = round($params['shortside'] / ($src['height'] / $src['width']));
			}
		}

		// crop yes / no
		if ($params['crop'] === TRUE)
		{
			$width_ratio  = $src['width'] / $dst['width'];
			$height_ratio = $src['height'] / $dst['height'];

			if ($width_ratio > $height_ratio)
			{
				$dst['offset_w'] = round(($src['width'] - $dst['width'] * $height_ratio) / 2);
				$src['width']    = round($dst['width'] * $height_ratio);
			}
			elseif ($width_ratio < $height_ratio)
			{
				$dst['offset_h'] = round(($src['height'] - $dst['height'] * $width_ratio) / 2);
				$src['height']   = round($dst['height'] * $width_ratio);
			}
		}

		// fill empty space around image
		if ($params['frame'] === TRUE)
		{
			$src['ratio']    = $src['width'] / $src['height'];
			$params['ratio'] = $params['width'] / $src['height'];
			if ($src['width'] > $src['height'])
			{
				if ((($params['height'] / $src['ratio']) < $params['ratio']) OR (round($params['width'] / $src['ratio']) > $params['height']))
				{
					$dst['width']  = round($params['height'] * $src['ratio']);
					$dst['height'] = $params['height'];
				}
				else
				{
					$dst['width']  = $params['width'];
					$dst['height'] = round($params['width'] / $src['ratio']);
				}
			}
			else
			{
				if ((($params['height'] / $src['ratio']) < $params['ratio']) OR (round($params['height'] * $src['ratio']) > $params['width']))
				{
					$dst['width']  = $params['width'];
					$dst['height'] = round($params['width'] / $src['ratio']);
				}
				else
				{
					$dst['width']  = round($params['height'] * $src['ratio']);
					$dst['height'] = $params['height'];
				}
			}
		}

		// create destination directory width x height or longside
		if (! empty($params['longside']))
		{
			$dir = '/l' . $params['longside'];
		}
		else if (! empty($params['shortside']))
		{
			$dir = '/s' . $params['shortside'];
		}
		else if (! empty($param['crop']))
		{
			$dir = "/c{$dst['width']}x{$dst['height']}";
		}
		else
		{
			$dir = "/{$dst['width']}x{$dst['height']}";
		}

		// check if cache path exists
		if (! is_dir($this->ci->config->config['img']['base_path'] . $dir))
		{
			if (is_writable($this->ci->config->config['img']['base_path']))
			{
				mkdir($this->ci->config->config['img']['base_path'] . $dir);
			}
			else
			{
				die('Unable to create cache dir in ' . $this->ci->config->config['img']['base_path']);
			}

		}

		// full path to final file
		$dst['file'] = $this->ci->config->config['img']['base_path'] . $dir . "/" . basename($source);

		$extra_parameters = '';

		// extra parameters
		if (! empty($params['class']))
		{
			$extra_parameters .= "class=\"{$params['class']}\"";
		}

		// id src width & height = dst by-pass
		if ($src['width'] === $dst['width'] && $src['height'] === $dst['height'])
		{
			$temp = pathinfo($source);
			return '<img src="' . base_url($temp['dirname'] . "/" . basename($dst['file'])) . "\" width=\"{$dst['width']}\" height=\"{$dst['height']}\" alt=\"{$params['alt']}\" {$extra_parameters}/>";
		}


		// if file exists - return img info
		if (file_exists($dst['file']) AND $params['nocache'] !== TRUE)
		{
			return "<img src=\"{$this->ci->config->config['img']['base_url']}{$dir}/" . basename($dst['file']) . "\" width=\"{$params['width']}\" height=\"{$params['height']}\" alt=\"{$params['alt']}\" {$extra_parameters}/>";
		}

		$this->_memory_prepare($info);

		// create dst img
		switch ($info[2])
		{
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

		if ($dst['width'] * 4 < $src['width'] AND $dst['height'] * 4 < $src['height'])
		{
			$_TMP['width']  = round($dst['width'] * 4);
			$_TMP['height'] = round($dst['height'] * 4);

			$_TMP['image'] = imagecreatetruecolor($_TMP['width'], $_TMP['height']);

			//imagecolortransparent($_TMP['image'], imagecolorallocate($_TMP['image'], 0, 0, 0, 127));
			imagealphablending($_TMP['image'], FALSE);
			imagesavealpha($_TMP['image'], TRUE);

			$transparent = imagecolorallocatealpha($_TMP['image'], 255, 255, 255, 127);
			imagefilledrectangle($_TMP['image'], 0, 0, $_TMP['width'], $_TMP['height'], $transparent);

			imagecopyresampled($_TMP['image'], $src['image'], 0, 0, $dst['offset_w'], $dst['offset_h'], $_TMP['width'], $_TMP['height'], $src['width'], $src['height']);
			$src['image']  = $_TMP['image'];
			$src['width']  = $_TMP['width'];
			$src['height'] = $_TMP['height'];

			$dst['offset_w'] = 0;
			$dst['offset_h'] = 0;
			unset($_TMP['image']);
		}

		$dst['image'] = imagecreatetruecolor($dst['width'], $dst['height']);
		imagealphablending($dst['image'], FALSE);
		imagesavealpha($dst['image'], TRUE);

		$transparent = imagecolorallocatealpha($dst['image'], 255, 255, 255, 127);
		imagefilledrectangle($dst['image'], 0, 0, $dst['width'], $dst['height'], $transparent);

		imagecopyresampled($dst['image'], $src['image'], 0, 0, $dst['offset_w'], $dst['offset_h'], $dst['width'], $dst['height'], $src['width'], $src['height']);

		if ($params['sharpen'] !== FALSE)
		{
			$dst['image'] = $this->unsharp_mask($dst['image'], 80, .5, 3);
		}

		if ($params['frame'] === TRUE)
		{

			$dst_off_h = floor(($params['height'] - $dst['height']) / 2);
			$dst_off_w = round(($params['width'] - $dst['width']) / 2);

			$currimg = $dst['image'];

			$dst['image'] = imagecreatetruecolor($params['width'], $params['height']);

			$bgcolor = imagecolorallocate($dst['image'], $params['r'], $params['g'], $params['b']);
			imagefill($dst['image'], 0, 0, $bgcolor);
			imagecopyresampled($dst['image'], $currimg, $dst_off_w, $dst_off_h, 0, 0, $dst['width'], $dst['height'], $dst['width'], $dst['height']);
			$dst['width']  = $params['height'];
			$dst['height'] = $params['width'];

		}


		$dst['type'] = $info[2];

		switch ($dst['type'])
		{
			case 1:
				imagetruecolortopalette($src['image'], FALSE, 256);
				imagegif($dst['image'], $dst['file']);
				break;
			case 2:
				Imageinterlace($dst['image'], 1);
				if (empty($params['quality'])) $params['quality'] = 85;
				imagejpeg($dst['image'], $dst['file'], $params['quality']);
				break;
			case 3:
				imagepng($dst['image'], $dst['file']);
				break;
		}

		imagedestroy($dst['image']);
		imagedestroy($src['image']);

		if ($params['return'] === TRUE) {
			return $this->ci->config->config['img']['base_url'] . $dir . "/" . basename($dst['file']);
		} else {
			return "<img src=\"{$this->ci->config->config['img']['base_url']}{$dir}/" . basename($dst['file']) . "\" width=\"{$dst['width']}\" height=\"{$dst['height']}\" alt=\"{$params['alt']}\" {$extra_parameters}/>";
		}



	}

	/**
	 * @param $img
	 * @param $amount
	 * @param $radius
	 * @param $threshold
	 *
	 * @return mixed
	 */
	private function unsharp_mask($img, $amount, $radius, $threshold)
	{
		// Attempt to calibrate the parameters to Photoshop:
		if ($amount > 500) $amount = 500;
		$amount = $amount * 0.016;
		if ($radius > 50) $radius = 50;
		$radius = $radius * 2;
		if ($threshold > 255) $threshold = 255;

		$radius = abs(round($radius)); // Only integers make sense.
		if ($radius == 0)
		{
			return $img;
			imagedestroy($img);
		}
		$w          = imagesx($img);
		$h          = imagesy($img);
		$imgCanvas  = $img;
		$imgCanvas2 = $img;
		$imgBlur    = imagecreatetruecolor($w, $h);

		// Gaussian blur matrix:
		//	1	2	1
		//	2	4	2
		//	1	2	1

		// Move copies of the image around one pixel at the time and merge them with weight
		// according to the matrix. The same matrix is simply repeated for higher radii.
		for ($i = 0; $i < $radius; $i ++)
		{
			imagecopy($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
			imagecopymerge($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
			imagecopymerge($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
			imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
			imagecopymerge($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
			imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
			imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20); // up
			imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
			imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
		}
		$imgCanvas = $imgBlur;

		// Calculate the difference between the blurred pixels and the original
		// and set the pixels
		for ($x = 0; $x < $w; $x ++)
		{ // each row
			for ($y = 0; $y < $h; $y ++)
			{ // each pixel
				$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
				$rOrig   = (($rgbOrig >> 16) & 0xFF);
				$gOrig   = (($rgbOrig >> 8) & 0xFF);
				$bOrig   = ($rgbOrig & 0xFF);
				$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
				$rBlur   = (($rgbBlur >> 16) & 0xFF);
				$gBlur   = (($rgbBlur >> 8) & 0xFF);
				$bBlur   = ($rgbBlur & 0xFF);

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


	/**
	 * increase memory limit for image rescale
	 *
	 * @param obj $image image handler
	 *
	 */
	private function _memory_prepare($image)
	{
		$memoryNeeded = ceil(($image[0] * $image[1] * $image['bits']) / (1024 * 1024));
		$memoryNeeded += ($memoryNeeded * 0.05);
		$memoryAvailble = intval(ini_get('memory_limit'));
		//      echo "<br>memory avaible = $memoryAvailble";

		if ($memoryNeeded > $memoryAvailble)
		{
			//          echo "<br>memoryNeeded = $memoryNeeded <br>";
			@ini_set("memory_limit", $memoryNeeded . "M");
			//          echo "<br>memory avaible = ".ini_get('memory_limit');
		}
	}
}
