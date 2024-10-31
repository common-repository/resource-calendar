<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Search_Page extends ResourceCalendar_Page {

	private $search_items = null;
	private $result = null;
	private $isNodata = false;


	function __construct() {
		parent::__construct();
		$this->search_items = array('mail'=>$_POST['mail'],'name'=>$_POST['name'],'tel'=>$_POST['tel']);
	}

	public function get_search_items () {
		return $this->search_items;
	}

	public function set_result($result) {
		$this->result = $result;
	}

	public function setNodata () {
		$this->isNodata = true;
	}

	public function check_request() {
// 		//ここは、弱い権限でログインしているスタッフがrequestを偽造して全部取得する場合などを想定
// 		if (!$this->isPluginAdmin() ) {
// 			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E908',basename(__FILE__).':'.__LINE__) );
// 		}
		$msg = "";
		if (ResourceCalendar_Page::serverCheck(array(),$msg) == false) {
			throw new ResourceCalendarException($msg ,__LINE__);
		}

	}


	public function show_page() {
		if ($this->isNodata ) {
			echo '{	"status":"Error","message":"'.ResourceCalendar_Component::getMsg('E906').'",
					"set_data":'.json_encode($this->result).' }';
		}
		else {
			echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
					"cnt":1,
					"set_data":'.json_encode($this->result).' }';
		}
	}	//show_page
}		//class

