#CodeIgniter - Img

Simple CodeIgniter library to generate high quality thumbnails
___
Hey everybody,

Library is based on excellent * Smarty plugin "Thumb" * created in 2005 by Christoph Erdmann. 

My version is a little bit different, I'm using core from Thumb, and some modification which gives me more flexibility to work with it.

##DOCUMENTATION:
Documentation is located at <http://www.spidersoft.com.au/projects/codeigniter-img-thumbnails-on-the-fly/>

##INSTALLATION:
Upload img.php file to application/libraries/, upload config/img.php to application/config folder. 

##USING THE LIBRARY:

Loading library

<code>
$this->load->library('img');
</code>

In view you can use function with this parameters:

<code>
echo $this->img->rimg('assets/img/image.jpg', array('longside' => 745, 'alt' => 'alt text')
</code>

##Parameters:

`longside` – width of longest side (pixel value),

`shortside` – width of shorter side

`crop` – cropping (true/false)

`width` – fixed width (with this parameter you need also set height)

`height` – height (with this parameter you need to also set height)

`sharpen` – sharp image after scale

`nocache` – rewrite existing file in the cache

Feel free to send me an email if you have any problems.

Thanks,

Slawomir Jasinski

 slav123@gmail.com
 
 @slavomirj
