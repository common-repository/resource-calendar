<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Booking_Edit extends ResourceCalendar_Page {



	protected $table_data = null;
	private $reservation_datas = null;
	private $target_day = '';

	private $unsetDatas = null;

	private $reservation_cd = null;

	private $msg = '';
	private $checkOk = false;

	private $valid_from = '';
	private $valid_to = '';

	private $isFromReservations = false;

	public function __construct() {
		parent::__construct();
	}
// 	public function get_target_day() {
// 		return $this->target_day;
// 	}

	public function isFromReservations () {
		return $this->isFromReservations;
	}

	public function get_target_day_afterRepeatCheck() {
		if  ($_POST['type'] == 'deleted' ) {
			if ($_POST['rcal_repeat_only_this'] == ResourceCalendar_Repeat::ONLY_UPDATE) {
				return $this->target_day;
			}
			else {
				return "";
			}
		}

		if (intval($_POST['rcal_repeat_patern']) == ResourceCalendar_Repeat::NO_REPEAT ) {
			//繰り返しから繰り返しなしの場合は繰り返しの情報を削除するので全部
			if (($_POST['type'] == 'updated' )&&($_POST['rcal_repeat_cd'] != ResourceCalendar_Repeat::NO_REPEAT )){
			}
			else {
				return $this->target_day;
			}
		}
		//上記以外は範囲分を全部
		return "";
	}

	public function set_reservation_datas($reservation_datas) {
		if ( (empty($reservation_datas) )
		  || (!is_array($reservation_datas))
		  || (count($reservation_datas) == 0) )  {
		  	//削除の場合は０件がありえるので無視。
			if  ($_POST['type'] != 'deleted' ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),1 );
			}
		}
		$this->reservation_datas = $reservation_datas;

	}

	public function setUnsetDatas($unsetDatas) {
		$this->unsetDatas = $unsetDatas;

	}

	public function set_config_datas($config_datas) {
		parent::set_config_datas($config_datas);
		$now = date_i18n('Ymd');
		$this->valid_from = ResourceCalendar_Component::computeDate(-1*$config_datas['RCAL_CONFIG_BEFORE_DAY'],substr($now,0,4),substr($now,4,2),substr($now,6,2));
		$this->valid_to = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY'],substr($now,0,4),substr($now,4,2),substr($now,6,2));

	}

	public function set_table_data($table_data) {
		$this->table_data = $table_data;
		$this->reservation_cd = $this->table_data['reservation_cd'];
	}

