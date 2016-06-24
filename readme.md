Windwork 图片处理组件
=============================
支持图片缩略图和打水印，兼容各种第三方云存贮。

## 生成缩略图
```
$img = \wf\image\ImageFactory::create();
// 设置源图片内容
$img->setImage(file_get_contents('src_image/1.png'));

// 生成200x100的缩略图，超过比例截掉
$img->thumb(200, 100, 'dist_image/thumb.png.cut_200x100.jpg', true);
// 生成100x200的缩略图，超过比例截掉
$img->thumb(100, 200, 'dist_image/thumb.png.cut_100x200.jpg', true);
// 生成200x100的缩略图，超过比例则补白色背景
$img->thumb(200, 100, 'dist_image/thumb.png.uncut_200x100.jpg', false);
// 生成100x200的缩略图，超过比例则补白色背景
$img->thumb(100, 200, 'dist_image/thumb.png.uncut_100x200.jpg', false);
```

## 打水印

```
$img = \wf\image\ImageFactory::create();
// 设置源图片内容
$img->setImage(file_get_contents('src_image/1.png'));

// 给图片内容打水印后，保存到'dist_image/water.png.jpg'
$img->watermark('src_image/logo.png', 'dist_image/water.png.jpg');

// 给图片内容打水印后，返回打水印后的二进制图片内容
$img = $img->watermark('src_image/logo.png');

```

```
<?php
namespace wf\image;

/**
 * 图像处理接口
 */
interface IImage {
    /**
     * 设置图片二进制内容
     * 
     * @param string $imageContent
     * @return \wf\image\Image
     */
    public function setImage($imageContent);
    
    /**
     * 生成缩略图
     * 宽度不小于$thumbWidth或高度不小于$thumbHeight的图片生成缩略图
     * 建议缩略图和被提取缩略图的文件放于同一目录，文件名为“被提取缩略图文件.thumb.jpg”
     *
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @param string $thumbPath 缩略保存的图完整路径，为空则返回字符串格式的缩略图二进制内容
     * @param bool $isCut = true 是否裁剪图片，true）裁掉超过比例的部分；false）不裁剪图片，图片缩放显示，增加白色背景
     * @return bool|string
     * @throws \wf\image\Exception
     */
    public function thumb($thumbWidth, $thumbHeight, $thumbPath, $isCut = true);
    
    /**
     * 给图片打水印
     * 建议用gif或png图片做水印，jpg不能设置透明，故不推荐用
     *
     * @param string $watermarkFile = 'static/images/watermark.png' 水印图片
     * @param string $distFile = null 水印图片保存路径，为空则返回打水印后的字符串格式的图片二进制内容
     * @param int $watermarkPlace = 9 水印放置位置 1:左上, 2：中上， 3右上, 4：左中， 5：中中， 6：右中，7：左下， 8：中下，9右下
     * @param int $watermarkQuality = 85 被打水印后的新图片(相对于打水印前)质量百分比
     * @return bool|string
     */
    public function watermark($watermarkFile = 'static/images/watermark.png', $distFile = null, $watermarkPlace = 9, $watermarkQuality = 85);
}
```