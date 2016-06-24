<?php 
PHP_SAPI == 'cli' || die('access denied');
$start = microtime(1);

require_once dirname(__DIR__) . '/IImage.php';
require_once dirname(__DIR__) . '/Exception.php';
require_once dirname(__DIR__) . '/adapter/GD.php';
require_once dirname(__DIR__) . '/ImageFactory.php';

$img = \wf\image\ImageFactory::create();

# 缩略图
// test png
$img->setImage(file_get_contents('src_image/1.png'));
$img->thumb(0,   100, 'dist_image/thumb.png.cut_0x100.jpg', true);
$img->thumb(200, 0,   'dist_image/thumb.png.cut_200x0.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.png.cut_200x100.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.png.uncut_200x100.jpg', false);
$img->thumb(100, 200, 'dist_image/thumb.png.cut_100x200.jpg', true);
$img->thumb(100, 200, 'dist_image/thumb.png.uncut_100x200.jpg', false);

// test jpg
$img->setImage(file_get_contents('src_image/2.jpg'));
$img->thumb(0,   100, 'dist_image/thumb.jpg.cut_0x100.jpg', true);
$img->thumb(200, 0,   'dist_image/thumb.jpg.cut_200x0.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.jpg.cut_200x100.jpg', true);
$img->thumb(100, 200, 'dist_image/thumb.jpg.cut_100x200.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.jpg.uncut_200x100.jpg', false);
$img->thumb(100, 200, 'dist_image/thumb.jpg.uncut_100x200.jpg', false);

// test gif
$img->setImage(file_get_contents('src_image/3.gif'));
$img->thumb(0,   100, 'dist_image/thumb.gif.cut_0x100.jpg', true);
$img->thumb(200, 0,   'dist_image/thumb.gif.cut_200x0.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.gif.cut_200x100.jpg', true);
$img->thumb(100, 200, 'dist_image/thumb.gif.cut_100x200.jpg', true);
$img->thumb(200, 100, 'dist_image/thumb.gif.uncut_200x100.jpg', false);
$img->thumb(100, 200, 'dist_image/thumb.gif.uncut_100x200.jpg', false);

# 打水印
$img->setImage(file_get_contents('src_image/1.png'));
$img->watermark('src_image/logo.png', 'dist_image/water.png.jpg');

$img->setImage(file_get_contents('src_image/2.jpg'));
$img->watermark('src_image/logo.png', 'dist_image/water.jpg.jpg');

$img->setImage(file_get_contents('src_image/3.gif'));
$img->watermark('src_image/logo.png', 'dist_image/water.gif.jpg');

$end = microtime(1);
$time = $end - $start;

print "测试完成，用时{$time}毫秒";
