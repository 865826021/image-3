<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2016 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\image;

/**
 * 静态创建图片处理（截图、水印）类实例工厂类
 * 
 * @package     wf.image
 * @author      erzh <cmpan@qq.com>
 * @link        http://www.windwork.org/manual/wf.image.html
 * @since       0.1.0
 */
final class ImageFactory {
	/**
	 * 
	 * @var array
	 */
	private static $instance = array();
		
	/**
	 * 创建图片处理组件实例
	 * @param array $cfg
	 * @return \wf\image\IImage
	 */
	public static function create($adapter = 'GD') {
		// 获取带命名空间的类名
		$class = "\\wf\\image\\adapter\\{$adapter}";

		// 如果该类实例未初始化则创建
		if(empty(static::$instance[$adapter])) {
			static::$instance[$adapter] = new $class();
		}
		
		return static::$instance[$adapter];
	}
}


