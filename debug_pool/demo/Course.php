<?php
/**
*   课程类，包含每个课程的全部信息。
*/
class Course
{
	// 所有元素不为空
	public $name;				// 课程名
	public $day;				// 课程所在星期
	public $week_list;			// 会在哪些周上课
	public $day_time_list;		// 二维，包含开始时间 和 结束时间, 类似 [ [8:00, 8:45], 9:00, 9:45] ]
	public $teacher;			// 教师名
	public $location;			// 教室位置

	// 一下是扩展属性，并不是所有课程都有
	public $score;				// 课程获得的分数。例如物理实验会有每节课的分数
	public $status;				// 是否缺课了。例如物理试验

	// 用于转换，汉字的星期
	public static function day_to_int($day)
	{
		return Course::$day_to_int[$day];
	}

    //  用于转换。上课节数对应的时间
    public static function section_to_time($section, $add_time = 0)
    {
        return Course::$section_to_time[$section];
    }

    /*********************** 私有区 ****************************/

    private static $section_to_time = array(
        '1' => "8:00",
        '2' => "8:55",
        '3' => "10:00",
        '4' => "10:55",
        '5' => "14:10",
        '6' => "15:05",
        '7' => "16:00",
        '8' => "16:55",
        '9' => "18:40",
        '10' => "19:30",
        '11' => "20:20"
    );

	private static $day_to_int = array(
		"一" => 1,
		"二" => 2,
		"三" => 3,
		"四" => 4,
		"五" => 5,
		"六" => 6,

		// 注意中文周日有三种叫法。
		"七" => 7,
		"日" => 7,
		"天" => 7
	);
}
