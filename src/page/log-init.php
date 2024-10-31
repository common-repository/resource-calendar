<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Log_Init extends ResourceCalendar_Page {

	private $init_datas =  null;
	private $get_cnt = '';

	public function __construct() {
		parent::__construct();

		$this->get_cnt = intval($_POST['get_cnt']);
	}

	public function set_init_datas($init_datas) {
		$this->init_datas = $init_datas;

	}

	public function get_cnt () {
		return $this->get_cnt;
	}


	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['rcal_logged_day'] = htmlspecialchars($d1['logged_day'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_logged_time'] = htmlspecialchars($d1['logged_time'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_operation'] = htmlspecialchars($d1['operation'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			unset($this->init_datas[$k1]['logged_day']);
			unset($this->init_datas[$k1]['logged_time']);
			unset($this->init_datas[$k1]['operation']);
			unset($this->init_datas[$k1]['remark']);
		}

		$this->echoInitData($this->init_datas);
	}
}