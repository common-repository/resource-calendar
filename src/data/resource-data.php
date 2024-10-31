<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');

	
class Resource_Data extends ResourceCalendar_Data {
	
	const TABLE_NAME = 'rcal_resource';	
	
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$key_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%s,%s,%s,%s,%d,%d,%s');
		if ($key_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $key_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' name = %s , '.
						' remark =  %s , '.
						' photo =  %s , '.
						' valid_from =  %s , '.
						' valid_to =  %s , '.
						' max_setting = %d , '.
						' setting_patern_cd = %d , '.
						' setting_data = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						$table_data['name'],
						$table_data['remark'],
						$table_data['photo'],
						$table_data['valid_from'],
						$table_data['valid_to'],
						$table_data['max_setting'],
						$table_data['setting_patern_cd'],
						$table_data['setting_data'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['resource_cd']);
		$where_string = ' resource_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	public function updateColumn(&$table_data){
		$set_string = "";
		$set_data_temp = array();
		
		$set_string .= 	$table_data['column_name'].' , '.
								' update_time = %s ';
														
		array_push($set_data_temp,$table_data['value'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['resource_cd']);
		$where_string = ' resource_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
	}

	public function deleteTable ($table_data){
		$where_string = ' resource_cd = %d ';
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(ResourceCalendar_Table_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['resource_cd']);
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	


	public function updateResourcePhotoData($resource_cd,$new_photo_ids) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT photo '.
				' FROM '.$wpdb->prefix.'rcal_resource'.
				' WHERE resource_cd = %d ',$resource_cd);
		
		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$this->fixedPhoto("updated",$new_photo_ids,$result[0]['photo']);
		
	}


	public function deleteResourcePhotoData($resource_cd) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT photo '.
				' FROM '.$wpdb->prefix.'rcal_resource  '.
				' WHERE resource_cd = %d ',$resource_cd);
		
		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		};
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$this->deletePhotoDatas($result[0]['photo']);
		
	}

	static function getSettingPaternDatas(){
		$result = array();
		$result[ResourceCalendar_Config::SETTING_PATERN_TIME] = __('Input time unit',RCAL_DOMAIN);
		$result[ResourceCalendar_Config::SETTING_PATERN_ORIGINAL] = __('Input pre-determined time frames',RCAL_DOMAIN);
		return $result;
	}
	
}