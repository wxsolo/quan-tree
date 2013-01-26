<?php
	include('./Ams_parser.php');
	include('./debug_helper.php');
	function get_ams()
	{
		$ams = new Ams_parser('jwxt.scuec.edu.cn', '09101149', '957624');
		$u = $ams->get();
		e($u);
		echo log_time();
	}

	get_ams();