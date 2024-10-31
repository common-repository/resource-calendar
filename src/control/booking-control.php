<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'control/resource-calendar-control.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'data/booking-data.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'comp/booking-component.php');

class Booking_Control extends ResourceCalendar_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';

	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'BookingFront_Page';
//			$this->set_response_type(ResourceCalendar_Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Booking_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Booking_Component($this->datas);
		$this->permits = array('BookingFront_Page','Booking_Get_Reservation','Booking_Get_Month','Booking_Edit');
	}



	public function do_action() {

		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class();
		$this->pages->set_config_datas($this->config);

		$user_login = $this->datas->getUserLogin();
		$this->pages->setPluginAdmin($this->comp->isPluginAdmin());

		if ($this->action_class == 'BookingFront_Page' ) {

			if (!empty($user_login) ) {
				$this->pages->set_user_inf($this->datas->getUserInfDataByUserlogin($user_login));
			}
			$this->pages->setUserLogin($user_login);

			$this->pages->set_resource_datas($this->datas->getTargetResourceDataPhoto());
			$reservation_datas = $this->datas->getAllEventData();
			$this->pages->set_reservation_datas($reservation_datas);

			$this->pages->set_month_datas(
					$this->datas->getMonthDataFromResevation(
							$this->config['RCAL_CONFIG_OPEN_TIME']
							,$this->config['RCAL_CONFIG_CLOSE_TIME']
							,$reservation_datas
							,$this->pages->getValidTo()));

			if ($this->config['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
				$this->pages->set_category_datas($this->datas->getCategoryDatas());
			}

		}
//初期表示で範囲分設定しているのでコメント。初期読み取りを制御する場合用においておく
// 		elseif ($this->action_class == 'Booking_Get_Reservation' ) {
// 			if ($this->pages->check_request() ) {
// 				$this->pages->set_reservation_datas($this->datas->getAllEventData($this->pages->get_target_day(),true));
// 			}

// 		}
// 		elseif ($this->action_class == 'Booking_Get_Month' ) {
// 			if ($this->pages->check_request() ) {
// 				$this->pages->set_month_datas($this->datas->getMonthData(
// 						$this->pages->get_target_day_from()
// 						,$this->pages->get_target_day_to()
// 						,$this->config['RCAL_CONFIG_OPEN_TIME']
// 						,$this->config['RCAL_CONFIG_CLOSE_TIME']));
// 			}
// 		}
		elseif ($this->action_class == 'Booking_Edit') {
			if ($this->pages->check_request() ) {

				$this->pages->setUserLogin($user_login);
				$result = $this->comp->editTableData($user_login);
				$this->comp->serverCheck($result);

				$unsetDatas = array();
				$doneUpdate = $this->datas->doRepeatInformation($result,$unsetDatas);
				$this->pages->set_table_data($result);
				if ($_POST['type'] == 'inserted' ) {
					//繰り返しの新規登録をした場合は、先頭のreservation_cdを取得する
					if ( $doneUpdate ) {
						$this->pages->set_reservation_cd($this->datas->getResevationCdByRepeatCd($result['repeat_cd']));
					}
					else {
						$this->pages->set_reservation_cd($this->datas->insertTable($result));
					}
				}
				elseif ($_POST['type'] == 'updated' ) {
					//繰り返しの更新で、再度予約を作り直した場合は、先頭のreservation_cdを取得する
					if ( $doneUpdate  ) {
						$this->pages->set_reservation_cd($this->datas->getResevationCdByRepeatCd($result['repeat_cd']));
					}
					else {
						$this->datas->updateTable($result);
					}
				}
				elseif ($_POST['type'] == 'deleted' && $doneUpdate == false) {
					$this->datas->cancelTable($result);
				}

				$reread = $this->datas->getTargetReservationData($this->pages->get_reservation_cd());
// 				if (count($reread)>0) {
// 					$this->comp->sendMail($reread[0]);
// 				}
				$this->comp->sendMail($reread[0]);
				$target_day = $this->pages->get_target_day_afterRepeatCheck();
				if ($this->pages->isFromReservations() ) {
					$this->pages->set_reservation_datas($this->datas->getAllEventDataFromAdmin());
				}
				else {
					if ($target_day == "") {
						$this->pages->set_reservation_datas($this->datas->getAllEventData());
						$this->pages->setUnsetDatas($unsetDatas);
					}
					else {
						$this->pages->set_reservation_datas($this->datas->getAllEventData($target_day,true));
					}
				}
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'BookingFront_Page') wp_die();

	}
}		//class


