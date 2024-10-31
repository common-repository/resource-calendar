<?php
class ResourceCalendarException extends Exception
{
	public function __construct($message ="resource calendar unknown error !!!", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

}

class ResourceCalendar_Table_Status {
	const INIT = 0;
	const DELETED = 3;
}

class ResourceCalendar_Response_Type {
	const JASON = 1;
	const HTML =2;
	const XML = 3;
	const JASON_406_RETURN = 4;
}

class ResourceCalendar_Status {
	const OPEN = 0;
	const CLOSE = 1;
	const CHECKED = 1;
	const NO_CHECKED = 2;
}

class ResourceCalendar_Reservation_Status {
	const COMPLETE = 1;
	const TEMPORARY = 2;
	const CANCELED =  3;
	const INIT =  0;
}

class ResourceCalendar_Edit {
	const OK = 1;
	const NG = 0;
}

class ResourceCalendar_Repeat {
	const NO_REPEAT = 1;
	const DAILY = 2;
	const WEEKLY = 3;
	const MONTHLY = 4;
	const YEARLY = 5;

//	const END_NEVER = 1;
	const END_CNT = 2;
	const END_DATE = 3;

	const ONLY_UPDATE = 1;
	const ALL_UPDATE = 2;

	const NO_NEED_UPDATE = 1;
	const NEED_UPDATE = 11;

}


class ResourceCalendar_Config {
	const OPEN_TIME = '0900';
	const CLOSE_TIME ='2200';
	const TIME_STEP = 15;



	const DEFALUT_BEFORE_DAY = 3;
	const DEFALUT_AFTER_DAY = 100;

	const DETAIL_MSG_OK = 1;
	const DETAIL_MSG_NG = 2;
	const NAME_ORDER_JAPAN = 1;
	const NAME_ORDER_OTHER = 2;
	const LOG_NEED =1;
	const LOG_NO_NEED =2;
	const TAP_INTERVAL = 500;
	//
	const DEFALUT_RESERVE_DEADLINE = 5;
	const DEFALUT_RESERVE_DEADLINE_UNIT_DAY = 1;
	const DEFALUT_RESERVE_DEADLINE_UNIT_HOUR = 2;
	const DEFALUT_RESERVE_DEADLINE_UNIT_MIN = 3;


	const CONFIRM_NO = 1;
	const CONFIRM_BY_ADMIN = 2;
	const CONFIRM_BY_MAIL = 3;

	const USER_ANYONE = 1;
	const USER_REGISTERED = 2;

	const SETTING_PATERN_TIME = 1;
	const SETTING_PATERN_ORIGINAL = 2;

	const RCAL_CONFIG_SHOW_DATEPICKER_CNT= 3;

	const USE_SESSION = 1;
	const USE_NO_SESSION = 2;

	const USE_SUBMENU = 1;
	const USE_NO_SUBMENU = 2;

	const REQUIRED = 1;

}


class ResourceCalendar_Color {
	const HOLIDAY = "#FFCCFF";
	const USUALLY = "#6699FF";
}

class ResourceCalendar_HOLIDAY_PATERN {
	const FULL = 0;
	const HALF = 1;
}

class ResourceCalendar_Category {
	const RADIO = 1;
	const CHECK_BOX = 2;
	const TEXT = 3;
	const SELECT = 4;
	const MAIL = 5;
}


class ResourceCalendar_Component {

	private $version = '1.0';
	protected $datas = null;
	private $mailErrorInformation = "";

