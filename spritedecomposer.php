<?php
/**
 * WARNING - This version is forked from its original owner :
 * @copyright	Copyright © 2014 - All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @author		James Liu
 * @author mail	james.liu668@gmail.com
 * @website		http://www.jmsliu.com/
 */
 
if(empty($_POST["width"]) || empty($_POST["height"]))
{
	echo "Please indicate the frame width and height";
}
else
{
	$msg = validUploadFile($_FILES["file"]);
	if ($msg)
	{
		//move_uploaded_file($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
		$frameWidth = $_POST["width"];
		$frameHeight = $_POST["height"];
		
		$startX = 0;
		$startY = 0;
		
		$result = explodeImage($_FILES["file"]["tmp_name"], $_FILES["file"]["type"], $frameWidth, $frameHeight, "./result/");
		foreach($result as $v) {
			echo "<img src=\"".$v."\"/><br />";
		}
	}
	else
	{
		echo "Invalid";
	}
}

function explodeImage($srcFilePath, $srcFileType, $frameWidth, $frameHeight, $desFolder) {
	$result = array();
	list($width, $height) = getimagesize($srcFilePath);
	$isPNG = ($srcFileType == "image/png");	// The image format can either be PNG or JPEG
	$srcimage = ($isPNG) ? imagecreatefrompng($srcFilePath) : imagecreatefromjpeg($srcFilePath);
	$frameNumber = 0;
	for($row = 0; $row < $height / $frameHeight; $row++) {
		for($col = 0; $col < $width / $frameWidth; $col++) {
			$desimage = imagecreatetruecolor($frameWidth, $frameHeight);
			imagealphablending($desimage, false);
			imagesavealpha($desimage, true);
			copyPixelsToImage($srcimage, $desimage, $col * $frameWidth, $row * $frameHeight, $frameWidth, $frameHeight);

			$strFormat = ($isPNG) ? ".png" : ".jpeg";
			$file = "./result/".$frameNumber.$strFormat;
			if ($isPNG)
				imagepng($desimage, $file);
			else
				imagejpeg($desimage, $file);
			imagedestroy($desimage);
			$result[] = $file;
			$frameNumber++;
		}
	}

	return $result;
}

function copyPixelsToImage($soruce, $destination, $startX, $startY, $width, $height) {
	for($i = 0; $i < $width; $i++) {
		for($j = 0; $j < $height; $j++) {
			$sourcePosX = $startX + $i;
			$sourcePoxY = $startY + $j;
			$rgba = ImageColorAt($soruce, $sourcePosX, $sourcePoxY);
			$a = ($rgba >> 24) & 0x7F;
			$r = ($rgba >> 16) & 0xFF;
			$g = ($rgba >> 8) & 0xFF;
			$b = $rgba & 0xFF;
			imagecolorallocatealpha($destination, $r, $g, $b, $a);
			imagesetpixel($destination, $i, $j, $rgba);
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
	
	return true;
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
