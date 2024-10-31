<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Reservation_Init extends ResourceCalendar_Page {

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


	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['rcal_remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_name'] = htmlspecialchars($d1['name'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_valid_from'] = htmlspecialchars($d1['valid_from'],ENT_QUOTES);
			$this->init_datas[$k1]['rcal_valid_to'] = htmlspecialchars($d1['valid_to'],ENT_QUOTES);

			$this->init_datas[$k1]['memo'] = unserialize($d1['memo']);


		}
		$this->echoInitData($this->init_datas);
	}


}