	public function __construct() {

	}

//ここからメール
	public function sendMail($set_data,$is_ConfirmbyCustomer = false) {
		if (empty($set_data)) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',__FILE__.":".__function__.':'.__LINE__ ),1 );

		}
		if($set_data['status'] == ResourceCalendar_Reservation_Status::CANCELED) {
			$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
					$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_CANCELED'),
					$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_CANCELED'));
		}
		if 	($this->datas->getConfigData('RCAL_CONFIG_CONFIRM_STYLE') ==  ResourceCalendar_Config::CONFIRM_BY_ADMIN ) {
			//管理者が登録／更新（承認）したらユーザに対する完了メールだけ「予約が完了しました」
			if ($this->isPluginAdmin() ) {
				if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
					$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'));
				}
			}
			else {
				if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
					//管理者にメールを送る
					$this->sendInformationMail($set_data,$this->getMailOfAdminUser(),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_ADMIN'),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_ADMIN'));
					//ここではユーザに対してお知らせメールを送る。「予約を受け付けました」
					$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED'),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_ACCEPTED'));
				}
			}
		}
		elseif ($this->datas->getConfigData('RCAL_CONFIG_CONFIRM_STYLE') ==  ResourceCalendar_Config::CONFIRM_BY_MAIL ) {
			//管理者が登録したらユーザに対するお知らせメールだけ
			if ($this->isPluginAdmin() ) {
				if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
					$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'),
							$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'));
				}
			}
			else {
				//客が予約確定画面から更新したら客へ「予約が完了しました」
				if ($is_ConfirmbyCustomer) {
					if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
						$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
								$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'),
								$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'));
					}
				}
				else {
					//客が登録したら客へ「以下のＵＲＬで確認してね」
					if ($set_data['status'] == ResourceCalendar_Reservation_Status::TEMPORARY) {
						$this->sendMailForConfirm($set_data);
					}
					//客が完了したのを更新したら客へ「予約が完了しました」
					else if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
						$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
								$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'),
								$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'));
					}
				}
			}
		}
		else {
			if($set_data['status'] != ResourceCalendar_Reservation_Status::CANCELED) {
			//即登録は完了した旨を客へ
				$this->sendInformationMail($set_data,$this->setCustomerMailAddress($set_data),
						$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'),
						$this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'));
			}
		}
		//スタッフへのお知らせはいつでも送る
		$this->sendInformationMail($set_data);
		//カテゴリーでメールの設定をしている場合はそこにも送る。
		$mailCategory = $this->datas->getCategoryDatas(" AND  category_patern = ".ResourceCalendar_Category::MAIL);
		if (count($mailCategory) > 0 ) {
			$subMenuDatas = unserialize($set_data['memo']);
			//
			$key = "i".$mailCategory[0]['category_cd'];
			if (array_key_exists($key, $subMenuDatas)) {
				$this->sendInformationMail($set_data,$subMenuDatas[$key]);
			}
		}
	}

	private function setCustomerMailAddress($set_data) {

 		$require_array = explode(',',$this->datas->getConfigData('RCAL_CONFIG_REQUIRED'));
 		if ( is_array($require_array) && in_array('rcal_mail',$require_array) == false  ) {
 			return "";
 		}
		return $set_data['name']."<".$set_data['mail'].">";
	}

	public function getMailOfAdminUser() {
		$mails = array();
		$users = get_users(array('role'=>'administrator'));
		foreach ($users as $k1 => $d1 ) {
			$mails[] = $d1->user_email;
		}
		return implode(',',$mails);
	}

	public function sendMailForConfirm($set_data) {

		$to = $this->setCustomerMailAddress($set_data);
		$subject = sprintf($this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT').'[%d]',
				$set_data['reservation_cd']);
		$url = get_bloginfo( 'url' );
		$page = get_option('rcal_confirm_page_id');
		$send_mail_text = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT');

		$body = $send_mail_text;

		$url = sprintf('%s/?page_id=%d&P1=%d&P2=%s',$url,intval($page),
				intval($set_data['reservation_cd']),
				$set_data['activate_key']);

		$body = str_replace('{X-URL}',$url,$body);

		$resource_name = $this->datas->getResourceName($set_data['resource_cd']);

		$body = str_replace('{X-TO_NAME}',htmlspecialchars($set_data['name'],ENT_QUOTES),$body);

		$body = str_replace('{X-TO_TIME}',$set_data['target_day'].' '.$set_data['time_from'].' - '.$set_data['time_to'],$body);
		$body = str_replace('{X-TO_RESOURCE}',htmlspecialchars($resource_name,ENT_QUOTES),$body);
		$body = str_replace('{X-TO_REMARK}',htmlspecialchars($set_data['remark'],ENT_QUOTES),$body);

		$body = str_replace('{X-SHOP_NAME}',$this->datas->getConfigData('RCAL_CONFIG_NAME'),$body);
		$body = str_replace('{X-SHOP_ADDRESS}',$this->datas->getConfigData('RCAL_CONFIG_ADDRESS'),$body);
		$body = str_replace('{X-SHOP_TEL}',$this->datas->getConfigData('RCAL_CONFIG_TEL'),$body);
		$body = str_replace('{X-SHOP_MAIL}',$this->datas->getConfigData('RCAL_CONFIG_MAIL'),$body);

		$body = str_replace('{X-TO_REPEAT_INF}',$this->editRepeatInf($set_data),$body);
		$body = apply_filters('rcal_replace_mail_body_confirm',$body,$set_data);

		$header = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_FROM');
		if (!empty($header))	$header = "from:".$header."\n";

		add_action( 'phpmailer_init', array( &$this,'setReturnPath') );
		add_action('wp_mail_failed', array( &$this,'getMailErrorInformation'));

		if (wp_mail( $to,$subject, $body,$header ) === false ) {
			//phpmailerのsendで直接falseを返す場合あり？
			if ($this->mailErrorInformation == ":" || $this->mailErrorInformation == "") {
				global $phpmailer;
				$this->mailErrorInformation = "PHP ErrorInformation:".$phpmailer->ErrorInfo;
			}
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E907',$this->mailErrorInformation),1);
		}
	}

	public function getMailErrorInformation($wpError) {
		if (is_wp_error($wpError) ) {
			$this->mailErrorInformation  = $wpError->get_error_code();
			$this->mailErrorInformation  .= ":".$wpError->get_error_message();
		}
	}

	public function editRepeatInf($set_data) {

		if ($set_data['repeat_patern']==ResourceCalendar_Repeat::NO_REPEAT) {
			return "";
		}
		//キャンセルで繰り返しのうち単独のキャンセルの場合は、メール上からも消す
		if ($_POST['type'] == 'deleted'
		&& $_POST['rcal_repeat_only_this'] == ResourceCalendar_Repeat::ONLY_UPDATE) {
			return "";
		}
		$edit = __('Repeat',RCAL_DOMAIN).":";
		if($set_data['repeat_patern']==ResourceCalendar_Repeat::YEARLY) {
			if ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
				if ($set_data['repeat_every'] == 1) {
					$edit .= sprintf(__("Annually on %s, until %s")
							,$set_data['repeat_mm']."/".$set_data['repeat_dd']
							,$set_data['repeat_valid_to']
							);
				}
				else {
					$edit .= sprintf(__("Every %d years on %s, until %s")
						,$set_data['repeat_every']
						,$set_data['repeat_mm']."/".$set_data['repeat_dd']
						,$set_data['repeat_valid_to']
						);
				}
			}
			elseif ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_CNT) {
				if ($set_data['repeat_cnt'] == 1) {
					$edit .= "Once";
				}
				else {
					if ($set_data['repeat_every'] == 1) {
						$edit .= sprintf(__("Annually on %s, %d times")
							,$set_data['repeat_mm']."/".$set_data['repeat_dd']
							,$set_data['repeat_cnt']
						);
					}
					else {
						$edit .= sprintf(__("Every %d years on %s, %d times")
							,$set_data['repeat_every']
							,$set_data['repeat_mm']."/".$set_data['repeat_dd']
							,$set_data['repeat_cnt']
						);
					}

				}

			}
		}
		elseif($set_data['repeat_patern']==ResourceCalendar_Repeat::MONTHLY) {
			$set_dd = substr($_POST['rcal_time_from'],8,2);
			if ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
				if ($set_data['repeat_every'] == 1) {
					$edit .= sprintf(__("Monthly on %d, until %s")
// 							,$set_data['repeat_dd']
							,$set_dd
							,$set_data['repeat_valid_to']
							);
				}
				else {
					$edit .= sprintf(__("Every %d months on %d, until %s")
						,$set_data['repeat_every']
// 							,$set_data['repeat_dd']
						,$set_dd
						,$set_data['repeat_valid_to']
						);
				}
			}
			elseif ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_CNT) {
				if ($set_data['repeat_cnt'] == 1) {
					$edit .= "Once";
				}
				else {
					if ($set_data['repeat_every'] == 1) {
						$edit .= sprintf(__("Monthly on %d, %d times")
// 							,$set_data['repeat_dd']
							,$set_dd
							,$set_data['repeat_cnt']
						);
					}
					else {
						$edit .= sprintf(__("Every %d months on %d, %d times")
							,$set_data['repeat_every']
// 							,$set_data['repeat_dd']
							,$set_dd
							,$set_data['repeat_cnt']
						);
					}

				}

			}
		}
		elseif($set_data['repeat_patern']==ResourceCalendar_Repeat::DAILY) {
			if ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
				if ($set_data['repeat_every'] == 1) {
					$edit .= sprintf(__("Daily, until %s")
							,$set_data['repeat_valid_to']
							);
				}
				else {
					$edit .= sprintf(__("Every %d days, until %s")
						,$set_data['repeat_every']
						,$set_data['repeat_valid_to']
						);
				}
			}
			elseif ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_CNT) {
				if ($set_data['repeat_cnt'] == 1) {
					$edit .= "Once";
				}
				else {
					if ($set_data['repeat_every'] == 1) {
						$edit .= sprintf(__("Daily, %d times")
							,$set_data['repeat_cnt']
						);
					}
					else {
						$edit .= sprintf(__("Every %d days, %d times")
							,$set_data['repeat_every']
							,$set_data['repeat_cnt']
						);
					}

				}

			}
		}
		elseif($set_data['repeat_patern']==ResourceCalendar_Repeat::WEEKLY) {
			$week_long = explode(',',__('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',RCAL_DOMAIN));
			$setWeek = explode(',' ,$set_data['repeat_weeks']);
			$setWeekArray = array();

			foreach( $setWeek as $d1 ) {
				$setWeekArray[] = str_replace("\"","", $week_long[$d1]);
			}
			$setWeekInformation = implode(',',$setWeekArray);

			if ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_DATE) {
				if ($set_data['repeat_every'] == 1) {
					$edit .= sprintf(__("Weekly on %s, until %s")
							,$setWeekInformation
							,$set_data['repeat_valid_to']
							);
				}
				else {
					$edit .= sprintf(__("Every %d weeks on %s, until %s")
							,$set_data['repeat_every']
							,$setWeekInformation
							,$set_data['repeat_valid_to']
						);
				}
			}
			elseif ($_POST["rcal_repeat_end_patern"] == ResourceCalendar_Repeat::END_CNT) {
				if ($set_data['repeat_cnt'] == 1) {
					$edit .= "Once";
				}
				else {
					if ($set_data['repeat_every'] == 1) {
						$edit .= sprintf(__("Weekly on %s, %d times")
							,$setWeekInformation
							,$set_data['repeat_cnt']
						);
					}
					else {
						$edit .= sprintf(__("Every %d weeks on %s, %d times")
							,$set_data['repeat_every']
							,$setWeekInformation
							,$set_data['repeat_cnt']
						);
					}

				}

			}
		}
		return $edit;

	}
	public function sendInformationMail($set_data,$to = "",$subject = "",$send_mail_text = "") {
		//メールによる確認で管理者が登録した場合は、ユーザに完了のお知らせメッセージを送る
		if (empty($to) ) {
			$to = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_BCC');
		}
		//BCCには初期では値の設定がないはず
		if (!empty($to)  ){
			if (empty($subject) ) {
				$subject = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_SUBJECT_INFORMATION');
			}
			$header = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_FROM');
			if (!empty($header))	$header = "from:".$header."\n";
			add_action( 'phpmailer_init', array( &$this,'setReturnPath') );
			if (empty($send_mail_text) ) {
				$send_mail_text = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_TEXT_INFORMATION');
			}

			$body = $send_mail_text;

			$status = "";
			if($set_data['status'] == ResourceCalendar_Reservation_Status::TEMPORARY) {
				$status  = __('Temporary Reservation',RCAL_DOMAIN);
			}
			else {
				if($set_data['status'] == ResourceCalendar_Reservation_Status::CANCELED) {
					$status  = __('Canceled Reservation',RCAL_DOMAIN);
				}
				else {
					$status  = __('Completed Reservation',RCAL_DOMAIN);
				}
			}
			$resource_name = "";
			//
			$type = $_POST['type'];
			if ($type == "deleted" ) $type="canceled";
//言語対応でダミーソースをいれとく
$d1 = __('exec',RCAL_DOMAIN);
$d1 = __('inserted',RCAL_DOMAIN);
$d1 = __('updated',RCAL_DOMAIN);
$d1 = __('canceled',RCAL_DOMAIN);
//言語対応でダミーソースをいれとく
			$status = sprintf(__('Action:%s Satus:%s',RCAL_DOMAIN),__($type,RCAL_DOMAIN),__($status,RCAL_DOMAIN));
			$resource_name = $this->datas->getResourceName($set_data['resource_cd']);

			$body = str_replace('{X-TO_STATUS}',$status,$body);
			$body = str_replace('{X-TO_NAME}',htmlspecialchars($set_data['name'],ENT_QUOTES),$body);
			$body = str_replace('{X-TO_TIME}',$set_data['target_day'].' '.$set_data['time_from'].' - '.$set_data['time_to'],$body);
			$body = str_replace('{X-TO_RESOURCE}',htmlspecialchars($resource_name,ENT_QUOTES),$body);
			$body = str_replace('{X-TO_REMARK}',htmlspecialchars($set_data['remark'],ENT_QUOTES),$body);


			$body = str_replace('{X-SHOP_NAME}',$this->datas->getConfigData('RCAL_CONFIG_NAME'),$body);
			$body = str_replace('{X-SHOP_ADDRESS}',$this->datas->getConfigData('RCAL_CONFIG_ADDRESS'),$body);
			$body = str_replace('{X-SHOP_TEL}',$this->datas->getConfigData('RCAL_CONFIG_TEL'),$body);
			$body = str_replace('{X-SHOP_MAIL}',$this->datas->getConfigData('RCAL_CONFIG_MAIL'),$body);

			$body = str_replace('{X-TO_REPEAT_INF}',$this->editRepeatInf($set_data),$body);

			$body = apply_filters('rcal_replace_mail_body_info',$body,$set_data);

			add_action('wp_mail_failed', array( &$this,'getMailErrorInformation'));

			if (wp_mail( $to,$subject, $body,$header ) === false ) {
				//phpmailerのsendで直接falseを返す場合あり？
				if ($this->mailErrorInformation == ":" || $this->mailErrorInformation == "") {
					global $phpmailer;
					$this->mailErrorInformation = "PHP ErrorInformation:".$phpmailer->ErrorInfo;
				}
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E907',$this->mailErrorInformation),1);
			}


		}
	}


	public function setReturnPath( $phpmailer ) {
		$path = $this->datas->getConfigData('RCAL_CONFIG_SEND_MAIL_RETURN_PATH');
		if (empty($path)) return;
		$phpmailer->Sender = $path;
	}

