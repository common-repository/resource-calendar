<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');

	
class Confirm_Data extends ResourceCalendar_Data {
	
	
	const TABLE_NAME = 'rcal_reservation';	
	
	function __construct() {
		parent::__construct();
		
	}
	
	

	public function updateTable ($table_data){
		

		$set_string = 	' status = %d  '.
//						' ,user_login = %s '.
						' ,update_time = %s ';
												
		$set_data_temp = array(
						$table_data['status']
//						,$table_data['user_login']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}
	

	public function deleteTable ($table_data){
		$set_string = 	' status = %d  '.
						' ,update_time = %s ';
		$set_string .= 	' ,memo = concat(remark,"'.sprintf(__("\nCanceled by %s. ",RCAL_DOMAIN),__("[Screen of Confirm]",RCAL_DOMAIN)).'") ';
												
		$set_data_temp = array(
						$table_data['status']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}


}