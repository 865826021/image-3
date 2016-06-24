<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2015 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\image\adapter;

use \wf\image\Exception;

/**
 * 图片处理类，使用GD2生成缩略图和打水印 
 *
 * @package     wf.image.adapter
 * @author      erzh <cmpan@qq.com>
 * @link        http://www.windwork.org/manual/wf.image.html
 * @since       0.1.0
 */
class GD implements \wf\image\IImage {
	/**
	 * 图片相关信息
	 * 
	 * @var array
	 */
	protected $imgInfo = '';
	
	/**
	 * 图片二进制内容
	 * @var string
	 */
	protected $imageContent = '';
		
	/**
	 * 构造函数中设置内存限制多一点以能处理较大图片
	 * @throws \wf\image\Exception
	 */
	public function __construct() {
		if (!function_exists('gd_info')) {
			throw new Exception('你的php没有使用gd2扩展,不能处理图片');
		}
		@ini_set("memory_limit", "128M");  // 处理大图片的时候要较很大的内存
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\image\IImage::setImage()
	 * @throws \wf\image\Exception
	 */
	public function setImage($imageContent) {
		if (!$imageContent || false == ($this->imgInfo = @getimagesizefromstring($imageContent))) {
			throw new Exception('错误的图片文件！');;
		}
		
		$this->imageContent = $imageContent;
		
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\image\IImage::thumb()
	 */
	public function thumb($thumbWidth, $thumbHeight, $thumbPath, $isCut = true) {
		if($isCut) {
			return $this->thumbCutOut($thumbWidth, $thumbHeight, $thumbPath);
		} else {
			return $this->thumbUnCut($thumbWidth, $thumbHeight, $thumbPath);
		}
	}
	
	/**
	 * 不裁剪方式生成缩略图
	 * @param int $thumbWidth
	 * @param int $thumbHeight
	 * @param string $thumbPath
	 * @return bool | string
	 * @throws \wf\image\Exception
	 */
	private function thumbUnCut($thumbWidth, $thumbHeight, $thumbPath) {
		list($srcW, $srcH) = $this->imgInfo;
		
		// 宽或高按比例缩放
		if ($thumbWidth == 0 || $thumbHeight == 0) {
			if ($thumbWidth == 0) {
				$thumbHeight = $srcH * ($thumbWidth / $srcW);
			} else {
				$thumbWidth = $thumbWidth = $srcW * ($thumbHeight/$srcH);
			}
			$imgW = $thumbWidth; // 图片显示宽
			$imgH = $thumbHeight; // 图片显示高
			$posX = 0; 
			$posY = 0;
		} else {
			if ($thumbWidth/$thumbHeight < $srcW/$srcH) {
				// 宽比例超过，补上高
				$imgW = $thumbWidth; // 图片显示宽
				$imgH = $thumbWidth * $srcH / $srcW; // 图片显示高
				$posX = 0;
				$posY = ($thumbHeight - $imgH) / 2;
			} else {
				// 高比例超过，补上宽
				$imgH = $thumbHeight; // 图片显示宽
				$imgW = $thumbHeight * $srcW / $srcH; // 图片显示高
				$posX = ($thumbWidth - $imgW) / 2;
				$posY = 0;
			}
		}
		
		$thumbImage = imagecreate($thumbWidth, $thumbHeight);
		
		// 填充背景色
		$fillColor = imagecolorallocate($thumbImage, 0xff, 0xff, 0xff);
		imagefill($thumbImage, 0, 0, $fillColor);

		// 合上图
		$attachImage = imagecreatefromstring($this->imageContent);
		imagecopyresized($thumbImage, $attachImage, $posX, $posY, 0, 0, $imgW, $imgH, $srcW, $srcH);
		
		// 为兼容云存贮设备，不直接把缩略图写入文件系统，而是返回文件内容
		ob_start();
		imagejpeg($thumbImage, null, 95);
		$thumb = ob_get_clean();

		imagedestroy($attachImage);
		imagedestroy($thumbImage);
		
		if(!$thumb) {
			throw new Exception('无法生成缩略图');
		}
		
		if (!$thumbPath) {
			return $thumb;
		}
			
		if (!is_dir(dirname($thumbPath))) {
			@mkdir(dirname($thumbPath), 0755, true);
		}
			
		return file_put_contents($thumbPath, $thumb);
	}
	
	/**
	 * 裁剪方式生成缩略图
	 * @param int $thumbWidth
	 * @param int $thumbHeight
	 * @param string $thumbPath
	 * @return bool | string
	 * @throws \wf\image\Exception
	 */
	private function thumbCutOut($thumbWidth, $thumbHeight, $thumbPath) {
		list($srcW, $srcH) = $this->imgInfo;
		$imgH = $srcH;
		$imgW = $srcW;
		
		$attachImage = imagecreatefromstring($this->imageContent);
			
		$thumbWidth > 0 || $thumbWidth = $srcW * ($thumbHeight/$srcH);
		$xRatio = $thumbWidth / $imgW;  // 宽比率
		$thumbHeight || $thumbHeight = $imgH*$xRatio;
			
		if ($imgW >= $thumbWidth || $imgH >= $thumbHeight ||
		  ($srcW < $thumbWidth || $srcH < $thumbHeight && ($thumbWidth && $thumbHeight))) {
			// 高需要截掉
			if(($xRatio * $imgH) > $thumbHeight) {
				$imgH = ($imgW / $thumbWidth) * $thumbHeight;
			} else {
				// 宽需要截掉
				$imgW = ($imgH / $thumbHeight) * $thumbWidth;
			}
		}
				
		// 缩略图一律用jpg格式文件，如果不设置缩略图保存路径则保存到原始文件所在目录
		$thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
		if($this->imgInfo['mime'] == 'image/gif') {
			imagecolortransparent($attachImage, imagecolorallocate($attachImage, 255, 255, 255));
		} else if($this->imgInfo['mime'] == 'image/png') {
			imagealphablending($thumbImage , false);//关闭混合模式，以便透明颜色能覆盖原画布
			imagesavealpha($thumbImage, true);
		}
	    
		// 重采样拷贝部分图像并调整大小到$thumbImage
		$srcX = floor(($srcW - $imgW)/2);
		$srcY = floor(($srcH - $imgH)/2);
		imagecopyresampled($thumbImage, $attachImage ,0, 0, $srcX, $srcY, $thumbWidth, $thumbHeight, $imgW, $imgH);
		
		// 为兼容云存贮设备，这里不直接把缩略图写入文件系统
		ob_start();
		imagejpeg($thumbImage, null, 95);
		$thumb = ob_get_clean();

		imagedestroy($attachImage);
		imagedestroy($thumbImage);
		
		if(!$thumb) {
			throw new Exception('无法生成缩略图');
		}

		if (!$thumbPath) {
			return $thumb;
		}
		
		if (!is_dir(dirname($thumbPath))) {
			@mkdir(dirname($thumbPath), 0755, true);
		}
			
		return file_put_contents($thumbPath, $thumb);
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\image\IImage::watermark()
	 */
	public function watermark($watermarkFile = 'static/images/watermark.png', $distFile = null, $watermarkPlace = 9, $watermarkQuality = 85) {
		@list($imgW, $imgH) = $this->imgInfo;
		
		$watermarkInfo	= @getimagesize($watermarkFile);
		$watermarkLogo	= ('image/png' == $watermarkInfo['mime']) ? @imagecreatefrompng($watermarkFile) : @imagecreatefromgif($watermarkFile);

		if(!$watermarkLogo) {
			return;
		}

		list($logoW, $logoH) = $watermarkInfo;
		$wmwidth = $imgW - $logoW;
		$wmheight = $imgH - $logoH;

		if(is_readable($watermarkFile) && $wmwidth > 10 && $wmheight > 10) {
			switch($watermarkPlace) {
				case 1:
					$x = +5;
					$y = +5;
					break;
				case 2:
					$x = ($imgW - $logoW) / 2;
					$y = +5;
					break;
				case 3:
					$x = $imgW - $logoW - 5;
					$y = +5;
					break;
				case 4:
					$x = +5;
					$y = ($imgH - $logoH) / 2;
					break;
				case 5:
					$x = ($imgW - $logoW) / 2;
					$y = ($imgH - $logoH) / 2;
					break;
				case 6:
					$x = $imgW - $logoW;
					$y = ($imgH - $logoH) / 2;
					break;
				case 7:
					$x = +5;
					$y = $imgH - $logoH - 5;
					break;
				case 8:
					$x = ($imgW - $logoW) / 2;
					$y = $imgH - $logoH - 5;
					break;
				case 9:
					$x = $imgW - $logoW - 5;
					$y = $imgH - $logoH - 5;
					break;
			}

			$dstImage = imagecreatetruecolor($imgW, $imgH);
			imagefill($dstImage, 0, 0, imagecolorallocate($dstImage, 0xFF, 0xFF, 0xFF));			
			$targetImage = @imagecreatefromstring($this->imageContent);
			
			imageCopy($dstImage, $targetImage, 0, 0, 0, 0, $imgW, $imgH);
			imageCopy($dstImage, $watermarkLogo, $x, $y, 0, 0, $logoW, $logoH);

			if ($distFile) {
				$ret = imagejpeg($dstImage, $distFile, $watermarkQuality);
			} else {
				ob_start();
				imagejpeg($dstImage, null, $watermarkQuality);
				$ret = ob_get_clean();
			}

			return $ret;
		}
	}
}