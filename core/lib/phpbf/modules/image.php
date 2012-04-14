<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Image module
* @file image.php
* @package PhpBF
* @subpackage image
* @version 0.4
* @author Loic Minghetti
* @date Started on the 2006-11-25
*/

// Security
if (!defined('C_SECURITY')) exit;

//Example of usage

/*
if (move_uploaded_file($_FILES["newfile"]["tmp_name"], $dir."original.jpg")) {
	$img = new img($dir."original.jpg");
	$img->resize(150,400);
	$img->save($dir."resized.jpg");	
	$img->destroy();
}

*/


// Define the image class used for editing images
class img {
	
	var $img = false;
	var $src = null;
	var $h = -1;
	var $w = -1;
	var $type = null;
	var $loaded = false;
	
	function img($src = NULL) {
		if ($src == NULL || !file_exists($src)) return false;
		$this->src = $src;
	}
	
	function load () {
		if ($this->loaded) return true;
		//$size = $this->get_size();
		//if (!$size) throw new exception("Failed loading image");
		list($this->w,$this->h,$this->type) = getImageSize($this->src);
		
		switch ($this->type) {
			case 1: $this->img = imageCreateFromGif($this->src); break; 
			case 2: $this->img = imageCreateFromJpeg($this->src); break; 
			case 3: $this->img = imageCreateFromPng($this->src); break; 
			default: throw new exception("Unsuported image type"); break; 
		}
		$this->loaded = true;
		return true;
	}
	
	function crop ($x, $y, $w, $h, $allow_fill) {
		
	}
	
	function resize ($nw = -1, $nh = -1, $method = "ratio" , $allow_enlarge = false) {
		
		if (!$this->load() || ($nw == -1 && $nh == -1) || $this->h <= 0 || $nh == 0) throw new exception("Image resize failed, invalid parameters");
	
		// SET WIDTH AND HEIGHT
		
		// resize types : 
		//	//EXACT	image has exact same size as specified, blank space being made transparent or black (according to format) <-- NOT IMPLEMENTED
		//	RATIO	set a max width and a max height and the resize will keep ratio 
		//	STRECH 	set a width and a height for the new picture
		
		
		// Determine the actual new width and height
		$ratio  = $this->w/$this->h;
		
		// prevent enlargement if disabled
		if (!$allow_enlarge) {
			if ($nw > $this->w) $nw = $this->w;
			if ($nh > $this->h) $nh = $this->h;
		}
			
					
		if ($nh == -1 || $nw == -1 || $method == "strech") {
			$actual_nw = ($nw == -1)? $nh*$ratio : $nw;
			$actual_nh = ($nh == -1)? $nw*$ratio : $nh;
			
		} else {
			$nratio = $nw/$nh; 
			$actual_nw = ($nratio > $ratio)? $nh*$ratio : $nw;
			$actual_nh = ($nratio < $ratio)? $nw/$ratio : $nh;
		}
		
		$new_img = imageCreateTrueColor($actual_nw, $actual_nh);
		
		if (imageCopyResampled($new_img, $this->img, 0, 0, 0, 0, $actual_nw,$actual_nh, $this->w,$this->h)) {
			ImageDestroy($this->img);
			$this->img = $new_img;
			return true;
		} else {
			return false;
		}
			
	}
	
	function save($nsrc, $nformat = 2, $quality = 80) {
		if (!$this->load() || !$this->img || !$nsrc) return false;
		if (!$nformat) $nformat = $this->type;
		if (file_exists($nsrc)) {
			if (!unlink($nsrc)) return false;
		}
		
		switch ($nformat) {
			case 1: imageGIF($this->img, $nsrc); break; 
			case 2: imageJPEG($this->img, $nsrc, $quality); break; 
			case 3: imagePNG($this->img, $nsrc); break; 		
		}
		if (!file_exists($nsrc)) return false;
		ImageDestroy($this->img);
		return true;
	}
	
	function destroy() {
		if ($this->img) ImageDestroy($this->img);
	}
}



?>