//ここまでメール


	static function getMsg($err_cd, $add_char = '') {
		$err_msg = '';
		switch ($err_cd) {
			case 'I001':
				$err_msg = sprintf(__("Updates cannot be performed on the demo site. ",RCAL_DOMAIN),$add_char);
				break;
			case 'N001':
				$err_msg = $err_cd.' '.sprintf(__("%s Completed successfully.",RCAL_DOMAIN),$add_char);
				break;
			case 'E007':
				$err_msg = sprintf(__("%s An unexpected error has occurred %s",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E011':
				$err_msg = $err_cd.' '.sprintf(__("The open time of this reservation has already passed.[%s]",RCAL_DOMAIN),$add_char);
				break;
// 			case 'E012':
// 				$err_msg = $err_cd.' '.sprintf(__("This reservation updated. [%s]",RCAL_DOMAIN),$add_char);
// 				break;
			case 'E021':
				$err_msg = $err_cd.' '.sprintf(__("This update may be an invalid request.[%s]",RCAL_DOMAIN),$add_char);
				break;
			case 'E201':
				$err_msg = sprintf(__("%s This field is mandatory.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E202':
				$err_msg = sprintf(__("%s Please review the time setting.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E203':
				$err_msg = sprintf(__("%s Please input using numerals.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E205':
				$err_msg = sprintf(__("%s Please input the postal code as XXXX-XXX.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E206':
				$err_msg = sprintf(__("%s Please input the phone number using numerals.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E207':
				$err_msg = sprintf(__("%s Please input the mail address in the following format: XXX@XXX.XXX[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E208':
				$err_msg = sprintf(__("%s Please input the date as MM/DD/YYYY or MMDDYYYY.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E209':
				$err_msg = sprintf(__("%s This date does not exist.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E210':
				$err_msg = sprintf(__("%s Please enter a space between the last and first names.[%s]",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E211':
				$err_msg = sprintf(__("%s Please input in %d characters or less.[%s]",RCAL_DOMAIN),$err_cd,$add_char[0],$add_char[1]);
				break;
			case 'E213':
				$err_msg = $err_cd.' '.__("Reservation cannot be made for this time frame.",RCAL_DOMAIN);
				break;
			case 'E214':
				$err_msg = sprintf(__("%s There is an error in the time settings.[%s] [%s]",RCAL_DOMAIN),$err_cd,$add_char[0],$add_char[1]);
				break;
			case 'E215':
				$err_msg = $err_cd.' '.sprintf(__("Please confirm the valid period.",RCAL_DOMAIN));
				break;
			case 'E216':
				$err_msg = sprintf(__("%s This value need  %s <= this value <= %s [%s]",RCAL_DOMAIN),$err_cd,$add_char[0],$add_char[1],$add_char[2]);
				break;
			case 'E217':
				$err_msg = sprintf(__("%s Please input in the following format:NAME<XXX@XXX.XXX>",RCAL_DOMAIN),$err_cd,$add_char);
				break;
			case 'E218':
				$err_msg = sprintf(__("%s \"Mail\" of \"field type\" is already registered",RCAL_DOMAIN),$err_cd);
				break;
// 			case 'E301':
// 				$err_msg = $err_cd.' '.sprintf(__("This time periods already reserved. [%s]",RCAL_DOMAIN),$add_char);
// 				break;
			case 'E302':
				$err_msg = $err_cd.' '.sprintf(__("The time setting is out of range.",RCAL_DOMAIN),$add_char);
				break;
			case 'E303':
				$err_msg = $err_cd.' '.sprintf(__("The reservation may be overlapping with another reservation. Please perform the operation again after refreshing the display.",RCAL_DOMAIN));
				break;
			case 'E401':
				$err_msg = __('The process will be terminated for an unexpected error has occurred.',RCAL_DOMAIN);
				break;
			case 'E901':
				$err_msg = $err_cd.' '.sprintf(__("There is a problem with the data.[%s]",RCAL_DOMAIN),$add_char);
				break;
			case 'E902':
				$err_msg = $err_cd.' '.sprintf(__("Database error has occurred.[%s][%s]",RCAL_DOMAIN),$add_char[0],$add_char[1]);
				break;
			case 'E904':
				$err_msg = $err_cd.' '.sprintf(__("File error[%s]",RCAL_DOMAIN),$add_char);
				break;
			case 'E906':
				$err_msg = $err_cd.' '.sprintf(__("There is no target data.",RCAL_DOMAIN));
				break;
			case 'E907':
				$err_msg = $err_cd.' '.sprintf(__("An error occurred in the mail sending operation.[%s]",RCAL_DOMAIN),$add_char);
				break;
			case 'E908':
				$err_msg = $err_cd.' '.sprintf("This access is out of the authority[%s]",$add_char);
				break;
			case 'E909':
				$err_msg = $err_cd.' '.sprintf(__("This reservation has been updated by another operation. Please perform the operation again after refreshing the display.[%s]",RCAL_DOMAIN),$add_char);
				break;
			case 'E910':
				$err_msg = $err_cd.' '.sprintf(__("This reservation does not exist.[%s]",RCAL_DOMAIN),$add_char);
				break;
			default:
				$err_msg = $err_cd.__("This message is not found",RCAL_DOMAIN).$add_char;

		}
		return $err_msg;
	}

	static function isRequestEmpty($in) {
		if ( '' == strval($in)) {
			return true;
		}
		return false;
	}

	static function computeDate($addDays = 1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$baseSec = mktime(0, 0, 0, $month, $day, $year);
		$addSec = $addDays * 86400;
		$targetSec = $baseSec + $addSec;
		return date("Y-m-d H:i:s", $targetSec);
	}

	static function getMonthEndDay($year, $month) {
		$dt = mktime(0, 0, 0, $month + 1, 0, $year);
		return date("d", $dt);
	}

	static function computeMonth($addMonths=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$month += $addMonths;
		$endDay = self::getMonthEndDay($year, $month);
		if($day > $endDay) $day = $endDay;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}

	static function computeYear($addYears=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$year += $addYears;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}

	static function checkTime($time_data) {
		$hour = +substr($time_data,0,2);
		if ($hour < 0 || $hour > 47 || !is_numeric($hour)) {
			return false;
		}
		$min = +substr($time_data,2);
		if ($min < 0 || $min > 59 || !is_numeric($min)) {
			return false;
		}
		return true;
	}


	static function formatTime($time_data) {

		return sprintf("%02s:%02s",+substr($time_data,0,2),substr($time_data,2,2));
	}

	static function replaceTimeToDb($time_data) {
		if (preg_match('/(?<hour>\d+):(?<minute>\d+)/', $time_data, $matches) == 0 ) {
			$matches['hour'] = substr($time_data,0,2);
			$matches['minute'] = substr($time_data,2,2);
		}
		return sprintf("%02d%02d",+$matches['hour'],+$matches['minute']);
	}


	static function checkAndEditRequestYmdForDb($in) {
//		if (empty($in) ) return;
		if ( '' == strval($in)) {
			return true;
		}
		if (preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',RCAL_DOMAIN).'$/',$in,$matches) == 0 ) {
		   preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',RCAL_DOMAIN).'$/',$in,$matches);
		}


		if ( checkdate(+$matches['month'],+$matches['day'],+$matches['year']) == false ) {
			return false;
		}

		return sprintf("%4d-%02d-%02d",+$matches['year'],+$matches['month'],+$matches['day']);
	}

	static function editCountryYmd($yyyy,$mm,$dd) {
		$format = __("%m/%d/%Y",RCAL_DOMAIN);
		$format = str_replace("%Y",$yyyy, $format );
		$format = str_replace("%m",$mm, $format );
		$format = str_replace("%d",$dd, $format );
		return $format;
	}


	static function getDayOfWeek($in) {
		return date("w", strtotime($in));
	}

	static function isMobile(){

		$useragents = array(
			'iPhone', // iPhone
			'iPod', // iPod touch
			'Android.*Mobile', // 1.5+ Android *** Only mobile
			'Windows.*Phone', // *** Windows Phone
			'dream', // Pre 1.5 Android
			'CUPCAKE', // 1.5+ Android
			'blackberry9500', // Storm
			'blackberry9530', // Storm
			'blackberry9520', // Storm v2
			'blackberry9550', // Storm v2
			'blackberry9800', // Torch
			'webOS', // Palm Pre Experimental
			'incognito', // Other iPhone browser
			'webmate' // Other iPhone browser
		);
		$pattern = '/'.implode('|', $useragents).'/i';
		return (preg_match($pattern, $_SERVER['HTTP_USER_AGENT']) == 1) ;
	}

	static function calcMinute($from,$to) {
		//$from toはHHMM
		if (strlen($from) == 3 ) $from = '0'.$from;
		if (strlen($to) == 3 ) $to = '0'.$to;
// 		$pasttime=strtotime('2000/01/01 '.sprintf("%s:%s:00",substr($from,0,2),substr($from,2,2)));
// 		$thistime=strtotime('2000/01/01 '.sprintf("%s:%s:00",substr($to,0,2),substr($to,2,2)));
		$pasttime=self::checkOver24($from);
		$thistime=self::checkOver24($to);
		$diff=$thistime-$pasttime;
		return floor($diff/60);
	}

	static function checkOver24($time) {
		$hh = +substr($time,0,2);
		if (23 < $hh) {
			return strtotime('2000/01/02 '.sprintf("%s:%s:00",$hh-24,substr($time,2,2)));
		}
		else {
			return strtotime('2000/01/01 '.sprintf("%s:%s:00",$hh,substr($time,2,2)));
		}
	}

	static function editOver24($time) {
		$hh = +substr($time,0,2);
		if (23 < $hh) {
			$hh = $hh - 24;
		}
		return sprintf("%02s:%02s",$hh,substr($time,-2));
	}

	static function checkRole($class_name) {
		$class_name_array = explode('_',$class_name);
		if (empty($class_name_array[0]) ) {
				throw new ResourceCalendarException(self::getMsg('E908',basename(__FILE__).':'.__LINE__),1);
		}
		$target_name = strtolower ($class_name_array[0]);
		if ( $target_name == 'booking'  ) return;
		if ( $target_name == 'confirm'  ) return;
// 		global $current_user;
// 		get_currentuserinfo();

		$current_user = wp_get_current_user();

		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);

		//このプラグインでは購読者は管理させない
		if (empty($user_role) || $user_role == 'subscriber' ) {
				throw new ResourceCalendarException(self::getMsg('E908',basename(__FILE__).':'.__LINE__),1);
		}
	}

	//ここは後で権限のロジックをいれるためにきりだしておく
	//ここはログインユーザの権限を調べる
	//現状はsubscriber=利用者以外はOKにしとく
	//
	public function isPluginAdmin(){
		if ( is_super_admin() ) {
			return true;
		}
		$userInformation = wp_get_current_user( );
		if ($userInformation->exists() ) {
			$noAdminRoleOfThisPlugin = apply_filters('rcal_set_admin_role',array('subscriber'));
			if (is_array($noAdminRoleOfThisPlugin)) {
				foreach ($noAdminRoleOfThisPlugin as $d1) {
					if ($userInformation->roles[0] == $d1 ) {
						return false;
					}
				}
			}
			else {
				if ($userInformation->roles[0] == $noAdminRoleOfThisPlugin ) {
					return false;
				}
			}
			return true;
		}
		return false;

	}




}