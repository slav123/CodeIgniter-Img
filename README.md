# CodeIgniter - Img

Simple CodeIgniter library to generate high quality thumbnails
___
Library is based on excellent * Smarty plugin "Thumb" * created in 2005 by Christoph Erdmann. 

My version is a little bit different, I'm using core from Thumb, and some modification which gives me more flexibility to work with it.

## Features
- thumbnails are generated “on the fly” no additional actions required,
- cache for generated thumbnails,
- a clear structure for generated thumbnails,
- thumbnails sharpening function,
- cropping function,
- fill space function

## INSTALLATION:
Upload img.php file to _application/libraries/_, upload _config/img.php_ to application/config folder. 

## USING THE LIBRARY:

Loading library

<code>
$this->load->library('img');
</code>

In view you can use function with this parameters:

<code>
echo $this->img->rimg('assets/img/image.jpg', array('longside' => 745, 'alt' => 'alt text')
</code>

## Parameters:

`longside` – width of longest side (pixel value),

`shortside` – width of shorter side

`crop` – cropping (true/false)

`width` – fixed width (with this parameter you need also set height)

`height` – height (with this parameter you need to also set height)

`sharpen` – sharp image after scale

`nocache` – rewrite existing file in the cache

`frame` - true / false (scale image to exact dimensions + create frame where proption is wrong)

`r`,`g`,`b` - colors for frame background

Feel free to send me an email if you have any problems.

Thanks,

Slawomir Jasinski

 slav123@gmail.com
 
 @slavomirj
