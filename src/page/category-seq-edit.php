<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Category_Seq_Edit extends ResourceCalendar_Page {

	private $table_data = null;

	function __construct() {
		parent::__construct();
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function check_request() {

		if (empty($_POST['type'])) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		if ( !isset($_POST['rcal_category_cd']) || '' == strval($_POST['rcal_category_cd']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		if ( empty($_POST['value']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3 );
		}
		$msg = '';
		if (ResourceCalendar_Page::serverCheck(array(),$msg) == false) return;
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":"" }';
	}


}