<?php
// error_reporting(0);
// 引入公共文件
include_once('Http_client.php');
include_once('simple_html_dom.php');
include_once('Activity.php');
include_once('debug_helper.php');
/**
 * 教务系统信息获取和处理类
 *
 * @package quan-tree
 * @author solo
 **/
class Duocai_parser
{
	/**
	 * 构造函数
	 *
	 * @param array 传入信息
	 * @return void
	 * @author solo
	 **/
	function __construct($params)
	{
		$this->host = $params['host'];
	}

	/**
	 * 得到多彩列表
	 *
	 * @return 返回一个Activity对象 
	 * @author solo
	 **/
	public function get()
	{
		$client = new HttpClient($this->host);

		// 禁止自动跳转
		$client->setHandleRedirects(false);

		// 伪造浏览器
		$client->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.3a) Gecko/20021207');
		$client->referer = "http://".$this->host."/";

		// 得到网站根目录
		if (!$client->get('/'))
		{
			die('404');				// 返回错误代码,获取失败
		}
		$pageContents = $client->getContent();
//		var_dump($pageContents);

		log_time();

		// 初始化html_dom类
		$dom = new simple_html_dom();
//		log_time();
		// 创建html dom树
		$dom->load($pageContents, true, true);
//		log_time();
		// 初始化活动列表
		$act_list = array();

		foreach ($dom->find('div[class=event clearfix]') as $e) 
		{
			$act = new Activity();
			//echo $e->children(0)->children(0)->children(0)->style;
			$act->title = $e->children(1)->children(0)->plaintext;
			$act->stime = $e->children(1)->children(1)->plaintext;
			$act->place = $e->children(1)->children(2)->plaintext;
			$act->type = $e->children(1)->children(3)->plaintext;

			$act->organizer = $e->children(1)->children(4)->plaintext;
			$act_list[] = $act;
		}
		e($act_list);
//		log_time();
	}

	/*********************** 私有区 ****************************/
	private $host;				//网站地址
}

$data = array("host" => "duocaiii.com");

$D = new Duocai_parser($data);
$D->get();