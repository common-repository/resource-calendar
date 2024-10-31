<?php

class Booking_Component extends ResourceCalendar_Component  {


	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editTableData ($user_login) {
		if  ($_POST['type'] == 'deleted' ) {
			$set_data['reservation_cd'] = intval($_POST['id']);
			$set_data['status'] = ResourceCalendar_Reservation_Status::CANCELED;
			$set_data['repeat_cd'] = intval($_POST['rcal_repeat_cd']);
			$set_data['repeat_patern'] = intval($_POST['rcal_repeat_patern']);
			if ($this->isPluginAdmin() ) {
				$set_data['user_login'] = stripslashes($_POST['rcal_user_login']);
			}
			else {
				$set_data['user_login'] = $user_login;
			}
		}
		else {
			$set_data['reservation_cd'] = 0;
			if ($_POST['type'] == 'updated' ) {
				$set_data['reservation_cd'] = intval($_POST['id']);
			}
			$set_data['resource_cd'] = intval($_POST['rcal_resource_cd']);
			$set_data['mail'] = stripslashes($_POST['rcal_mail']);
			$set_data['name'] = stripslashes($_POST['rcal_name']);
			$set_data['tel'] = stripslashes($_POST['rcal_tel']);
			$set_data['remark'] = stripslashes($_POST['rcal_remark']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';

			$set_data['time_from'] =stripslashes($_POST['rcal_time_from']);
			$set_data['time_to'] =stripslashes($_POST['rcal_time_to']);

			$set_data['activate_key'] = substr(md5(uniqid(mt_rand(),1)),0,8);
			//管理者が登録する場合は、設定されてユーザのログインID
			if ($this->isPluginAdmin() ) {
				$set_data['user_login'] = stripslashes($_POST['rcal_user_login']);
			}
			else {
				$set_data['user_login'] = $user_login;
			}
			if 	(($this->datas->getConfigData('RCAL_CONFIG_CONFIRM_STYLE') ==  ResourceCalendar_Config::CONFIRM_BY_ADMIN )
				 || ($this->datas->getConfigData('RCAL_CONFIG_CONFIRM_STYLE') ==  ResourceCalendar_Config::CONFIRM_BY_MAIL ) ){
				if ($this->isPluginAdmin() ) {
					$set_data['status'] = ResourceCalendar_Reservation_Status::COMPLETE;
				}
				else {
					//メールの場合は、現状のスタータスを取得してそのステータスを維持する。
					if 	(($this->datas->getConfigData('RCAL_CONFIG_CONFIRM_STYLE') ==  ResourceCalendar_Config::CONFIRM_BY_MAIL )
					  && ($_POST['type'] == 'updated' )) {
						$set_data['status'] = $this->datas->getStatusFromReservation($set_data['reservation_cd']);
					}
					else {
						$set_data['status'] = ResourceCalendar_Reservation_Status::TEMPORARY;
					}
				}
			}
			else {
				$set_data['status'] = ResourceCalendar_Reservation_Status::COMPLETE;
			}

			if ($this->datas->getConfigData('RCAL_CONFIG_USE_SUBMENU') == ResourceCalendar_Config::USE_SUBMENU ) {
				$edit_record = array();
				foreach ($_POST['rcal_memo'] as $k1 => $d1 ){
					$edit_record[$k1] = stripslashes($d1);
				}
				$set_data['memo'] = serialize($edit_record);
			}
			//繰り返しはここで初期化しとく
			$set_data['repeat_cd'] = ResourceCalendar_Repeat::NO_REPEAT;

		}
		return $set_data;
	}

	public function serverCheck($set_data) {

		global $wpdb;
		$reservation_data = '';
		if ( $_POST['type'] != 'inserted'    ) {
			$reservation_data = $this->datas->getTargetReservationData($set_data['reservation_cd']);
			if ( count($reservation_data) == 0 ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E910', basename(__FILE__).':'.__LINE__).':['.$set_data['reservation_cd'].']',2);

			}
			if ( $_POST['p2'] != $reservation_data[0]['activate_key'] ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E909', basename(__FILE__).':'.__LINE__),3);
			}
			//顧客は自分のしか更新できない
			if (!$this->isPluginAdmin() ) {
				if ($set_data['user_login'] != $reservation_data[0]['user_login'] ) {
					throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E908', basename(__FILE__).':'.__LINE__),4);

				}
			}
		}

		//同一時間帯はひとつだけ。
		$reservation_cd  = "";
		if ( $_POST['type'] == 'updated'    ) $reservation_cd = $set_data['reservation_cd'];
		if ( ($_POST['type'] != 'deleted')&&($_POST['type'] != 'cancel') ) {
			//[TODO]削除予定
// 			//管理者はいつでも予約可能 →　ここはEDITの中ではじくから不要
// 			if (!$this->isPluginAdmin()){
// 				//fromは指定分以降より後
// 				$from = strtotime($set_data['time_from']);
// 				$limit_time = new DateTime(date_i18n('Y-m-d H:i'));
// 				$limit_time->modify("+".$this->datas->getConfigData('RCAL_CONFIG_RESERVE_DEADLINE')." min");
// 				if (+$limit_time->format('U') > $from) {
// 					throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901', basename(__FILE__).':'.__LINE__),4);
// 				}
// 			}
			//休業日のチェックと特別な営業日のチェック
			//
			$sp_dates = $this->datas->getConfigData("RCAL_SP_DATES");
			$year = substr($set_data['time_from'],0,4);
			//yyyy-mm-dd
			$ymd = str_replace('-','',substr($set_data['time_from'],0,10));
			$in_time = str_replace(':','',substr($set_data['time_from'],-5));
			$out_time = str_replace(':','',substr($set_data['time_to'],-5));
			$inTimeForHalfHoliday = $in_time;
			$outTimeForHalfHoliday = $out_time;
			$set_holiday = ResourceCalendar_Component::getDayOfWeek($set_data['time_from']);

			//終わりが２４時を超えていて、翌日の予約をする場合には、日付を１日戻す
			if ( 2400 < +$this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME')  ) {
				$inTimeForHalfHoliday = $this->setOver24($in_time);
				$outTimeForHalfHoliday = $this->setOver24($out_time);
				if ( $in_time  < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME') ){
					$setDateForMinus = new DateTime($set_data['time_from']);
					$setDateForMinus->sub(new DateInterval("P1D"));
					$year = $setDateForMinus->format("Y");
					$ymd = $setDateForMinus->format("Ymd");
					$set_holiday = $setDateForMinus->format("w");
				}
			}

			if(isset($sp_dates[$year][$ymd]) && $sp_dates[$year][$ymd] == ResourceCalendar_Status::OPEN ) {
			}
			elseif(isset($sp_dates[$year][$ymd]) && $sp_dates[$year][$ymd] == ResourceCalendar_Status::CLOSE ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E213'),__LINE__);
			}
			else {
				//休みの場合も全休と半休がある
				//曜日;from;to,曜日;from;to,曜日;from;to
				$holidays_tmp = $this->datas->getConfigData("RCAL_CONFIG_CLOSED");
				if (!empty($holidays_tmp)) {
					$holidays_data = explode(',',$holidays_tmp);
					$holidays = array();
					if (count($holidays_data) > 0 ) {
						$holidays_detail = array();

						foreach($holidays_data  as $k1 => $d1 ) {
							$splitdata = explode(';',$d1);
							$holidays[] = $splitdata[0];
							$holidays_detail[] = array($splitdata[1],$splitdata[2]);
						}

						if (in_array($set_holiday,$holidays)  ) {
							$idx = array_search($set_holiday,$holidays);
							if ($idx !== false) {	//定休日の登録があって、
								//休みの時間の中にはいっていてはいけない
								if ($outTimeForHalfHoliday <= $holidays_detail[$idx][0]
										|| $holidays_detail[$idx][1] <= $inTimeForHalfHoliday ) {
								}
								else {
									throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E213',basename(__FILE__).':'.__LINE__),5);
								}
							}
						}
					}
				}
			}
			//予約が営業時間内に収まっているか？
// 			$in_time = str_replace(':','',substr($set_data['time_from'],-5));
// 			$out_time = str_replace(':','',substr($set_data['time_to'],-5));
			//24時超えの場合の対処
// 			if (+$this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') < 2500 ) {
// 				if ( $in_time  < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME')
// 				  || $this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') < $in_time
// 				  || $out_time  < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME')
// 				  || $this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') < $out_time ) {
// 					throw new ResourceCalendarException(self::getMsg('E302'),__LINE__);
// 				}
// 			}
// 			else {
// 				self::checkBetweenOpenAndCloseWhenOver24($in_time);
// 				self::checkBetweenOpenAndCloseWhenOver24($out_time);
// 			}
			if ( $inTimeForHalfHoliday  < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME')
			  || $this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') < $inTimeForHalfHoliday
			  || $outTimeForHalfHoliday  < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME')
			  || $this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') < $outTimeForHalfHoliday ) {
				throw new ResourceCalendarException(self::getMsg('E302'),__LINE__);
			}

			//表示は複数枠を表示できるようにしているが、
			//重複はできない。予約枠を増やす運用前提とする。
			//繰り返しの登録の場合は、開始日は重複しないようにする
			
			$possible_cnt = apply_filters('rcal_change_possiblCnt',0);
			$cnt = $this->datas->countReservation($set_data['resource_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
			if ($cnt > $possible_cnt ) {
				throw new ResourceCalendarException(self::getMsg('E303'),__LINE__);
			}
			// 			//繰り返しのデータとの重複がないことをチェックする
// 			if (intval($_POST['rcal_repeat_patern']) == ResourceCalendar_Repeat::NO_REPEAT ) {
// 				//繰り返し情報を取得する
// 				$events = $this->datas->getAllEventData($ymd,true);
// 				$in_d = new DateTime($set_data['time_from']);
// 				$out_d =  new DateTime($set_data['time_to']);
// 				foreach ($events as $k1 => $d1 ) {
// 					if ($set_data['resource_cd'] == $d1['resource_cd']) {
// 						if (($_POST['type'] == 'inserted'  )
// 							||($_POST['type'] == 'updated' &&
// 								$set_data['reservation_cd'] != $d1['reservation_cd'])) {
// 									$from_d = new DateTime($d1['time_from']);
// 									$to_d = new DateTime($d1['time_to']);
// 							if (($out_d <= $from_d ) || ($to_d <= $in_d)) {

// 							}
// 							else {
// 								throw new ResourceCalendarException(self::getMsg('E301',$from_d->format("h:i")."-".$to_d->format("h:i")),__LINE__);
// 							}
// 						}
// 					}

// 				}
// 			}
// 			else {
// 				//繰り返しの登録の場合は、開始日は重複しないようにする
// 				$possible_cnt = 0;
// 				$cnt = $this->datas->countReservation($set_data['resource_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
// 				if ($cnt > $possible_cnt ) {
// 					throw new ResourceCalendarException(self::getMsg('E303'),__LINE__);
// 				}

// 			}
		}
		return true;
	}

// 	private function checkBetweenOpenAndCloseWhenOver24($target) {
// 		//24時までと以降をわける
// 		if (($this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME') <=  $target && $target <= 2400) ||
// 			( 0 <= $target && $target <= $this->datas->getConfigData('RCAL_CONFIG_CLOSE_TIME') ) ){
// 		}
// 		else {
// 			throw new ResourceCalendarException(self::getMsg('E302'),__LINE__);
// 		}
// 	}

	private function setOver24($target) {
		//24時までと以降をわける
		if ( 0 <= $target && $target < $this->datas->getConfigData('RCAL_CONFIG_OPEN_TIME') ) {
			return +$target+2400;
		}
		return $target;
	}

}
