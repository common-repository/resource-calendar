<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-calendar-data.php');


class Booking_Data extends ResourceCalendar_Data {

	const TABLE_NAME = 'rcal_reservation';
	const TABLE_NAME_REPEAT = 'rcal_repeat';

	public function __construct() {
		parent::__construct();
	}

	public function getAllEventData($target_day="",$isOnly_target_day =false,&$unsetData = ""){
		global $wpdb;

		$now = date_i18n('Ymd');
		$save_target_day = $target_day;

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
			// 			$last_day = new DateTime(ResourceCalendar_Component::computeDate($this->getConfigData('RCAL_CONFIG_AFTER_DAY'),substr($now,0,4),substr($now,4,2),substr($now,6,2)));
			// 			$to_date = $last_day->modify("last day of this month")->format("Y-m-d H:i:s");
		}
		if (empty($unsetData) ) {
			$unsetData = array();
		}

		$sql = 	$wpdb->prepare(
				' SELECT '
				.'reservation_cd,resource_cd,user_login,name,mail,tel,'
				.'time_from,'
				.'time_to,'
				.'status,'
				.'remark,memo,notes,res.delete_flg,res.insert_time,res.update_time,activate_key '
				.',res.repeat_cd,repeat_patern,repeat_every,repeat_end_patern,repeat_cnt,repeat_weeks '
				.',DATE_FORMAT(rep.valid_from,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as valid_from '
				.',DATE_FORMAT(rep.valid_to,"'.__('%%m/%%d/%%Y',RCAL_DOMAIN).'") as valid_to '
				.' FROM '.$wpdb->prefix.'rcal_reservation res'
				.' INNER JOIN '.$wpdb->prefix.'rcal_repeat rep'
				.' ON res.repeat_cd = rep.repeat_cd '
				.'   WHERE '
				.'     time_from >= %s '
				.'     AND time_to <= %s '
				.'     AND res.delete_flg <> '.ResourceCalendar_Table_Status::DELETED
				.'     AND status <> '.ResourceCalendar_Reservation_Status::CANCELED
				.' ORDER BY time_from ',
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




	public function getTargetResourceDataPhoto() {
		$result = $this->getTargetResourceData();
		foreach ($result as $k1 => $d1 ) {
			//[PHOTO]
			$photo_result = $this->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				$tmp[] = $photo_result[$i];
			}
			$result[$k1]['photo_result'] = $tmp;
			//[PHOTO]
		}
		return $result;
	}

	public function getMonthDataFromResevation($open,$close,$reservation_datas,$day_to) {
		//datepickerの表示タイミングがデータ取得の後なので1月分多くとる。
		//→環境設定のAFTERDAYまで全部とる
		//現状、day_fromとday_toが空なのは、初期表示の場合のみ
		$day_from = ResourceCalendar_Component::computeMonth(-1);
		//月の配列をつくる
		$start_month = $day_from;
		$month_array = array();
		$target_month = substr($day_to,0,4).substr($day_to,5,2);
		for(;;) {
			$set_month = substr($start_month,0,4).substr($start_month,5,2);
			$month_array[] = $set_month;
			if ($set_month >= $target_month) break;
			$start_month = ResourceCalendar_Component::computeMonth(1,substr($start_month,0,4),substr($start_month,5,2),1);
		}
		$edit_result = array();
		foreach ($month_array as $d1) {
			$edit_result[$d1] = array();
		}

		if ( +$close <=  2400)  {
		}
		//終了時間が24時を超えている場合の、翌日予約は前日予約で計上する
		else {
			$open = +substr("0".$open,-4,2);
		}

		$max_cnt = count($reservation_datas);
		for($i = 0 ; $i < $max_cnt ; $i++) {
			$target = new DateTime($reservation_datas[$i]['time_from']);

			if  ( +$close <=  2400)  {
			}
			//終了時間が24時を超えている場合の、翌日予約は前日予約で計上する
			else {
				$hh = +$target->format("H");
				if ($hh < $open ) {
					$target->modify("-1 days");
				}
			}
			$ym = $target->format("Ym");
			$ymd = $target->format("Ymd");
			if (empty($edit_result[$ym][$ymd][$reservation_datas[$i]['status']]) ) {
				$edit_result[$ym][$ymd][$reservation_datas[$i]['status']] = 1;
			}
			else {
				$edit_result[$ym][$ymd][$reservation_datas[$i]['status']]++;
			}
		}
		return $edit_result;

	}

