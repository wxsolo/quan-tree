<?php
/**
 * 基础用户类
 *
 * @package Cassistant
 * @author ys
 **/
class User
{
	public $id;				// 学号，或者教工号
	public $name;			// 姓名
	public $type;			// 类型，可能是学生或者教工
	public $course_list;	// 课表
	public $term;			// 学期
	public $department;		// 院系
	public $subject;		// 专业
	public $class;			// 班机

	/**
	 * 合并多个User对象的信息
	 *
	 * @param  array 要合并的User对象的列表
	 * @return User  返回一个User对象
	 * @author ys
	 **/
	public static function merge($users)
	{
		// TODO: 完成函数功能
	}
}