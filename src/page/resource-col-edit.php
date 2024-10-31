<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Resource_Col_Edit extends ResourceCalendar_Page {

	private $table_data = null;


	public function __construct() {
		parent::__construct();
	}

	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function check_request() {


		if (  !isset($_POST['resource_cd']) || ResourceCalendar_Component::isRequestEmpty($_POST['resource_cd']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),1 );
		}
		if (  !isset($_POST['column']) || ResourceCalendar_Component::isRequestEmpty($_POST['column']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),2 );
		}
		if (  !isset($_POST['value']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),3 );
		}

		$col = intval($_POST['column']);
		$check_item = '';
		$meta = '';

		switch (intval($_POST['column'])) {
			case 2:
				$check_item = 'name';
				break;
//datatableでは更新可能にしない
// 			case 3:
// 				$check_item = 'valid_from';
// 				break;
// 			case 4:
// 				$check_item = 'valid_to';
// 				break;
			case 6:
				$check_item = 'remark';
				break;
		}
		if (empty($check_item)) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,4);
		}
		$msg = '';
		if (ResourceCalendar_Page::serverCheck(array(),$msg) == false) return false;
		if (ResourceCalendar_Page::serverColumnCheck($_POST['value'],$check_item,$msg) == false ) {
			throw new ResourceCalendarException($msg ,1);
		}
	}

	public function show_page() {

		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode(htmlspecialchars($this->table_data['value'],ENT_QUOTES)).' }';
	}


}