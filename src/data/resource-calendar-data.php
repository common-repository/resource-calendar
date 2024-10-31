<?php

abstract class ResourceCalendar_Data {

	const RCAL_NAME_DELIMITER = ' ';
	const RCAL_DUMMY_DOMAIN = '@dummy.com';
	const RCAL_ADMINISTRATOR = 4;
	const RCAL_MAINTENANCE = 7;


	private $version = '1.0';
	protected $config = null;

	private $isAdmin = null;


	public function __construct() {
		$result =  unserialize(get_option( 'RCAL_CONFIG'));

		if (empty($result['RCAL_CONFIG_OPEN_TIME']) ) $result['RCAL_CONFIG_OPEN_TIME'] =  ResourceCalendar_Config::OPEN_TIME;
		if (empty($result['RCAL_CONFIG_CLOSE_TIME']) ) $result['RCAL_CONFIG_CLOSE_TIME'] =  ResourceCalendar_Config::CLOSE_TIME;
		if (empty($result['RCAL_CONFIG_TIME_STEP']) ) $result['RCAL_CONFIG_TIME_STEP'] =  ResourceCalendar_Config::TIME_STEP;
		if (empty($result['RCAL_CONFIG_BEFORE_DAY']) ) $result['RCAL_CONFIG_BEFORE_DAY'] =  ResourceCalendar_Config::DEFALUT_BEFORE_DAY;
		if (empty($result['RCAL_CONFIG_AFTER_DAY']) ) $result['RCAL_CONFIG_AFTER_DAY'] =  ResourceCalendar_Config::DEFALUT_AFTER_DAY;
		if (empty($result['RCAL_CONFIG_SHOW_DETAIL_MSG']) ) $result['RCAL_CONFIG_SHOW_DETAIL_MSG'] =  ResourceCalendar_Config::DETAIL_MSG_NG;
		if (empty($result['RCAL_CONFIG_RESERVE_DEADLINE']) ) $result['RCAL_CONFIG_RESERVE_DEADLINE'] =  ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE;
		if (!isset($result['RCAL_CONFIG_CLOSED']) || '' == strval($result['RCAL_CONFIG_CLOSED'])  ) $result['RCAL_CONFIG_CLOSED'] =  '';
		if (empty($result['RCAL_CONFIG_SHOW_DATEPICKER_CNT']) ) $result['RCAL_CONFIG_SHOW_DATEPICKER_CNT'] =  ResourceCalendar_Config::RCAL_CONFIG_SHOW_DATEPICKER_CNT;
		if (empty($result['RCAL_CONFIG_CONFIRM_STYLE']) ) $result['RCAL_CONFIG_CONFIRM_STYLE'] =  ResourceCalendar_Config::CONFIRM_NO;
		if (empty($result['RCAL_CONFIG_ENABLE_RESERVATION']) ) $result['RCAL_CONFIG_ENABLE_RESERVATION'] =  ResourceCalendar_Config::USER_ANYONE;

		if (empty($result['RCAL_CONFIG_SEND_MAIL_FROM']) ) $result['RCAL_CONFIG_SEND_MAIL_FROM'] =  "";
		if (empty($result['RCAL_CONFIG_SEND_MAIL_RETURN_PATH']) ) $result['RCAL_CONFIG_SEND_MAIL_RETURN_PATH'] =  "";
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT'] = __("Dear {X-TO_NAME} \n\nPlease confirm this reservation.\n\nResource:{X-TO_RESOURCE} \nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}\n\nClick the following URL\n{X-URL}\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT_ADMIN']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT_ADMIN'] = __("Confirm this reservation.\n\nCustomer Name:{X-TO_NAME}\nStatus:{X-TO_STATUS}\nResource:{X-TO_RESOURCE}\nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}\n",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'] = __("Dear {X-TO_NAME} \n\nThank you for the reservation.\nYour reservation was confirmed.\n\nResource:{X-TO_RESOURCE} \nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT_ACCEPTED']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT_ACCEPTED'] = __("Dear {X-TO_NAME} \n\nThank you for the reservation.\n\nResource:{X-TO_RESOURCE} \nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}\n\nPlease note that your reservation is not complete until confirmed by the admin,\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT_INFORMATION']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT_INFORMATION'] = __("The reservation was updated.\n\nStatus:{X-TO_STATUS}  \nCustomer Name:{X-TO_NAME} \nResource:{X-TO_RESOURCE} \nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_TEXT_CANCELED']) ) $result['RCAL_CONFIG_SEND_MAIL_TEXT_CANCELED'] = __("Dear {X-TO_NAME} \n\nYour reservation was canceled.\n\nResource:{X-TO_RESOURCE} \nTime:{X-TO_TIME}\n{X-TO_REPEAT_INF}\nRemark:{X-TO_REMARK}\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT'] = __("Confirm Reservation",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT_ADMIN']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT_ADMIN'] = __("Confirm Reservation(Admin)",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'] = __("Your reservation was comfirmed",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED'] = __("Thank you for the reservation",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT_INFORMATION']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT_INFORMATION'] = __("Information of the reservation",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_SUBJECT_CANCELED']) ) $result['RCAL_CONFIG_SEND_MAIL_SUBJECT_CANCELED'] = __("Your reservation was canceled",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_SEND_MAIL_BCC']) ) $result['RCAL_CONFIG_SEND_MAIL_BCC'] = "";

		if (empty($result['RCAL_CONFIG_NAME']) ) $result['RCAL_CONFIG_NAME'] = "";
		if (empty($result['RCAL_CONFIG_RESOURCE_NAME']) ) $result['RCAL_CONFIG_RESOURCE_NAME'] = __("Resource",RCAL_DOMAIN);
		if (empty($result['RCAL_CONFIG_ADDRESS']) ) $result['RCAL_CONFIG_ADDRESS'] = "";
		if (empty($result['RCAL_CONFIG_TEL']) ) $result['RCAL_CONFIG_TEL'] = "";
		if (empty($result['RCAL_CONFIG_MAIL']) ) $result['RCAL_CONFIG_MAIL'] = "";


		$result['RCAL_CONFIG_LOG']  = ResourceCalendar_Config::LOG_NEED;
		if (empty($result['RCAL_CONFIG_CAL_SIZE']) ) $result['RCAL_CONFIG_CAL_SIZE'] = 80;

		$sp_dates =  unserialize(get_option( 'RCAL_SP_DATES'));

		$result['RCAL_SP_DATES'] = $sp_dates;

		if (empty($result['RCAL_CONFIG_USE_SESSION_ID']) ) $result['RCAL_CONFIG_USE_SESSION_ID'] = ResourceCalendar_Config::USE_SESSION;
		if (empty($result['RCAL_CONFIG_USE_SUBMENU']) ) $result['RCAL_CONFIG_USE_SUBMENU'] = ResourceCalendar_Config::USE_NO_SUBMENU;
		if (empty($result['RCAL_CONFIG_REQUIRED']) ) $result['RCAL_CONFIG_REQUIRED'] = 'rcal_name,rcal_tel,rcal_mail';

		$this->config = $result;
	}


	public function getUserLogin( ) {
		if (is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return $current_user->user_login;
		}
		else {
			return '';
		}
	}


	public function getTargetResourceData($resource_cd = ''){
		global $wpdb;
		$cnt = 0;

		$where = '';
		if ($resource_cd <> '') {
			$where = $wpdb->prepare (' AND resource_cd = %d ',$resource_cd);
		}
		$sql = 'SELECT  '.
			'DATE_FORMAT(valid_from,"'.__("%m/%d/%Y",RCAL_DOMAIN).'") as valid_from '.
			',DATE_FORMAT(valid_to,"'.__("%m/%d/%Y",RCAL_DOMAIN).'") as valid_to '.
			',DATE_FORMAT(valid_from,"%Y%m%d") as chk_from '.
			',DATE_FORMAT(valid_to,"%Y%m%d") as chk_to '.
			',resource_cd,name,remark,photo,display_sequence,max_setting '.
			',setting_patern_cd ,setting_data '.
			' FROM '.$wpdb->prefix.'rcal_resource '.
			' WHERE delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
			$where.
			' ORDER BY display_sequence ';

		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function getValidToFromResourceData($resource_cd ){
		global $wpdb;
		$sql = 'SELECT  '.
			'valid_to '.
			' FROM '.$wpdb->prefix.'rcal_resource '.
			' WHERE delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
			' AND resource_cd = '.$resource_cd;

		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result[0]['valid_to'];
	}

	public function getResourceName($resource_cd ){
		global $wpdb;

		$sql = $wpdb->prepare ('SELECT  name '.
			' FROM '.$wpdb->prefix.'rcal_resource '.
			' WHERE delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
			' AND resource_cd = %d '.
			' ORDER BY display_sequence ',$resource_cd);

		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result) > 0 )
			return $result[0]['name'];
		else return __("No name",RCAL_DOMAIN);
	}

	static function getTargetReservationData($reservation_cd){
		global $wpdb;

		$sql = 	$wpdb->prepare(	'SELECT '.
						'reservation_cd,rs.resource_cd,rs.name ,'.
						' DATE_FORMAT(time_from,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as target_day,'.
						' DATE_FORMAT(time_from, "%%H:%%i")  as time_from,'.
						' DATE_FORMAT(time_to, "%%H:%%i")   as time_to,'.
						' DATE_FORMAT(time_from, "%%Y%%m%%d%%H%%i")  as check_day,'.
						' rs.remark,rs.memo,rs.notes,rs.delete_flg,rs.insert_time,rs.update_time,rs.activate_key,rs.status ,rs.mail '.
						' ,re.name as resource_name ,rs.user_login'.
						' ,rp.repeat_patern,rp.repeat_every '.
						' ,DATE_FORMAT(rp.valid_from,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as repeat_valid_from'.
						' ,DATE_FORMAT(rp.valid_to,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as repeat_valid_to'.
						' ,DATE_FORMAT(time_from,"%%m") as repeat_mm'.
						' ,DATE_FORMAT(time_from,"%%d") as repeat_dd'.
						' ,rp.repeat_cnt,rp.repeat_weeks'.
						' FROM '.$wpdb->prefix.'rcal_reservation rs '.
						' INNER JOIN '.$wpdb->prefix.'rcal_resource re'.
						' ON rs.resource_cd = re.resource_cd '.
						' INNER JOIN '.$wpdb->prefix.'rcal_repeat rp'.
						' ON rs.repeat_cd = rp.repeat_cd '.
				' WHERE reservation_cd = %d ',$reservation_cd);

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;


	}

	public function getAllEventDataFromAdmin($target_day="",$isOnly_target_day =false,$repeat_cd = ""){
		global $wpdb;

		$now = date_i18n('Ymd');

		$to_date = '2099-12-31 12:00:00';
		if (empty($target_day) ) {
			$target_day = ResourceCalendar_Component::computeDate(-1*$this->getConfigData('RCAL_CONFIG_BEFORE_DAY'),substr($now,0,4),substr($now,4,2),substr($now,6,2));
		}
		if ($isOnly_target_day) {
			$to_date = ResourceCalendar_Component::computeDate(2,substr($target_day,0,4),substr($target_day,4,2),substr($target_day,6,2));
			$target_day = ResourceCalendar_Component::computeDate(0,substr($target_day,0,4),substr($target_day,4,2),substr($target_day,6,2));
		}
		else {
			$to_date = ResourceCalendar_Component::computeDate($this->getConfigData('RCAL_CONFIG_AFTER_DAY'),substr($now,0,4),substr($now,4,2),substr($now,6,2));
		}

		$where = "";
		if (!empty($repeat_cd)) {
			$where = " AND res.repeat_cd = ".$repeat_cd;
		}
		$sql = 	$wpdb->prepare(
				' SELECT '
				.'reservation_cd,res.resource_cd,user_login,res.name,mail,tel,'
				.'rsc.name as rcal_resource_name,'
				.'time_from,'
				.'time_to,'
				.'DATE_FORMAT(time_from,"%%H%%i") as hhmm_from,'
				.'DATE_FORMAT(time_to,"%%H%%i") as hhmm_to,'
				.'concat(DATE_FORMAT(res.time_from,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).' %%H:%%i - ") , DATE_FORMAT(res.time_to,"%%H:%%i")) as rcal_show_time, '
				.'DATE_FORMAT(time_from,"%%Y/%%m/%%d") as time_from_ymd , '
				.'status,'
				.'res.remark,res.memo,res.notes,res.delete_flg,res.insert_time,res.update_time,activate_key '
				.',res.repeat_cd,repeat_patern,repeat_every,repeat_end_patern,repeat_cnt,repeat_weeks '
				.',DATE_FORMAT(rep.valid_from,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as valid_from '
				.',DATE_FORMAT(rep.valid_to,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as valid_to '
				.' FROM '.$wpdb->prefix.'rcal_reservation res'
				.' INNER JOIN '.$wpdb->prefix.'rcal_resource rsc'
				.' ON res.resource_cd = rsc.resource_cd '
				.' INNER JOIN '.$wpdb->prefix.'rcal_repeat rep'
				.' ON res.repeat_cd = rep.repeat_cd '
				.'   WHERE '
				.'     time_from >= %s '
				.'     AND time_to <= %s '
				.'     AND res.delete_flg <> '.ResourceCalendar_Table_Status::DELETED
				.'     AND status <> '.ResourceCalendar_Reservation_Status::CANCELED
				.$where
				.' ORDER BY time_from,res.resource_cd ',
				$target_day,$to_date
		);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}





	//テーブルにはひとつのキー項目がautoincrementで定義されている前提
	//$set_dataにはキー項目は設定していない
	//$set_attrにはそれぞれの項目の属性が%s,%d,%fで入っている
	public function insertSql($table_name,$set_data,$set_attr) {
		global $wpdb;
		$sql = ' INSERT INTO '.$wpdb->prefix.$table_name.' ( ';
		//最後の２カラムはinsertとupdate
		$val = ' VALUES ('.$set_attr.',%s,%s)';

		foreach ( $set_data as $k1 => $d1 ) {
			$sql .= $k1.',';
		}
		//最後の２カラムはinsertとupdate
		$sql .= 'insert_time,update_time)';
		$current_time = date_i18n('Y-m-d H:i:s');
		$set_data['insert_time'] = $current_time;
		$set_data['update_time'] = $current_time;

		$sql = $sql.$val;
		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
// 		$save_id = mysql_insert_id(); //非推奨
		$save_id = $this->getLastId($table_name);
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
		//intの前提
		return $save_id;
	}

	public function getLastId($table_name) {
		global $wpdb;
		$sql = 'select last_insert_id() as id from '.$wpdb->prefix.$table_name;
		$result = $wpdb->get_results($sql,ARRAY_A);

		return $result[0]['id'];
	}

	public function writeLog($setdata) {
		$this->_writeLog($setdata);
	}

	protected function _writeLog($setdata) {

		global $wpdb;
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'rcal_log'.
				' (`sql`,remark,insert_time ) '.
				' VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$setdata,$_SERVER['REMOTE_ADDR'].':'.$_SERVER['HTTP_REFERER'].':'.$this->getUserLogin( ).':'.$_SERVER['HTTP_USER_AGENT'] ,$current_time));
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}

	}

	public function updateSql($table_name,$set_string,$where_string,$set_data) {
		global $wpdb;

		$sql = 	' UPDATE '.$wpdb->prefix.$table_name.
				' SET '.$set_string.
				' WHERE '.$where_string ;

		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);

		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