	public function getMonthData($day_from="",$day_to="",$open="",$close="") {
		global $wpdb;

		//datepickerの表示タイミングがデータ取得の後なので1月分多くとる。
		//現状、day_fromとday_toが空なのは、初期表示の場合のみ
		if (empty($day_from) && empty($day_to) ) {
			$day_from = ResourceCalendar_Component::computeMonth(-1);
			$day_to = ResourceCalendar_Component::computeMonth(3);
		}
		//月の配列をつくる
		$start_month = $day_from;
		$month_array = array();
		$target_month = substr($day_to,0,4).substr($day_to,5,2);
		for(;;) {
			$set_month = substr($start_month,0,4).substr($start_month,5,2);
			$month_array[] = $set_month;
			if ($set_month >= $target_month) break;
			$start_month = ResourceCalendar_Component::computeMonth(1,substr($start_month,0,4),substr($start_month,5,2),1);
		}
		$edit_result = array();
		foreach ($month_array as $d1) {
			$edit_result[$d1] = array();
		}

		//if (empty($open) || empty($close) || ( +$close <=  2400) ) {
		if ('' == strval($open) || '' == strval($close) || ( +$close <=  2400) ) {

			$sql = 	$wpdb->prepare(
					' SELECT '.
					' rs.target_month,rs.YYYYMMDD,rs.status,COUNT(*) as cnt '.
					' FROM (  '.
					'        SELECT  DATE_FORMAT(time_from,"%%Y%%m") as target_month,status, '.
					'                DATE_FORMAT(time_from,"%%Y%%m%%d") as YYYYMMDD '.
					'        FROM '.$wpdb->prefix.'rcal_reservation  '.
					'        WHERE time_from >= %s '.
					'        AND time_to < %s '.
					'        AND delete_flg <> '.ResourceCalendar_Table_Status::DELETED.
					'      ) rs '.
					' GROUP BY  rs.target_month,YYYYMMDD,rs.status '.
					' ORDER BY rs.target_month,YYYYMMDD ',
					$day_from,$day_to
			);
		}
		else {
			$open = +substr("0".$open,-4,2);
			$sql = $wpdb->prepare(
					'select rs.target_month,rs.YYYYMMDD,rs.status,COUNT(*) as cnt  '
					.'  from ( '
					.'    select date_format(edit,"%%Y%%m") as target_month,date_format(edit,"%%Y%%m%%d") as YYYYMMDD ,status '
					.'       from ('
					.'         select time_from,case when hh < %d then time_from - interval 1 day else time_from end as edit,status '
					.'            from ('
					.'              select time_from,time_to,date_format(time_from,"%%k") as hh,status'
					.'                 from '.$wpdb->prefix.'rcal_reservation '
					.'                 WHERE time_from >= %s '
					.'                   AND time_to < %s '
					.'                   AND delete_flg <> '.ResourceCalendar_Table_Status::DELETED
					.'              ) in1'
					.'         ) in2'
					.'    )rs'
					.'  GROUP BY  rs.target_month,YYYYMMDD,rs.status'
					.'  ORDER BY rs.target_month,YYYYMMDD',$open,$day_from,$day_to);
		}

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result)>0){

			foreach($result as $k1 => $d1){
				$edit_result[$d1['target_month']][$d1['YYYYMMDD']][$d1['status']] = +$d1['cnt'];
			}
		}
		return $edit_result;
	}


	public function insertTable ($table_data){
		$reservation_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d');
		if ($reservation_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $reservation_cd;
	}

	public function insertTableRepeat ($table_data){
		$repeat_cd = $this->insertSql(self::TABLE_NAME_REPEAT,$table_data,'%d,%d,%s,%d,%s,%d,%s');
		if ($repeat_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $repeat_cd;
	}


	public function updateTable ($table_data){

		$set_string = 	' resource_cd = %d , '.
						' name = %s , '.
						' mail = %s , '.
						' tel = %s , '.
						' activate_key = %s , '.
						' time_from = %s , '.
						' time_to = %s , '.
						' remark = %s , '.
						' status = %d , '.
						' memo = %s , ' .
						' repeat_cd = %d , ' .
						' user_login = %s , ' .
						' update_time = %s ';

		$set_data_temp = array(
						$table_data['resource_cd'],
						$table_data['name'],
						$table_data['mail'],
						$table_data['tel'],
						$table_data['activate_key'],
						$table_data['time_from'],
						$table_data['time_to'],
						$table_data['remark'],
						$table_data['status'],
						$table_data['memo'],
						$table_data['repeat_cd'],
						$table_data['user_login'],
				date_i18n('Y-m-d H:i:s')	);
		$set_data_temp[] = $table_data['reservation_cd'];
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function updateTableByRepeatcd ($table_data){

		$set_string =
				' name = %s , '.
				' mail = %s , '.
				' tel = %s , '.
				' activate_key = %s , '.
				' remark = %s , '.
				' memo = %s , ' .
				' update_time = %s ';

		$set_data_temp = array(
				$table_data['name'],
				$table_data['mail'],
				$table_data['tel'],
				$table_data['activate_key'],
				$table_data['remark'],
				$table_data['memo'],
				date_i18n('Y-m-d H:i:s')	);
		$set_data_temp[] = $table_data['repeat_cd'];
		$where_string = ' repeat_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function updateTableRepeat ($table_data){

		$set_string = 	' repeat_patern = %d , '.
				' repeat_every = %d , '.
				' valid_from = %s , '.
				' repeat_end_patern = %d , '.
				' valid_to = %s , '.
				' repeat_cnt = %d , '.
				' repeat_weeks = %s , '.
				' update_time = %s ';

		$set_data_temp = array(
				$table_data['repeat_patern'],
				$table_data['repeat_every'],
				$table_data['valid_from'],
				$table_data['repeat_end_patern'],
				$table_data['valid_to'],
				$table_data['repeat_cnt'],
				$table_data['repeat_weeks'],
				date_i18n('Y-m-d H:i:s')	);

			$set_data_temp[] = $table_data['repeat_cd'];
		$where_string = ' repeat_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME_REPEAT,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function cancelTable ($table_data){
		$set_string = 	' status = %d  '.
						' ,update_time = %s ';
		$set_string .= 	' ,notes = concat(notes,"'.sprintf(__("\nCanceled by %s.(1) ",RCAL_DOMAIN),__("[Screen of Booking]",RCAL_DOMAIN)).'") ';

		$set_data_temp = array(
						$table_data['status']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}


	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(ResourceCalendar_Table_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';

		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function cancelReservationOfRepeat ($tableData,$exceptThisReservation = false){
		$setString = 	' status = %d  '.
						' ,update_time = %s ';
		$setString .= 	' ,notes = concat(notes,"'.sprintf(__("\nCanceled by %s.(2) ",RCAL_DOMAIN),__("[Screen of Booking]",RCAL_DOMAIN)).'") ';

		$setDataTemp = array(
						$tableData['status']
						,date_i18n('Y-m-d H:i:s')
						,$tableData['repeat_cd']);
		$whereString = ' repeat_cd = %d ';
		if ($exceptThisReservation) {
			$setDataTemp[] = $tableData['reservation_cd'];
			$whereString .= " AND reservation_cd <> %d ";
		}
		if ( $this->updateSql(self::TABLE_NAME,$setString,$whereString,$setDataTemp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}

	public function deleteTableRepeat ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(ResourceCalendar_Table_Status::DELETED,
				date_i18n('Y-m-d H:i:s'),
				$table_data['repeat_cd']);
		$where_string = ' repeat_cd = %d ';

		if ( $this->updateSql(self::TABLE_NAME_REPEAT,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function deleteReservationByRepeatCd ($repeatCd){
		$where_string = ' repeat_cd = %d  ';
		if ( $this->deleteSql(self::TABLE_NAME,$where_string,array($repeatCd)) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function deleteReservationByReservationCdForRepeat ($reservationCd){
		$where_string = ' reservation_cd = %d  ';
		if ( $this->deleteSql(self::TABLE_NAME,$where_string,array($reservationCd)) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}



	public function doRepeatInformation(&$set_data,&$unset_data) {
		$doneUpdate = false;
		$repeat_cd = intval($_POST['rcal_repeat_cd']);

		if ($_POST['type'] == 'deleted' ) {
			//全部キャンセルする
			if ($_POST['rcal_repeat_only_this'] == ResourceCalendar_Repeat::ALL_UPDATE) {
				$set_data_repeat['repeat_cd'] = $repeat_cd;
				$this->deleteTableRepeat($set_data_repeat);
				$this->cancelReservationOfRepeat($set_data);
				$doneUpdate = true;
			}
			//該当予約だけをキャンセル
			else {
				//この予約が繰り返しで最後の繰り返しの場合
				if ($repeat_cd <> ResourceCalendar_Repeat::NO_REPEAT
				&& $this->getCountSql('rcal_reservation '
									.' WHERE status <> '.ResourceCalendar_Reservation_Status::CANCELED
								 	.' AND repeat_cd = '.$repeat_cd
									.' AND reservation_cd <> '.$set_data['reservation_cd']
										) == 0) {
					$set_data_repeat['repeat_cd'] = $repeat_cd;
					$this->deleteTableRepeat($set_data_repeat);
				}
			}
			return;
		}
		//繰り返しがない場合
		if (intval($_POST['rcal_repeat_patern']) == ResourceCalendar_Repeat::NO_REPEAT ) {
			//繰り返しから繰り返しなしの場合はREPEAT情報を削除
			//これまで繰り返していた情報すべてを繰り返しなしにする場合
			//更新で繰り返しあり→なしにする場合は、該当予約のみ。
			//全部なしにしたい場合は、キャンセルする。
			if (($_POST['type'] == 'updated' )
				&&($_POST['rcal_repeat_cd'] != ResourceCalendar_Repeat::NO_REPEAT )){
				//この予約が最後の繰り返しの場合
				if ($this->getCountSql('rcal_reservation '
									.' WHERE status <> '.ResourceCalendar_Reservation_Status::CANCELED
								 	.' AND repeat_cd = '.$repeat_cd
									.' AND reservation_cd <> '.$set_data['reservation_cd']
										) == 0) {
					$set_data_repeat['repeat_cd'] = $repeat_cd;
					$this->deleteTableRepeat($set_data_repeat);
				}
			}

		}
		//繰り返しがある場合
		else {
			//updateの場合は、繰り返し項目に関係ない項目はリピートコードをキーに更新する
			if ( ($_POST['type'] == 'inserted' ) ||
				($_POST['type'] == 'updated'
					&& intval($_POST['rcal_repeat_need_update']) == ResourceCalendar_Repeat::NEED_UPDATE )){
				$set_data_repeat = array();
				$set_data_repeat['repeat_patern'] = intval($_POST['rcal_repeat_patern']);
				$set_data_repeat['repeat_every'] = intval($_POST['rcal_repeat_every']);
				$set_data_repeat['valid_from'] = stripslashes($_POST['rcal_repeat_valid_from']);
				$set_data_repeat['repeat_end_patern'] = intval($_POST['rcal_repeat_end_patern']);
				if ($set_data_repeat['repeat_end_patern'] == ResourceCalendar_Repeat::END_DATE) {
					$set_data_repeat['valid_to'] = stripslashes($_POST['rcal_repeat_valid_to']);
				}
				else {
					//DBの日付なのでYYYY-MM-DDでよい
					$set_data_repeat['valid_to'] = '2099-12-31';
				}
				$set_data_repeat['repeat_cnt'] = intval($_POST['rcal_repeat_cnt']);
				$set_data_repeat['repeat_weeks'] = stripslashes($_POST['rcal_repeat_weeks']);
				//新規登録または更新で前は繰り返しではない
				if (($_POST['type'] == 'inserted' ) ||
						(($_POST['type'] == 'updated' )&&($_POST['rcal_repeat_cd'] == ResourceCalendar_Repeat::NO_REPEAT ))){
					$repeat_cd = $this->insertTableRepeat($set_data_repeat);
					//更新の場合は新たにIDをつくるので削除する
					if ($_POST['type'] == 'updated' ) {
						$this->deleteReservationByReservationCdForRepeat($set_data['reservation_cd']);
					}
				}
				else {
					$set_data_repeat['repeat_cd'] = $repeat_cd;
					$this->updateTableRepeat($set_data_repeat);
					//繰り返しにより登録した予約を削除する
					$this->deleteReservationByRepeatCd($repeat_cd);
				}
				$set_data['repeat_cd'] = $repeat_cd;
				$this->insertRepeatInformation($set_data,$set_data_repeat,$unset_data);
				$doneUpdate = true;
			}
			//[TODO]delete/insertしないで一括更新する
			//その場合は繰り返し情報の変更はないので、予約のみ対応
			else {
				$set_data['repeat_cd'] = $repeat_cd;
				$this->updateTableByRepeatcd($set_data);
			}
		}

		return $doneUpdate;

	}

	private function _calcMonth($date,$month,$dd,$add = true) {
		$result = clone $date;
		if ($add) {
			$result->add(new DateInterval("P{$month}M"));
		}
		else {
			$result->sub(new DateInterval("P{$month}M"));
		}
		//大元の日と等しい
		if ($dd == $result->format("d")) {
			return $result;
		}
		else {
			//31などがない月の考慮
			$calcDate = new DateTime($date->format("Y-m-01"));
			//2月は計算元の日付が、３１３０２９の可能性がある
			if ($add){
				$calc = $month + 1;
				$calcDate->add(new DateInterval("P{$calc}M"));
				$calcDate->sub(new DateInterval("P1D"));
			}
			else {
				$calc = $month -1;
				$calcDate->sub(new DateInterval("P{$calc}M1D"));
			}
			return $calcDate;
		}
	}

	public function insertRepeatInformation($set_data,$set_data_repeat,&$unsetData) {
		$date = new DateTime($set_data_repeat["valid_from"]);
		//週の場合は開始日が含まれている週の先頭日付を求める。
		$weekinf = array();
		if ($set_data_repeat["repeat_patern"] == ResourceCalendar_Repeat::WEEKLY) {
			//日曜日はじまりで、１週前から設定する
			// 				$w = $date->format("w")+7;
			$w = $date->format("w");
			$date = $date->modify("-{$w} days");
			$weekinf = explode(",",$set_data_repeat["repeat_weeks"]);
		}
		$every = $set_data_repeat["repeat_every"];
		switch ($set_data_repeat["repeat_patern"]) {
			case ResourceCalendar_Repeat::DAILY:
				$minus_date = $date->modify("-{$every} days");
				break;
			case ResourceCalendar_Repeat::WEEKLY:
				$minus_date = $date->modify("-{$every} weeks");
				break;
			case ResourceCalendar_Repeat::MONTHLY:
				$minus_date = $this->_calcMonth($date, $every,$date->format("j"),false);
				break;
			case ResourceCalendar_Repeat::YEARLY:
				$minus_date = $date->modify("-{$every} year");
				break;
		}
		//以下は循環関数になっている
		$edit_result = array();
		$now = date_i18n('Ymd');
		//繰り返し可能なのは当日以降
		$from = new DateTime(date_i18n('Y-m-d'));
		$to = new DateTime(ResourceCalendar_Component::computeDate($this->getConfigData('RCAL_CONFIG_AFTER_DAY'),substr($now,0,4),substr($now,4,2),substr($now,6,2)));
		//日付は指定日付か予約可能な日のうち早い日
		if ($set_data_repeat["repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
			$toValid = new DateTime($set_data_repeat["valid_to"]);
			if ($toValid < $to ) {
				$to = $toValid;
			}
		}

		$this->caliculateRepeatDate(
				$minus_date
				,$set_data_repeat["repeat_patern"]
				,$every
				,$set_data_repeat["repeat_end_patern"]
				,$set_data_repeat["repeat_cnt"]
				,$from
				,$to
				,$edit_result
				,$weekinf
				,0
				,$date->format("j"));


		$time_from = substr($set_data["time_from"],10);
		$time_to = substr($set_data["time_to"],10);

		//個別登録のチェック用
		$result = $this->getAllEventData();
		$check_array = array();
		foreach ($result as $k1 => $d1) {
			$ymd = str_replace("-","",substr($d1['time_from'],0,10));
			$check_array [$ymd][$d1['resource_cd']][] =array($d1['time_from'],$d1['time_to'],$d1['reservation_cd']);
		}
		//休日の場合は、データを削除する。比較するための配列
		$open = +substr($this->config['RCAL_CONFIG_OPEN_TIME'],0,2);
		$fromForHolidayCheck = str_replace(":","",substr($set_data["time_from"],11));
		if (+substr($fromForHolidayCheck,0,2) < $open) {
			$hh = +substr($fromForHolidayCheck,0,2)+24;
			$fromForHolidayCheck =substr("0". $hh , -2).substr($fromForHolidayCheck,2);
		}
		$toForHolidayCheck = str_replace(":","",substr($set_data["time_to"],11));
		if (+substr($toForHolidayCheck,0,2) < $open) {
			$hh = +substr($toForHolidayCheck,0,2)+24;
			$toForHolidayCheck =substr("0". $hh , -2).substr($toForHolidayCheck,2);
		}
		$holidaysArray = array();
		if (!empty($this->config['RCAL_CONFIG_CLOSED'])) {
			//曜日;from;to,曜日;from;to,曜日;from;to
			$holidays_data = explode(',',$this->config["RCAL_CONFIG_CLOSED"]);
			foreach($holidays_data  as $k1 => $d1 ) {
				$splitdata = explode(';',$d1);
				//echo ($fromForHolidayCheck." ". $toForHolidayCheck ." " .$splitdata[0] ." ".$splitdata[1] ." ".$splitdata[2] ." ");
				if ((+$toForHolidayCheck <= +$splitdata[1] )
						|| (+$splitdata[2] <= +$fromForHolidayCheck  )) {
						}
						else {
							$holidaysArray[$splitdata[0]] = true;
						}
			}
		}

		//特別なやすみ
		$sp_dates = $this->config['RCAL_SP_DATES'];
		$resourceValidTo = new DateTime($this->getValidToFromResourceData($set_data['resource_cd']));

		//edit_resultに繰り返しの情報が設定されていて、
		//以下で無効データ→休みや既存予約がある場合に削除していく
		foreach ($edit_result as $k1 => $d1 ) {

			$ymd = $d1->format("Y-m-d");
			$setTimeFrom = $ymd .$time_from;
			$setTimeTo = $ymd .$time_to;
			//休日が該当している場合は、対象外

			$holiday_chk = new DateTime($setTimeFrom);
			$hh = +$holiday_chk->format('H');
			if ($hh < $open ) {
				$holiday_chk->modify('-1 days');
			}
			$week_chk = $holiday_chk->format("w");
			if (( $resourceValidTo < $d1 )
				|| (array_key_exists($week_chk,$holidaysArray))) {
				//特別な営業日がある場合は定休日でも予約OK
				$doUnset = true;
				if (! empty($sp_dates) ) {
					$yyyy = substr($ymd,0,4);
					if (isset($sp_dates[$yyyy])&& count($sp_dates[$yyyy]) > 0) {
						$yyyymmdd = str_replace("-","",$ymd);
						if (isset($sp_dates[$yyyy][$yyyymmdd]) && $sp_dates[$yyyy][$yyyymmdd] == ResourceCalendar_Status::OPEN) {
							$doUnset = false;
						}
					}
				}
				if ($doUnset) {
					$unsetData[] = $setTimeFrom;
					unset($edit_result[$k1]);
				}
			}
			//特別な休みの場合
			elseif (! empty($sp_dates) ) {
				$yyyy = substr($ymd,0,4);
				if (isset($sp_dates[$yyyy])&& count($sp_dates[$yyyy]) > 0) {
					$yyyymmdd = str_replace("-","",$ymd);
					if (isset($sp_dates[$yyyy][$yyyymmdd]) && $sp_dates[$yyyy][$yyyymmdd] == ResourceCalendar_Status::CLOSE) {
						$unsetData[] = $setTimeFrom;
						unset($edit_result[$k1]);
					}
				}
			}
			else {
				$checkYmd = str_replace("-", "",$ymd);
				if (array_key_exists($checkYmd,$check_array)) {
					foreach($check_array[$checkYmd] as $k2 => $d2) {
						if ($k2 == $set_data['resource_cd']) {
							foreach ($d2 as  $k3 => $d3) {
								//時間が重複している部分がある場合は、繰り返しのほうを無効にする
								$f1 = new DateTime($d3[0]);
								$t1 = new DateTime($d3[1]);
								$f2 = new DateTime($setTimeFrom);
								$t2 = new DateTime($setTimeTo);
								if (($t2 <= $f1 ) || ($t1 <= $f2)) {
								}
								else {
									//選択中の予約は削除する→既存の１件ずつのルートにのせる。
									//重複したデータとして表示しないための判定
									if ($d3[2] != $set_data['reservation_cd'] ) {
										$unsetData[] = $setTimeFrom;
									}
									unset($edit_result[$k1]);
									continue 2;
								}
							}

						}
					}
				}
			}
		}

		$setDatasArray = array();
		//繰り返しの開始を過去にすると1件も更新がない場合がある
		if (count($edit_result)> 0){
			foreach ($edit_result as $k2 => $d2 ) {
				$ymd = $d2->format("Y-m-d");
				$ymd_tomorrow = clone $d2;
				$ymd_tomorrow->add(new DateInterval("P1D"));
				$tmp = $set_data;
				unset($tmp['reservation_cd']);
				//24時超えを考慮
				if (+substr($fromForHolidayCheck,0,2) > 23 ) {
					$tmp["time_from"] = $ymd_tomorrow->format("Y-m-d") . $time_from;
				}
				else {
					$tmp["time_from"] = $ymd . $time_from;
				}
				if (+substr($toForHolidayCheck,0,2) > 23 ) {
					$tmp["time_to"] = $ymd_tomorrow->format("Y-m-d") . $time_to;
				}
				else {
					$tmp["time_to"] = $ymd . $time_to;
				}
				$setDatasArray[] = $tmp;
			}
			$this->bulkInsertSql($setDatasArray);
		}
	}

	public function bulkInsertSql($setDatasArray) {
		global $wpdb;
		$currentTime = date_i18n('Y-m-d H:i:s');
		$sql = ' INSERT INTO '.$wpdb->prefix.self::TABLE_NAME.' ( ';
		foreach($setDatasArray[0] as $k1 => $d1) {
			$sql .= $k1.',';
		}
		$sql .= 'insert_time,update_time) VALUES ';


		$format = '(%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,%s,%s)';
		$values = array();
		foreach ( $setDatasArray as $k1 => $d1 ) {
			$setDatasArray[$k1]['insert_time'] = $currentTime;
			$setDatasArray[$k1]['update_time'] = $currentTime;
			$values[] = $wpdb->prepare($format,$setDatasArray[$k1]);
		}
		$sql .= implode(",",$values);
		$result = $wpdb->query($sql);

		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'RCAL_DEMO' ) && RCAL_DEMO   ) || ($this->config['RCAL_CONFIG_LOG'] == ResourceCalendar_Config::LOG_NEED )) {
			$this->_writeLog($sql);
		}
	}

	public function caliculateRepeatDate(
			$baseDate,
			$patern,
			$every,
			$end_patern,
			$cnt ,
			$from,
			$to,
			&$editResult,
			$weekinf,
			$currentCnt,
			$dd
			) {

		$date = $baseDate;
		switch ($patern) {
			case ResourceCalendar_Repeat::DAILY:
				$result = $date->modify("+{$every} days");
				break;
			case ResourceCalendar_Repeat::WEEKLY:
				$result = $date->modify("+{$every} weeks");
				break;
			case ResourceCalendar_Repeat::MONTHLY:
				//$result = $date->modify("+{$every} month");
				$result = $this->_calcMonth($date, $every,$dd,true);
				break;
			case ResourceCalendar_Repeat::YEARLY:
				$result = $date->modify("+{$every} year");
				break;
		}

		//週は週初めから設定している曜日分加算する
		if ($patern == ResourceCalendar_Repeat::WEEKLY) {
			foreach($weekinf as $d1) {
				$set_result = new DateTime($result->format("y-m-d"));
				$set_result->modify("+{$d1} days");
				if ($to < $set_result ) {
					return;
				}
				if ($from <= $set_result){
					$editResult[] = $set_result;
				}
			}
		}
		else {
			if ($to < $result ) {
				return;
			}
			if ($from <= $result ) {
				$editResult[] =new DateTime($result->format("y-m-d"));
			}
		}
		if ($end_patern == ResourceCalendar_Repeat::END_CNT) {
			$currentCnt++;
			if ($cnt < $currentCnt ) {
				return;
			}
		}
		$this->caliculateRepeatDate($result,$patern, $every, $end_patern, $cnt ,$from, $to, $editResult,$weekinf,$currentCnt,$dd);

	}

	public function getResevationCdByRepeatCd($repeat_cd) {
		global $wpdb;
		$sql = 'SELECT  '.
				' reservation_cd '.
				' FROM '.$wpdb->prefix.'rcal_reservation '.
				' WHERE repeat_cd = '.$repeat_cd.
				' ORDER BY reservation_cd '.
				' LIMIT 1 ';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result[0]['reservation_cd'];
	}

//kokomade


	public function getInitDatas() {
	}
}