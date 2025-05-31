<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Create new objects from png's
$mask = new Imagick('/home/foxholestats/public_html/tiles/largeMask.png');


$files = glob("/home/foxholestats/public_html/tiles/original/*.png");
foreach($files as $file){

$image = new Imagick($file);
//$image->setImageColorspace (imagick::COLORSPACE_RGB); 

// IMPORTANT! Must activate the opacity channel
// See: http://www.php.net/manual/en/function.imagick-setimagematte.php
$image->setImageMatte(1); 

//$image->scaleImage(1124, 0);
//91%
$targetX = round($image->getImageWidth() * 0.911);
$targetY = round($targetX * 0.867);

$image->cropImage($targetX, $targetY, ($image->getImageWidth()-$targetX)/2, ($image->getImageHeight()-$targetY)/2);

$image->setImagePage($targetX, $targetY, 0, 0);

// Create composite of two images using DSTIN
// See: http://www.imagemagick.org/Usage/compose/#dstin
$image->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);

// Write image to a file.
$image->writeImage('/home/foxholestats/public_html/tiles/masked/' . basename($file));


echo $file, "<br/>";
//header("Content-Type: image/png");
//echo $image;
//break;
}
?>