//		if (!$result) {
//			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',$wpdb->last_query));
//		}
		return true;
	}

	//

	public function deleteSql($table_name,$where_string,$set_data) {
		global $wpdb;

		$sql = 	' DELETE FROM '.$wpdb->prefix.$table_name.
				' WHERE '.$where_string ;

		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
//		if (!$result) {
//			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',$wpdb->last_query));
//		}
		return true;
	}


	public function getConfigData ($target = null) {
		if ('' == strval($target) ) return $this->config;
		return @$this->config[$target];
	}


	public function setConfigData ($table_data) {
		foreach ($table_data as $k1 => $d1 ) {
			$this->config[$k1] = $d1;
		}
		update_option('RCAL_CONFIG',serialize($this->config));

		$this->_writeLog(serialize($this->config));

	}

	public function getCountSql ($sql) {
		global $wpdb;

		$execSql ='SELECT count(*) AS cnt FROM '.$wpdb->prefix.$sql;
		if ($wpdb->query($execSql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($execSql,ARRAY_A);
		}

		return $result[0]['cnt'];

	}

	public function _dbAccessAbnormalEnd () {
		global $wpdb;
		throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E902',array($wpdb->last_error,$wpdb->last_query)) );
	}

	//photo from
	//$idsはphoto_idをカンマ区切りで設定する。
	public function getPhotoData($ids) {
		$result = array();
		if ( '' != strval($ids) ) {
			global $wpdb;
			$result = $wpdb->get_results('SELECT photo_id,photo_name,photo_path,photo_resize_path FROM '.$wpdb->prefix.'rcal_photo '.
										' WHERE photo_id in ('.$ids.')  AND delete_flg <> '.ResourceCalendar_Table_Status::DELETED,ARRAY_A);
			if ($result === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
		$photo_result = array();
		if (count($result) > 0) {
			$edit_result = array();
			foreach ($result as $k1 => $d1) {
				$edit_result[$d1['photo_id']] = $d1;
			}
			$seq = explode(",",$ids);
			for($i = 0;$i<count($seq);$i++) {
				if (array_key_exists($seq[$i],$edit_result) )
					$photo_result[] = $edit_result[$seq[$i]];
				//テーブルのデータを直接削除する以外ないはず
				else
					$photo_result[] = array('photo_id' => $seq[$i] ,'photo_name' => 'NO IMAGE', 'photo_path' => '','photo_resize_path' => '');
			}
		}
		return $photo_result;
	}

// 	public function availablePhotoData($ids) {
// 		global $wpdb;
// 		$exec_sql =	$wpdb->prepare(
// 					' UPDATE  '.
// 					$wpdb->prefix.'rcal_photo '.
// 					'  SET delete_flg = '.ResourceCalendar_Reservation_Status::INIT.
// 					'   WHERE photo_id in (%s) ',$ids);
// 		$result = $wpdb->query($exec_sql);
// 		if ($result === false ) {
// 			$this->_dbAccessAbnormalEnd();
// 		}
// 		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
// 			$this->_writeLog($exec_sql);
// 		}
// 	}

	public function getPhotoDataForDelete($photo_id){
		global $wpdb;
		$result = $wpdb->get_results(' SELECT photo_path,photo_resize_path FROM '.$wpdb->prefix.'rcal_photo where delete_flg <> '.ResourceCalendar_Table_Status::DELETED.' AND photo_id = '.$photo_id,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}

		return $result;
	}

// 	public function deletePhotoData($photo_id) {
// 		$res = $this->getPhotoDataForDelete($photo_id);
// 		if (count($res) == 0 ) {
// 			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__,__('NO PHOTO DATA',RCAL_DOMAIN)));
// 		}
// 		$files = array($res[0]['photo_path'],$res[0]['photo_resize_path']);
// 		foreach ($files as $d1) {
// 			if ( ! unlink(RCAL_UPLOAD_DIR.basename($d1)) ) {
// 				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__,__('PHOTO DATA CAN\'T DELETE',RCAL_DOMAIN)));
// 			}
// 		}
// 	}

	public function insertPhotoData ($photo_id,$target_file_name,$target_width=100,$target_height=100){
		global $wpdb;
		//項目の増減がありので、とりあえずINSERTして必要なファイル名のみupdateする
		$sql = ' INSERT INTO '.$wpdb->prefix.'rcal_photo '
				.' (photo_name,photo_path,photo_resize_path,width,height,delete_flg,insert_time,update_time )'
				.' SELECT photo_name,photo_path,photo_resize_path,width,height,delete_flg,insert_time,update_time FROM '.$wpdb->prefix.'rcal_photo '
				.'  WHERE photo_id = %d ';

		$exec_sql = $wpdb->prepare($sql,$photo_id);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		//$new_photo_id = mysql_insert_id();
		$new_photo_id = $this->getLastId("rcal_photo");
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}

		$set_string = 	' photo_path = %s , '.
						' photo_resize_path = %s , '.
						' insert_time = %s , '.
						' update_time = %s ';

		$set_data_temp = array(
						RCAL_UPLOAD_URL.$target_file_name,
						RCAL_UPLOAD_URL.$target_width."_".$target_height."_".$target_file_name,
						date_i18n('Y-m-d H:i:s'),
						date_i18n('Y-m-d H:i:s'),
						$new_photo_id);
		$where_string = ' photo_id = %d ';
		if ( $this->updateSql('rcal_photo',$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return $new_photo_id;
	}


	public function deletePhotoDatas ($photo_ids){
		if ( '' == strval($photo_ids) )  return;
		global $wpdb;
		$set_string = 	' delete_flg = %s , '.
						' update_time = %s ';

		$set_data_temp = array(
						ResourceCalendar_Table_Status::DELETED,
						date_i18n('Y-m-d H:i:s'));
		$where_string = ' photo_id IN ('.$photo_ids.') ';
		if ( $this->updateSql('rcal_photo',$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		$sql = ' SELECT photo_path,photo_resize_path FROM '.$wpdb->prefix.'rcal_photo '
				.'  WHERE photo_id IN ('.$photo_ids.') ';

		$result = $wpdb->get_results($sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		foreach ($result as  $d1) {
			$files = array($d1['photo_path'],$d1['photo_resize_path']);
			foreach ($files as $d2) {
				if ( ! @unlink(RCAL_UPLOAD_DIR.basename($d2)) ) {
					//エラーを返しても消えたのがどうなるわけでもないのでログに書き留めておく
					//throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__('PHOTO DATA CAN\'T DELETE',RCAL_DOMAIN).' -> '.RCAL_UPLOAD_DIR.basename($d2).' '.basename(__FILE__).':'.__LINE__));
					error_log(ResourceCalendar_Component::getMsg('E901',__('PHOTO DATA CAN\'T DELETE',RCAL_DOMAIN).' -> '.RCAL_UPLOAD_DIR.basename($d2).' '.basename(__FILE__).':'.__LINE__).' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');


				}
			}
		}
	}

	public function fixedPhoto($type,$new_photo_ids,$old_photo_ids = "") {
		if ( '' == strval($new_photo_ids) )  return;
		//仮登録と仮削除を確定する→ＮＧ
		//仮登録を確定する
		global $wpdb;
		$sql = ' UPDATE '.$wpdb->prefix.'rcal_photo '
				.' SET delete_flg = %d '
				.'  WHERE photo_id in ( '.$new_photo_ids.' ) AND delete_flg = %d ';

		$exec_sql = $wpdb->prepare($sql,ResourceCalendar_Reservation_Status::INIT,ResourceCalendar_Reservation_Status::TEMPORARY);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
		if ( $type == "updated" &&  '' != strval($old_photo_ids)) {
			//更新前にあって、更新後にないＩＤを消す
			$old_array = explode(',',$old_photo_ids);
			$new_array = explode(',',$new_photo_ids);
			$del_array = array();
			foreach($old_array as $d1) {
				if (!in_array($d1,$new_array) ) $del_array[] = $d1;
			}
			if (count($del_array) > 0 ) {
				$this->deletePhotoDatas(implode(',',$del_array));
			}
		}
	}


	//[photo to]
	public function getMaxDisplaySequence ($table_name) {
		$cnt = 0;
		global $wpdb;
		$sql = 'SELECT max(display_sequence) as max_seq FROM '.$wpdb->prefix.$table_name.' where delete_flg <> '.ResourceCalendar_Table_Status::DELETED;
		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result) {
			$cnt = $result[0]['max_seq'];
		}
		return $cnt;
	}

	public function updateSeq($table_data,$tale_name,$key_name ) {
		foreach ($table_data as $k1 => $d1) {
			$set_string = 	'display_sequence = %d , '.
							' update_time = %s ';

			$set_data_temp = array($d1,
							date_i18n('Y-m-d H:i:s'),
							$k1);
			$where_string = $key_name.' = %d ';
			if ( $this->updateSql("rcal_".$tale_name,$set_string,$where_string,$set_data_temp) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
	}

	public function countReservation($resource_cd ,$in_time,$out_time,$reservation_cd = '' ) {
		global $wpdb;
		$where = '';
		if ( '' != strval($resource_cd) ) $where = $wpdb->prepare('AND resource_cd = %d ',$resource_cd);
		if ( '' == strval($reservation_cd)) {
			$exec_sql =	$wpdb->prepare(
						' SELECT  '.
						' count(*) as cnt '.
						' FROM '.$wpdb->prefix.'rcal_reservation '.
						'   WHERE ((time_from < %s AND %s <= time_to )'.
						'		OR (time_from <= %s AND %s < time_to ) )'.
						$where.
						'     AND delete_flg <> %d '.
						'     AND status <> %d ',
						$out_time,$out_time,$in_time,$in_time,ResourceCalendar_Table_Status::DELETED,ResourceCalendar_Reservation_Status::CANCELED);
		}
		else {
			$exec_sql =	$wpdb->prepare(
						' SELECT  '.
						' count(*) as cnt '.
						' FROM '.$wpdb->prefix.'rcal_reservation '.
						'   WHERE ((time_from < %s AND %s <= time_to )'.
						'		OR (time_from <= %s AND %s < time_to ) )'.
						$where.
						'     AND delete_flg <> %d '.
						'     AND reservation_cd <> %d '.
						'     AND status <> %d ',
						$out_time,$out_time,$in_time,$in_time,ResourceCalendar_Table_Status::DELETED,$reservation_cd,ResourceCalendar_Reservation_Status::CANCELED);
		}
		if ($wpdb->query($exec_sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($exec_sql,ARRAY_A);
		}
		return $result[0]['cnt'];
	}

	public function getUserInfDataByUserlogin($user_login) {
		global $wpdb;

		if (substr(strtoupper(get_locale()),0,2) == "JA" ) {
			$name_order = 'um2.meta_value," " ,um1.meta_value';
		}
		else {
			$name_order = 'um1.meta_value," " ,um2.meta_value';
		}

		$sql = 	' SELECT us.user_login as user_login,concat('.$name_order.') as user_name , us.user_email , um3.meta_value as tel , um4.meta_value as mobile'.
				' FROM '.$wpdb->users.' us  '.
				' INNER JOIN '.$wpdb->usermeta.' um1  '.
				'       ON    us.ID = um1.user_id AND um1.meta_key ="first_name" '.
				' INNER JOIN '.$wpdb->usermeta.' um2  '.
				'       ON    us.ID = um2.user_id AND um2.meta_key ="last_name" '.
				' LEFT JOIN '.$wpdb->usermeta.' um3  '.
				'       ON    us.ID = um3.user_id AND um3.meta_key ="tel" '.
				' LEFT JOIN '.$wpdb->usermeta.' um4  '.
				'       ON    us.ID = um4.user_id AND um4.meta_key ="mobile" '.
				'WHERE us.user_login = %s  ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		//
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}

		if (empty($result[0]['tel']) && !empty($result[0]['mobile']) ) {
			$result[0]['tel'] = $result[0]['mobile'];
		}

		return $result[0];

	}

	public function getCategoryDatas($where = "") {
		global $wpdb;
		$sql = 'SELECT  '.
				' category_cd '.
				' ,category_name '.
				' ,category_patern '.
				' ,category_values '.
				' FROM '.$wpdb->prefix.'rcal_category '.
				' WHERE delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
				$where.
				' ORDER BY display_sequence,category_cd ';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function getAllCategoryData($key=null) {
		global $wpdb;
		$where = "";
		if ( '' != strval($key) ){
			$where = ' AND category_cd = '.$key;
		}
		$sql = 'SELECT  '.
				' category_cd '.
				' ,category_name '.
				' ,category_patern '.
				' ,display_sequence '.
				' ,category_values '.
				' FROM '.$wpdb->prefix.'rcal_category '.
				' WHERE delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
				$where.
				' ORDER BY display_sequence,category_cd ';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function getStatusFromReservation($reservation_cd) {
		global $wpdb;
		$sql = 'SELECT  '.
				' status '.
				' FROM '.$wpdb->prefix.'rcal_reservation '.
				' WHERE reservation_cd = '.$reservation_cd;
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result[0]['status'];
	}

}