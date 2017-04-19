<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class Image {
	static function able($path) {
		switch (@exif_imagetype($path)) {
			case(IMAGETYPE_GIF):  return 'image/gif';
			case(IMAGETYPE_JPEG): return 'image/jpg';
			case(IMAGETYPE_PNG):  return 'image/png';
		}
	}
    static function create($w, $h) { return new Image(imagecreatetruecolor($w, $h) ); }

	function __construct($path = null) {
		if ($path) {
			$this->from($path);
		}
	}
	function fromjpeg($path)	{ $this->Img = imagecreatefromjpeg($path); }
	function fromgif($path)		{ $this->Img = imagecreatefromgif($path); }
	function frompng($path)		{ $this->Img = imagecreatefrompng($path); }
	function fromstring($str)	{ $this->Img = imagecreatefromstring($str); }
	function from($path) {
		if (is_resource($path)) {
			$this->Img = $path;
			return;
		}
		switch (@exif_imagetype($path)) {
	        case(IMAGETYPE_GIF) : return $this->fromgif($path);
	        case(IMAGETYPE_JPEG): return $this->fromjpeg($path);
	        case(IMAGETYPE_PNG):  return $this->frompng($path);
		}
		return false;
	}
	function copyresized($newImg, $dst_x=null, $dst_y=null, $src_x=null, $src_y=null, $dst_w=null, $dst_h=null, $src_w=null, $src_h=null) {
		imagealphablending($newImg, false);
		imagesavealpha    ($newImg, true);

		$sIndex = imagecolortransparent($this->Img);
		$tColor = $sIndex < 0 ? ['red' => 255, 'green' => 255, 'blue' => 255] : @imagecolorsforindex($this->Img, $sIndex);
		$tIndex = imagecolorallocatealpha($newImg, $tColor['red'], $tColor['green'], $tColor['blue'], 127);
		imagefill($newImg, 0, 0, $tIndex);

		imagecopyresampled($newImg, $this->Img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}
	function copy($new, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h) {
		imagecopy($new, $this->Img, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}
	function getCopyresized($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		$new = Image::create($dst_w, $dst_h);
		$this->copyresized($new->Img,  $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		return $new;
	}
	function getCroped($x, $y, $w, $h) {
		$new = Image::create($w, $h);
		$this->copy($new, 0, 0, $x, $y, $w, $h);
		return $new;
	}
	function getAutoCroped($w, $h, $verticalPosition = 20, $horizontalPosition = 60, $zoom = false) {
		$dstW = $this->x();
		$dstH = $this->y();
		image::makeProportional($w, $h, $dstW, $dstH);
		if ($zoom) {
			$defaultZoom = $dstW / $w;
			if ($zoom < $defaultZoom) {
				$f = $defaultZoom * 1/$zoom;
				$dstW *= 1/$f;
				$dstH *= 1/$f;
			}
		}
		$src_y = ($this->y() - $dstH) * $verticalPosition / 100;
		$src_x = ($this->x() - $dstW) * $horizontalPosition / 100;
		$new = $this->getCopyresized(0, 0, $src_x, $src_y, $w, $h, $dstW, $dstH);
		return $new;
	}
	function getResized($w, $h, $prop = false) {
		$dstW = $this->x();
		$dstH = $this->y();
		if ($prop) {
			image::makeProportional($dstW, $dstH, $w, $h);
		}
		$new = $this->getCopyresized(0, 0, 0, 0, $w, $h, $dstW, $dstH);
		return $new;
	}
	function addImg($Img, $x, $y, $scale = 1) {
		if (!$Img instanceof Image) {
			$Img = new Image($Img);
		}
		$w = $Img->x();
		$h = $Img->y();
		imagecopyresampled($this->Img, $Img->Img, $x, $y, 0, 0, $w*$scale, $h*$scale, $w, $h);
	}
	function colorallocate($color, $alpha=0) {
			list($r, $g, $b) = color::toArray($color);
			return imagecolorallocatealpha($this->Img, $r, $g, $b, $alpha);
	}
	function fill($color, $alpha = 0) {
		if (is_string($color)) {
			$color = $this->colorallocate($color, $alpha);
		}
		imagefill($this->Img, 0, 0, $color);
		return $color;
	}
	function saveAs($path, $type = '', $quality = 92) {
		!$type && ($type = strtolower(preg_replace('/.*\./', '', $path)));
		switch ($type) {
			case 'gif':
				imagegif($this->Img, $path);
				break;
			case 'png':
				imagepng($this->Img, $path);
				break;
			case 'jpeg':
			case 'jpg':
			default:
				imageinterlace($this->Img, 1);
				imagejpeg($this->Img, $path, $quality);
		}
	}
	function x() { return imagesx($this->Img); }
	function y() { return imagesY($this->Img); }

	static function makeProportional($musterW, $musterH, &$width, &$height) {
		if ((!$height || $musterW/$musterH > $width/$height) && $width != 0) {
			$height = round(($musterH / $musterW) * $width);
		} else {
			$width  = round(($musterW / $musterH) * $height);
		}
	}
}
