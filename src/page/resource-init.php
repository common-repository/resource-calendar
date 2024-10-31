<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Resource_Init extends ResourceCalendar_Page {

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
			$this->init_datas[$k1]['rcal_display_sequence'] = $d1['display_sequence'];
			$this->init_datas[$k1]['rcal_max_setting'] = $d1['max_setting'];
			$this->init_datas[$k1]['rcal_setting_patern_cd'] = $d1['setting_patern_cd'];
			$this->init_datas[$k1]['rcal_setting_data'] = $d1['setting_data'];

			unset($this->init_datas[$k1]['max_setting']);
			unset($this->init_datas[$k1]['setting_patern_cd']);
			unset($this->init_datas[$k1]['setting_data']);
			unset($this->init_datas[$k1]['remark']);
			unset($this->init_datas[$k1]['name']);
			unset($this->init_datas[$k1]['valid_from']);
			unset($this->init_datas[$k1]['valid_to']);
			unset($this->init_datas[$k1]['display_sequence']);
			unset($this->init_datas[$k1]['notes']);
			unset($this->init_datas[$k1]['memo']);
		}
		$this->echoInitData($this->init_datas);
	}
}