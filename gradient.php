<?php

// gradient($width, $height, $from_r, $from_g, $from_b, $to_r, $to_g, $to_b, $vertical);


function gradient($image, $image_width, $image_height, $c1_r, $c1_g, $c1_b, $c2_r, $c2_g, $c2_b, $vertical = false)
{
    // make sure that the parameters are what we need them to be
    $image_width    =   intval($image_width);
    $image_height   =   intval($image_height);
    $c1_r           =   intval($c1_r);
    $c1_g           =   intval($c1_g);
    $c1_b           =   intval($c1_b);
    $c2_r           =   intval($c2_r);
    $c2_g           =   intval($c2_g);
    $c2_b           =   intval($c2_b);
    $vertical       =   (bool)$vertical;

    // render gradient step by step
    for($i=0; $i<$image_height; $i++)
    {
        // get each color component for this step
        $color_r = floor($i * ($c2_r-$c1_r) / $image_height)+$c1_r;
        $color_g = floor($i * ($c2_g-$c1_g) / $image_height)+$c1_g;
        $color_b = floor($i * ($c2_b-$c1_b) / $image_height)+$c1_b;
        
        // create this color
        $color = imagecolorallocate($image, $color_r, $color_g, $color_b);
        
        // draw a line using this color
        imageline($image, 0, $i, $image_width, $i, $color);
    }

    if($vertical) // rotate the image 
    {
        // php.net/imagerotate: "Note: This function is only available if PHP is compiled with the bundled version of the GD library."
        // so if this function doesn't work you must find another way to rotate the image;
        // one option is to use the function that I found googling and I added to the end of this file called rotateImage
        $image = imagerotate($image, 90, 0); 
    } 
    
    return $image;
}

function rotateImage($img, $rotation) 
{
  $width = imagesx($img);
  $height = imagesy($img);
  switch($rotation) 
  {
    case 90: 
        $newimg= @imagecreatetruecolor($height , $width );
        break;
    case 180: 
        $newimg= @imagecreatetruecolor($width , $height );
        break;
    case 270: 
        $newimg= @imagecreatetruecolor($height , $width );
        break;
    case 0: 
        return $img;
        break;
    case 360: 
        return $img;
        break;
  }
  if($newimg) 
  {
    for($i = 0;$i < $width ; $i++) 
    {
      for($j = 0;$j < $height ; $j++) 
      {
        $reference = imagecolorat($img,$i,$j);
        switch($rotation) 
        {
          case 90: if(!@imagesetpixel($newimg, ($height - 1) - $j, $i, $reference )){return false;}break;
          case 180: 
            if(!@imagesetpixel($newimg, $width - $i-1, ($height - 1) - $j, $reference ))
            {
                return false;
            }
            break;
          case 270: if(!@imagesetpixel($newimg, $j, $width - $i, $reference )){return false;}break;
        }
      }
    } 
    return $newimg;
  }
  return false;
}
?>
