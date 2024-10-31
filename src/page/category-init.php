<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Category_Init extends ResourceCalendar_Page {

	private $init_datas =  null;

	function __construct() {
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

			$this->init_datas[$k1]['rcal_category_cd'] = $d1['category_cd'];
			$this->init_datas[$k1]['rcal_category_patern'] = $d1['category_patern'];
			$this->init_datas[$k1]['rcal_category_name'] = htmlspecialchars($d1['category_name']);
			$this->init_datas[$k1]['rcal_category_values'] = htmlspecialchars($d1['category_values']);
			$this->init_datas[$k1]['rcal_display_sequence'] = $d1['display_sequence'];
			//remarkは項目としてないが、見栄えのために現状いれている
			$this->init_datas[$k1]['rcal_remark'] = "";
			unset ($this->init_datas[$k1]['category_cd'] );
			unset ($this->init_datas[$k1]['category_patern'] );
			unset ($this->init_datas[$k1]['category_name'] );
			unset ($this->init_datas[$k1]['category_values'] );
			unset ($this->init_datas[$k1]['display_sequence'] );
		}
		$this->echoInitData($this->init_datas);
	}
}