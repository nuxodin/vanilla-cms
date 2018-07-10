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
    static function create($w, $h) { return new Image(imagecreatetruecolor(max(1,$w), max(1,$h)) ); }

	function __construct($path = null) {
		if ($path) $this->from($path);
	}
	// ignore warnings like "gd-jpeg, libjpeg: recoverable error: Invalid SOS parameters for sequential JPEG" and "gd-jpeg, libjpeg: recoverable error: Invalid SOS parameters for sequential JPEG"
	function fromjpeg($path)	{ $this->Img = @imagecreatefromjpeg($path); }
	function fromgif($path)		{ $this->Img = imagecreatefromgif($path); }
	function frompng($path)		{ $this->Img = imagecreatefrompng($path); }
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
	function getCopyresized($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		$new = Image::create($dst_w, $dst_h);
		$this->copyresized($new->Img,  $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
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
	function getResized($w, $h, $proportional = false) {
		$dstW = $this->x();
		$dstH = $this->y();
		if ($proportional) image::makeProportional($dstW, $dstH, $w, $h);
		$new = $this->getCopyresized(0, 0, 0, 0, $w, $h, $dstW, $dstH);
		return $new;
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

	// https://secure.php.net/manual/en/function.imagecreatefromgif.php#104473
	static function is_gif_animated($path) {
		if (!($fh = @fopen($path, 'rb'))) return false;
		$count = 0;
		while (!feof($fh) && $count < 2) {
			$chunk = fread($fh, 1024 * 100); //read 100kb at a time
			$count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
		}
		fclose($fh);
		return $count > 1;
	}
}