// 	public function get_table_data() {
// 		return $this->table_data;
// 	}

	public function set_reservation_cd($reservation_cd) {
		if (  strval($reservation_cd) == '' ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),1 );
		}
		$this->reservation_cd = $reservation_cd;
		$this->table_data['reservation_cd'] = $reservation_cd;
	}

	public function get_reservation_cd () {
		return $this->table_data['reservation_cd'];
	}

	public function check_request() {
		if (isset($_POST["rcal_from_reservations"]) && ! empty($_POST["rcal_from_reservations"])) {
			$this->isFromReservations = true;
		}
		//要会員登録の権限チェック
		if ($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] == ResourceCalendar_Config::USER_REGISTERED) {
			if (!is_user_logged_in()) {
				$this->msg  .= ResourceCalendar_Component::getMsg('E908',__function__.':'.__LINE__ );
				return $this->checkOk;
			}
		}
		if (empty($_POST['type'])) {
			$this->msg  .= ResourceCalendar_Component::getMsg('E901',__function__.':'.__LINE__ );
			return $this->checkOk;
		}

		//更新・削除でreservation_cdがない
		if ( ($_POST['type'] != 'inserted' )  && ( !isset($_POST['id']) || '' == strval($_POST['id'])) ) {
			$this->msg  .= ResourceCalendar_Component::getMsg('E908',__function__.':'.__LINE__ );
			return $this->checkOk;
		}

		if (!$this->_parse_data()) {
			return false;
		}

		$check_item = array('customer_name','booking_tel','booking_mail','resource_cd','time_from','time_to','booking_repeat_patern');
		if ($_POST['type'] != 'deleted'
			&& isset($_POST["rcal_repeat_patern"])
			&& $_POST["rcal_repeat_patern"] != ResourceCalendar_Repeat::NO_REPEAT ){
			$check_item[] = 'booking_starts';
			$check_item[] = 'booking_repeat_every';
			if  ( $_POST["rcal_repeat_patern"] == ResourceCalendar_Repeat::WEEKLY ) {
				$check_item[] = 'booking_repeat_week';
			}
			if ( $_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_CNT) {
				$check_item[] = 'booking_repeat_cnt';
			}
			if ( $_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
				$check_item[] = 'booking_ends';
			}
		}

		$check_item = apply_filters('rcal_booking_set_check_item', $check_item);
		$this->checkOk = parent::serverCheck($check_item,$this->msg);

		if  ($this->checkOk && $_POST['type'] != 'deleted' ) {
			//from toの大小
			$from = strtotime($_POST['rcal_time_from']);
			$to = strtotime($_POST['rcal_time_to']);
			if ($from >= $to) {
				$this->checkOk = false;
				$this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM003 '.__('Check reserved time ',RCAL_DOMAIN);
			}

			//fromは指定分以降より後
			$limit_time = new DateTime(date_i18n('Y-m-d H:i'));
			$limit_time->add(new DateInterval("PT".$this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE']."M"));
			if ($from < $limit_time->getTimestamp() ) {
				$this->checkOk = false;
				$this->msg .=  (empty($this->msg) ? '' : "\n"). 'EM004 '.sprintf(__('Your reservation is possible from %s.',RCAL_DOMAIN),$limit_time->format(__('m/d/Y',RCAL_DOMAIN).' H:i'));
			}
			//未来も制限がある
			if (strtotime($this->valid_to) < $from) {
				$this->checkOk = false;
				$this->msg .=  (empty($this->msg) ? '' : "\n").  'EM002 '.sprintf(__('The future times can not reserved. please less than %s days ',RCAL_DOMAIN),$this->config_datas['RCAL_CONFIG_AFTER_DAY']);
			}
			//繰り返しのチェック
			if ($_POST["rcal_repeat_patern"] != ResourceCalendar_Repeat::NO_REPEAT ){
				if ( $_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
					if ( isset($_POST["rcal_repeat_valid_to"])
						&& !empty($_POST["rcal_repeat_valid_to"])) {
						$repeat_from = strtotime($_POST["rcal_repeat_valid_from"]);
						$repeat_to   = strtotime($_POST["rcal_repeat_valid_to"]);
						if ($repeat_from >= $repeat_to) {
							$this->checkOk = false;
							$this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM005 '.__('Check repeat starts or ends ',RCAL_DOMAIN);
						}
						//未来も制限がある　時間は設定されないので含むの判定にする。
						//valid_fromで判定しないのは、繰り返し項目なので登録日などが設定されるため
						if (strtotime($this->valid_to) < $repeat_to) {
							$this->checkOk = false;
							$this->msg .=  (empty($this->msg) ? '' : "\n").  'EM006 '.sprintf(__('The future times can not reserved. please less than %s days ',RCAL_DOMAIN),$this->config_datas['RCAL_CONFIG_AFTER_DAY']);
						}
					}
				}
			}
		}
		return $this->checkOk;

	}

	private function _parse_data() {
		$_POST['status'] = '';
		//YYYY-MM-DD HH:MM 最後に読み直すために
// 		$split = explode(' ',$_POST['rcal_time_from']);
// 		$this->target_day = str_replace('-','',$split[0]);
		if (isset($_POST['rcal_time_from']) ) {
			$this->target_day = parent::_editDateFor24($_POST['rcal_time_from'], substr($this->config_datas['RCAL_CONFIG_OPEN_TIME'],0,2));
		}
		$isNormal = true;
		if (isset($_POST["rcal_repeat_valid_from"]) && !empty($_POST["rcal_repeat_valid_from"])) {
			 $check = ResourceCalendar_Component::checkAndEditRequestYmdForDb($_POST["rcal_repeat_valid_from"]);
			 if ($check === false) {
				$this->checkOk = false;
			 	$this->msg .=  (empty($this->msg) ? '' : "\n"). ResourceCalendar_Component::getMsg('E208', __("Starts on",RCAL_DOMAIN));
			 	$isNormal = false;
			 }
			 $_POST["rcal_repeat_valid_from"] = $check;
		}
		if (isset($_POST["rcal_repeat_valid_to"]) && !empty($_POST["rcal_repeat_valid_to"])) {
			 $check = ResourceCalendar_Component::checkAndEditRequestYmdForDb($_POST["rcal_repeat_valid_to"]);
			 if ($check === false) {
				$this->checkOk = false;
			 	$this->msg .=  (empty($this->msg) ? '' : "\n"). ResourceCalendar_Component::getMsg('E208', __("Ends on",RCAL_DOMAIN));
			 	$isNormal = false;
			 }
			 $_POST["rcal_repeat_valid_to"] = $check;
		}

		return $isNormal;
	}

	public function show_page() {
		if ($this->isFromReservations) {
			$this->show_page_reservations();
			return;
		}
		if ($this->checkOk ) {
			$res = parent::echoMobileData($this->reservation_datas,$this->target_day ,substr($this->config_datas['RCAL_CONFIG_OPEN_TIME'],0,2));
			if (empty($res[$this->target_day]) ) {
				 $res[$this->target_day] = '{"e":0}';
			}
			$msg = __('The reservation is completed',RCAL_DOMAIN);
			//管理者ではない場合
			if (!$this->isPluginAdmin($this->user_login) ) {
				if 	($this->config_datas['RCAL_CONFIG_CONFIRM_STYLE'] ==  ResourceCalendar_Config::CONFIRM_BY_ADMIN ) {
					//管理者による確認でも、削除は即反映する。
					if  ($_POST['type'] != 'deleted' ) {
						$msg = __('The reservation has not yet been completed. A Reservation Complete mail will be sent after an administrator has confirmed it.',RCAL_DOMAIN);
					}
				}
				elseif 	($this->config_datas['RCAL_CONFIG_CONFIRM_STYLE'] ==  ResourceCalendar_Config::CONFIRM_BY_MAIL ) {
					//メールによる確認では、更新時は前のステータスをそのまま、削除は即反映、登録は仮
					if  ($_POST['type'] == 'inserted' ) {
						$msg = __('The reservation has not yet been completed. Please open \"Confirm Reservation Screen\" from the mail which was sent and confirm the reservation.',RCAL_DOMAIN);
					}
					else if ($_POST['type'] == 'updated' ) {
						//reservation_datasにはその日のデータが複数入っているので該当データを見つける
						foreach ($this->reservation_datas as $k1 => $d1) {
							if ($this->reservation_cd == $d1['reservation_cd']) {
								if ($d1['status'] == ResourceCalendar_Reservation_Status::TEMPORARY) {
										$msg = __('The reservation has not yet been completed. Please open \"Confirm Reservation Screen\" from the mail which was sent and confirm the reservation.',RCAL_DOMAIN);
								}
							}
						}
					}
				}
				//確認なしなので完了にする。
				else {
				}
			}
			//繰り返しの場合は、全データを返却する
			if ($this->get_target_day_afterRepeatCheck() == "") {

				$echoData = '{	"status":"Ok","isRepeat":"1","message":"'.$msg.$this->editRepeatErrorMessage().'"';
				$echoData .= ',"set_data":'.'{';
				$comma = "";

				$from = substr($this->valid_from,0,10);
				$to = str_replace("-","",substr($this->valid_to,0,10));
				for($i=0;$i<10000;$i++) {
					$target =  date("Ymd",strtotime($from." + ".($i+1)." days") );
					if (array_key_exists($target,$res)) {
						$echoData .= $comma.'"'.$target.'":'.$res[$target];
//						$echoData = "rcalSchedule._daysResource[".$target."] = ".$res[$target].";";
					}
					else {
						$echoData .= $comma.'"'.$target.'":{"e":0}';
//						echo "rcalSchedule._daysResource[".$target."] = {\"e\":0};";
					}
					$comma = ',';
					if ($target == $to ) break;
				}

// 				foreach ($res as $k1 => $d1 ) {
// 					$echoData .= $comma.'"'.$k1.'":'.$d1;
// 					$comma = ',';
// 				}
				$echoData .= '}}';
				echo $echoData;

// 				echo '{	"status":"Ok","message":"'.$msg.'",
// 						"set_data":'.'{"'.$this->target_day.'":'.$res[$this->target_day].' '.
// 										',"20151010":'.$res[20151010].'} }';
// 						// 						"set_data":'.json_encode($res).' }';
			}
			//単一の登録の場合はこれまでとおり
			else {
				echo '{	"status":"Ok","isRepeat":"0","message":"'.$msg.'",
						"set_data":'.'{"'.$this->target_day.'":'.$res[$this->target_day].'} }';
			}
		}
		else {
			$msg['status'] = 'Error';
			$msg['message'] = $this->msg;
			echo json_encode($msg);
		}
	}

	public function editRepeatErrorMessage() {
		$dates = array();
		foreach ($this->unsetDatas as $k1 => $d1 ) {
			$target_date = __('%%m/%%d/%%Y',RCAL_DOMAIN);
			$target_date = str_replace('%%Y',substr($d1,0,4),$target_date);
			$target_date = str_replace('%%m',substr($d1,5,2),$target_date);
			$target_date = str_replace('%%d',substr($d1,8,2),$target_date);

			$dates[] = $target_date;
		}
		$addMessage = "";
		if (count($dates)>0 ) {
			$addMessage =  "\\n".__('But When the day meets the conditions for Repeat and there is already a registered reservation, the registered reservation will be given precedence. The message which will be displayed in such cases is shown below.',RCAL_DOMAIN)."\\n"
					.implode("\\n",$dates);
		}
		return $addMessage;
	}


	public function show_page_reservations() {
		if ($this->checkOk ) {
			$no = 0;
			foreach ($this->reservation_datas as $k1 => $d1) {
				$no++;
				if ($_POST['type'] == 'updated' && $_POST['id'] == $d1['reservation_cd']) {
					$this->reservation_datas[$k1]['no'] = __($_POST['type'],RCAL_DOMAIN);
				}
				else {
					$this->reservation_datas[$k1]['no'] = $no;
				}
				$this->reservation_datas[$k1]['check'] = '';
				$this->reservation_datas[$k1]['rcal_remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
				$this->reservation_datas[$k1]['rcal_name'] = htmlspecialchars($d1['name'],ENT_QUOTES);

				$this->reservation_datas[$k1]['memo'] = unserialize($d1['memo']);


				unset($this->reservation_datas[$k1]['remark']);
				unset($this->reservation_datas[$k1]['name']);
			}
			echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->reservation_datas).' }';
		}
		else {
			$msg['status'] = 'Error';
			$msg['message'] = $this->msg;
			echo json_encode($msg);

		}
	}
}