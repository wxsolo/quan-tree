<?php
/**
 * 基础活动类
 *
 * @package quan-tree
 * @author solo
 **/
class Activity
{
	public $school;			// 学校
	public $title;			// 主题
	public $stime;			// 开始时间
	public $etime;			// 结束时间
	public $place;			// 地点
	public $type;			// 类型
	public $organizer;		// 主办方

	/**
	 * 合并多个Activity对象的信息
	 *
	 * @param  array 要合并的Activity对象的列表
	 * @return Activity  返回一个Activity对象
	 * @author solo
	 **/
	public static function merge($act)
	{
		// TODO: 完成函数功能
	}
}