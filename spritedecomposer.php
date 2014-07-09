<?php
/**
 * @copyright	Copyright © 2014 - All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @author		James Liu
 * @author mail	james.liu668@gmail.com
 * @website		http://www.jmsliu.com/
 */
 
if(empty($_POST["width"]) || empty($_POST["height"]))
{
	echo "please give the frame width and height";
}
else
{
	$msg = validUploadFile($_FILES["file"]);
	if( $msg == "valid")
	{
		//move_uploaded_file($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
		$frameWidth = $_POST["width"];
		$frameHeight = $_POST["height"];
		
		$startX = 0;
		$startY = 0;
		
		list($width, $height) = getimagesize($_FILES["file"]["tmp_name"]);
		$srcimage = imagecreatefrompng($_FILES["file"]["tmp_name"]);
		
		$usedColor = getColorCollection($srcimage, $width, $height);
		$colorInt = findNotUsedColor($usedColor);
		$r = ($colorInt >> 16) & 0xFF;
		$g = ($colorInt >> 8) & 0xFF;
		$b = $colorInt & 0xFF;
		
		$frameNumber = 0;
		for($row = 0; $row < $height / $frameHeight; $row++)
		{
			for($col = 0; $col < $width / $frameWidth; $col++)
			{
				$desimage = imagecreatetruecolor($frameWidth, $frameHeight);
				$transparent_new = ImageColorAllocate($desimage, $r, $g, $b);
				$transparent_new_index = ImageColorTransparent( $desimage, $transparent_new );
				ImageFill( $desimage, 0, 0, $transparent_new_index );
				
				imagecopy($desimage, $srcimage, 0, 0, $col * $frameWidth, $row * $frameHeight, $frameWidth, $frameHeight);
				
				$file = "./result/".$frameNumber.".png";
				imagepng($desimage, $file);
				imagedestroy($desimage);
				echo "<img src=\"".$file."\"/>";
				
				$frameNumber++;
			}
		}
	}
	else
	{
		echo "invalid";
	}
}

function getColorCollection($imageResource, $width, $height)
{
	$totalPixels = $width * $height;
	$colorCollection = array();
	for($i = 0; $i < $width; $i++)
	{
		for($j = 0; $j < $height; $j++)
		{
			$rgb = ImageColorAt($imageResource, $i, $j);
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			$colorCollection[$rgb] = $rgb;
		}
	}
	
	return $colorCollection;
}

function findNotUsedColor($colorCollection)
{
	for($i = 0; $i < 0xFFFFFF; $i++)
	{
		if(!in_array($i, $colorCollection))
		{
			return $i;
		}
	}
}

function validUploadFile($file)
{
	if ($file["error"] > 0)
	{
		return "Uploading File Error!";
	}
	
	if (empty($file["type"]))
	{
		return "Unknown File Type!";
	}
	else if ($file["type"] != "image/jpeg"
	&& $file["type"] != "image/jpg"
	&& $file["type"] != "image/png"
	&& $file["type"] != "image/x-png")
	{
		return "File type ".$file["type"]." is invalid!";
	}

	if($file["size"] > 2000000)
	{
		return "File is too big!";
	}
	
	return "valid";
}

function validImages()
{
	/**
		Enable the following extensions
		extension=php_mbstring.dll
		extension=php_exif.dll
	*/
	$ext = exif_imagetype( $_FILES['file']['tmp_name']);
	if($ext === false)
	{
		return false;
	}
	else
	{
		return true;
	}
}
?>