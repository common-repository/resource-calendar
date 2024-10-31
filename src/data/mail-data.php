<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');

	
class Mail_Data extends ResourceCalendar_Data {
	
	
	function __construct() {
		parent::__construct();
	}


	public function update ($table_data){
		$this->setConfigData($table_data);
		return true;
		
	}
	
}