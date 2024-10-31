<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');


class Category_Data extends ResourceCalendar_Data {

	const TABLE_NAME = 'rcal_category';

	function __construct() {
		parent::__construct();
	}


	public function insertTable ($table_data){
		$category_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%d,%s');
		if ($category_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $category_cd;
	}


	public function updateTable ($table_data){
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['category_cd'] = intval($_POST['rcal_category_cd']);
				$set_data['display_sequence'] = intval($_POST['rcal_display_sequence']);
			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('rcal_category')+1;
			}

			$set_data['category_name'] = stripslashes($_POST['rcal_category_name']);
			$set_data['category_patern'] =  intval($_POST['rcal_category_patern']);
			$set_data['category_values'] =  stripslashes($_POST['rcal_category_value']);

		$set_string = 	' category_name = %s , '.
						' category_patern = %d , '.
						' category_values = %s , '.
						' display_sequence = %d , '.
						' update_time = %s ';

		$set_data_temp = array($table_data['category_name'],
						$table_data['category_patern'],
						$table_data['category_values'],
						$table_data['display_sequence'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['category_cd']);
		$where_string = ' category_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(ResourceCalendar_Table_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['category_cd']);
		$where_string = ' category_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}


		return true;
	}


	public function getInitDatas() {
	}

	public function getCategoryPatern(){

		$result = array();
		$result[ResourceCalendar_Category::RADIO] = __('Radio Button',RCAL_DOMAIN);
		$result[ResourceCalendar_Category::CHECK_BOX] = __('Check Box',RCAL_DOMAIN);
		$result[ResourceCalendar_Category::TEXT] = __('Text',RCAL_DOMAIN);
		$result[ResourceCalendar_Category::SELECT] = __('Select Box',RCAL_DOMAIN);
		$result[ResourceCalendar_Category::MAIL] = __('Mail',RCAL_DOMAIN);
		return $result;
	}


}