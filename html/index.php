<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// scan directories for images that still need to be done
$original = array_map('basename', glob('originals/*', GLOB_BRACE));
$resized = array_map('basename', glob('resized/*', GLOB_BRACE));
$missing_resized = array_diff($original, $resized);

if (count($missing_resized) == 0) {
	die('Done!');
}

$resized_directory = $_SERVER['DOCUMENT_ROOT'] . '/resized/';

$image = 'originals/' . array_values($missing_resized)[0];
$imagename = array_values($missing_resized)[0];
$path_parts = pathinfo($image);
$ext = '.' . $path_parts['extension'];

$source = imagecreatefromstring(file_get_contents($image));
$srcwidth = imagesx($source);
$srcheight = imagesy($source);
$srcratio = $srcwidth / $srcheight;

if ($srcratio < 1) { // tall image
	$targetratio = 400 / 600;
} else { // wide image
	$targetratio = 600 / 400;
}

if ($targetratio < $srcratio) { // make the image taller
	$newwidth = $srcwidth;
	$newheight = $srcwidth / $targetratio;
} elseif ($targetratio > $srcratio) { // make the image wider
	$newheight = $srcheight;
	$newwidth = $srcheight * $targetratio;
} else { // no changes needed
	copy($image, $resized_directory . $imagename);
	header('Location:index.php');
	exit;
}

$newimage = imagecreatetruecolor($newwidth, $newheight);
$white = imagecolorallocate($newimage, 255, 255, 255);
imagefill($newimage, 0, 0, $white);

$offsetwidth = round(($newwidth - $srcwidth) / 2);
$offsetheight = round(($newheight - $srcheight) / 2);

if ($offsetwidth == 0) { // top and bottom banners
	$bannerwidth = $srcwidth;
	$bannerheight = $offsetheight;
	$bannerimagetop = imagecreatetruecolor($bannerwidth, $bannerheight);
	$bannerimagetopname = str_replace($ext, '-top'.$ext, $imagename);
	$bannerimagebottom = imagecreatetruecolor($bannerwidth, $bannerheight);
	$bannerimagebottomname = str_replace($ext, '-bottom'.$ext, $imagename);
	imagecopy($bannerimagetop, $source, 0, 0, 0, 0, $bannerwidth, $bannerheight);
	imagejpeg($bannerimagetop, $resized_directory . $bannerimagetopname, 100);
	imagedestroy($bannerimagetop);
	exec('convert ' . escapeshellarg($resized_directory . $bannerimagetopname) . ' -blur 0x88 ' . escapeshellarg($resized_directory . $bannerimagetopname));
	imagecopy($bannerimagebottom, $source, 0, 0, 0, $srcheight-$bannerheight, $bannerwidth, $bannerheight);
	imagejpeg($bannerimagebottom, $resized_directory . $bannerimagebottomname, 100);
	exec('convert ' . escapeshellarg($resized_directory . $bannerimagebottomname) . ' -blur 0x88 ' . escapeshellarg($resized_directory . $bannerimagebottomname));
	imagedestroy($bannerimagebottom);
	// reload blurred images
	$bannerimagetop = imagecreatefromstring(file_get_contents($resized_directory . $bannerimagetopname));
	$bannerimagebottom = imagecreatefromstring(file_get_contents($resized_directory . $bannerimagebottomname));
	sleep(3);
	imagecopy($newimage, $bannerimagetop, 0, 0, 0, 0, $bannerwidth, $bannerheight);
	imagecopy($newimage, $bannerimagebottom, 0, $newheight-$bannerheight, 0, 0, $bannerwidth, $bannerheight);
} else { // left and right banners
	$bannerwidth = $offsetwidth;
	$bannerheight = $srcheight;
	// left banner
	$bannerimageleft = imagecreatetruecolor($bannerwidth, $bannerheight);
	$bannerimageleftname = str_replace($ext, '-left'.$ext, $imagename);
	imagecopy($bannerimageleft, $source, 0, 0, 0, 0, $bannerwidth, $bannerheight);
	imagejpeg($bannerimageleft, $resized_directory . $bannerimageleftname, 100);
	imagedestroy($bannerimageleft);
	exec('convert ' . escapeshellarg($resized_directory . $bannerimageleftname) . ' -blur 0x88 ' . escapeshellarg($resized_directory . $bannerimageleftname));

	// right banner
	$bannerimageright = imagecreatetruecolor($bannerwidth, $bannerheight);
	$bannerimagerightname = str_replace($ext, '-right'.$ext, $imagename);
	imagecopy($bannerimageright, $source, 0, 0, $srcwidth-$bannerwidth, 0, $bannerwidth, $bannerheight);
	imagejpeg($bannerimageright, $resized_directory . $bannerimagerightname, 100);
	imagedestroy($bannerimageright);
	exec('convert ' . escapeshellarg($resized_directory . $bannerimagerightname) . ' -blur 0x88 ' . escapeshellarg($resized_directory . $bannerimagerightname));

	sleep(3); // wait while image magick works

	// get banners and put them into our new main image
	$bannerimageleft = imagecreatefromstring(file_get_contents($resized_directory . $bannerimageleftname));
	$bannerimageright = imagecreatefromstring(file_get_contents($resized_directory . $bannerimagerightname));
	imagecopy($newimage, $bannerimageleft, 0, 0, 0, 0, $bannerwidth, $bannerheight);
	imagecopy($newimage, $bannerimageright, $newwidth-$bannerwidth, 0, 0, 0, $bannerwidth, $bannerheight);
}

imagecopy($newimage, $source, $offsetwidth, $offsetheight, 0, 0, $srcwidth, $srcheight);
imagejpeg($newimage, $resized_directory . $imagename, 100);

// cleanup
imagedestroy($source);
imagedestroy($newimage);
if (isset($bannerimagetop)) {
	imagedestroy($bannerimagetop);
	unlink($resized_directory . $bannerimagetopname);
}
if (isset($bannerimagebottom)) {
	imagedestroy($bannerimagebottom);
	unlink($resized_directory . $bannerimagebottomname);
}
if (isset($bannerimageleft)) {
	imagedestroy($bannerimageleft);
	unlink($resized_directory . $bannerimageleftname);
}
if (isset($bannerimageright)) {
	imagedestroy($bannerimageright);
	unlink($resized_directory . $bannerimagerightname);
}

header('Location:index.php');
exit;
?>