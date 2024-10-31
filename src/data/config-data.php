<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');


class Config_Data extends ResourceCalendar_Data {

	function __construct() {
		parent::__construct();
	}

	public function getAllSpDateData($target_year = null){

		$sp_dates =  unserialize(get_option( 'RCAL_SP_DATES'));
		$result = array();
		for ($yyyy = $target_year ; $yyyy < $target_year + 2 ; $yyyy++ ) {
			if ($sp_dates && !empty($sp_dates[$yyyy])) {

				foreach ($sp_dates[$yyyy] as $k1 => $d1) {
					$title = __('Special holiday',RCAL_DOMAIN);
					if ($d1==ResourceCalendar_Status::OPEN) $title = __('Business day',RCAL_DOMAIN);
					$target_date = __('%%m/%%d/%%Y',RCAL_DOMAIN);
					$target_date = str_replace('%%Y',substr($k1,0,4),$target_date);
					$target_date = str_replace('%%m',substr($k1,4,2),$target_date);
					$target_date = str_replace('%%d',substr($k1,6,2),$target_date);
					$result[] = array("target_date"=>$target_date,"status_title"=>$title,"status"=>$d1);
				}
			}
		}
		return $result;
	}


	public function update ($table_data){
		$this->setConfigData($table_data);
		return true;

	}

	public function updateSpDate ($table_data){
		$set_data = serialize($table_data['RCAL_SP_DATES']);
		update_option('RCAL_SP_DATES',$set_data);
		$this->writeLog($set_data);
		return true;

	}
}