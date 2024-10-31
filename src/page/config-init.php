<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Config_Init extends ResourceCalendar_Page {

	private $init_datas =  null;

	public function __construct() {
		parent::__construct();
	}

// 	public function get_init_datas() {
// 		return $this->init_datas;
// 	}
	public function set_init_datas($init_datas) {
		$this->init_datas = $init_datas;

	}

	public function get_target_year () {
		return $_POST['target_year'];
	}


	public function show_page() {
		$this->echoInitData($this->init_datas);
	}
}