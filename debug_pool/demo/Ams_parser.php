<?php
// 引入公共文件
include_once('Http_client.php');
include_once('Course.php');
include_once('User.php');

/**
 * 教务系统信息获取和处理类
 *
 * @package Cassistant
 * @author solo
 **/
class Ams_parser
{
	/**
	 * 构造函数
	 *
	 * @param array 用户的登录信息
	 * @return void
	 * @author solo
	 **/
	function __construct($params)
	{
		$this->host = 'jwxt.scuec.edu.cn';
		$this->user_type = $params['type'];
		$this->user_id   = $params['id'];
		$this->user_pws  = $params['password'];
	}

	/**
	 * 登录教务系统
	 *
	 * @return string or login_failed
	 * @author solo
	 **/
	private function login()
	{
		$client = new HttpClient($this->host);

		// 禁止自动跳转
		$client->setHandleRedirects(false);

		// 伪造浏览器
		$client->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.3a) Gecko/20021207');
		$client->referer = "http://".$this->host."/";

		// 得到__VIEWSTATE
		$client->get('/');
		$pageContents = $client->getContent();
		preg_match("/name=\"__VIEWSTATE\" value=\"([\w\W]*?)\" \/>/", $pageContents, $viewstate);
		$viewstate = $viewstate[1];
		
		// 进行登录
		$client->post(
			'/default2.aspx',
			array(
				'TextBox1'         => $this->user_id,				// 学号或工号
				'TextBox2'         => $this->user_pws,				// 密码
				'__VIEWSTATE'      => $viewstate,
				'RadioButtonList1' => '%D1%A7%C9%FA',
				'Button1'          => ''
			)
		);
		
		// 得到头信息,用于获取session
		$headers = $client->getHeaders();

		if(isset($headers['location']))
		{
			$cookies = $client->getCookies();

			// 得到课表页信息
			$client->referer = "http://jwxt.scuec.edu.cn/xs_main.aspx?xh=".$this->user_id;
			$client->get("/xskbcx.aspx?xh=".$this->user_id."&xm=%CE%E9%D0%C7&gnmkdm=N121603");

			// 转换编码
			$table_content = $client->get_utf8_content("GBK");

			return $table_content;
		}
		else
		{
			return "login_failed";
		}
	}

	/**
	 * 得到个人信息和课表信息
	 *
	 * @return 返回一个User对象 
	 * @author solo
	 **/
	public function get()
	{
		// 登录
		$content = $this->login();
//		$content = file_get_contents("./qi.html");

		if($content === "login_failed")				// 登录失败
		{
			echo "login_failed";
		}
		else										// 登录成功
		{
			// 得到课表信息
			preg_match_all("/<td align=\"Center\" rowspan=\"[2,3]\"[>]?([\w\W]*?)<\/td>/i", $content, $courses);

			//过滤多余字符
			$courses =str_replace(" width=\"7%\">", "", $courses[1]);
			$courses =str_replace("<br><br><br><br>", "<br>", $courses);
			$courses=str_replace("<br><br>", "", $courses);

			// 初始化课程数组列表
			$course_list = array();

			// 遍历,得到具体课表信息
			foreach ($courses as $key => $value) 
			{
				
				//	分割出课程具体信息
				$course = explode("<br>", $value);

				for($i=0; $i< count($course)/4;$i++)
				{
					// 实例化课程信息类
					$c = new Course();

					// 得到课程名
					$name = trim($course[ $i*4+0 ]);

					// 得到课是周几的(正则匹配一、二、三、四、五、六、七、天、日的utf8码)
					preg_match('~[\x{4e00},\x{4e8c},\x{4e09},\x{56db},\x{4e94},\x{516d},\x{4e03}]+~u', $course[ $i*4+1 ], $day);

					// 得到节数
					preg_match("/[\d{,2},\d{,2}]{2,}/", $course[ $i*4+1 ],$sec);
					$sec_tmp = explode(",", $sec[0]);				// 分割出起始和结束日期

					// 填充课程起始和结束时间
					$day_time_list = array(
							"0" => Course::section_to_time($sec_tmp[0]),											// 起始时间
							"1" => strtotime("+45 minute", Course::section_to_time($sec_tmp[count($sec_tmp)-1]))	// 结束时间
						);

					// 得到课程上课周数
					preg_match_all("/\d{1,2}-\d{1,2}/", $course[ $i*4+1 ],$weeks); 

					// 分解出起始和结束周
					$week_list 	  = explode("-", $weeks[0][0]);
					$week_list_array = array();
					$list_num = $week_list[1]-$week_list[0]+1;

					// 判断是否为单双周
					preg_match("/单周/", $course[ $i*4+1 ],$both);
					preg_match("/双周/", $course[ $i*4+1 ],$odd);
					if(isset($both[0]) && $both[0] == "单周")				// 单周
					{
						$add_week = 2;				// 课程周数加二
						$list_num /=2;				// 课程上课总周数减半
						if($week_list[0]%2 == 0 )	// 防止教务系统中开始周数为偶数
							$week_list[0] += 1;
					}
					elseif(isset($odd[0]) && $odd[0] == "双周")				//双周
					{
						$add_week = 2;				
						$list_num /=2;				
						$week_list[0];
						if($week_list[0]%2 == 1 )
							$week_list[0] += 1;
					}

					else 							// 单双周都要上课
					{
						$add_week = 1;				// 课程周数加一
					}

					$add_week_tmp = 0;				// 初始化周数递增变量
					// 填充课程上课的周数
					for ($j=0; $j < $list_num; $j++) 
					{ 	
						array_push($week_list_array, $week_list[0]+$add_week_tmp);
						$add_week_tmp += $add_week;				// 周的加数递增
					}

					$c->name          = $name;
					$c->day           = Course::day_to_int(  $day[0]  );
					$c->day_time_list = $day_time_list;
					$c->week_list     = $week_list_array;
					$c->teacher       = trim($course[ $i*4+2 ]);
					$c->location      = trim($course[ $i*4+3 ] );
					
					$course_list[] = $c;				// 装填课表
					
				}
			}

			// 实例化用户信息
			$u = new User();

			// 匹配到用户基本信息的大体位置
			preg_match("/<TR class=\"trbg1\">([\w\W]*?)<\/TR>/i", $content, $user_infos_content);

			// 匹配到用户的各项信息所在位置
			preg_match_all("/<span id=\"Label[\d]\"([\w\W]*?)<\/span>/", $user_infos_content[0], $user_infos);

			// 循环得到需要的用户信息
			foreach ($user_infos[1] as $key => $value) 
			{
				$user_info = explode("：", $value);
				$user_info_tmp[$key] = $user_info[1];				// 用户信息存入临时变量
			}

			// 填充用户信息
			$u->id          = trim($user_info_tmp[0]);
			$u->name        = trim($user_info_tmp[1]);
			$u->course_list = $course_list;
			$u->department  = trim($user_info_tmp[2]);
			$u->subject     = trim($user_info_tmp[3]);
			$u->class       = trim($user_info_tmp[4]);
		}
		
		return $u;
	}

	/*********************** 私有区 ****************************/
	private $host;				//教务系统地址
	private $user_id;			//学号或工号
	private $user_pws;			//密码
}