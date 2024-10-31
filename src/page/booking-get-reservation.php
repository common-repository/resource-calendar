<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Booking_Get_Reservation extends ResourceCalendar_Page {

	private $target_day = '';
	private $reservation_datas = null;
	private $first_hour = '';

	private $checkOk = true;
	private $msg = '';

	public function __construct() {
		parent::__construct();
		$this->first_hour = $_POST['first_hour'];
		$this->target_day = $_POST['rcal_target_day'];

	}

	public function get_target_day() {
		return $this->target_day;
	}



	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;

	}




	public function check_request() {

		$check_item = array('target_day_mobile');
		$this->checkOk = parent::serverCheck($check_item,$this->msg);
		if ($this->checkOk) {
			$target = date("Y-m-d H:i:s", strtotime($_POST['rcal_target_day']));
			$before = ResourceCalendar_Component::computeDate(-1*$this->config_datas['RCAL_CONFIG_BEFORE_DAY']);
			$after = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY']);
			if ($target < $before || $after < $target) {
			  $this->checkOk = false;
			  $this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM005 '.__('Date is out of ranges.',RCAL_DOMAIN);
			}
		}
		return $this->checkOk;

	}

	public function show_page() {
		if ($this->checkOk ) {
			$res = parent::echoMobileData($this->reservation_datas,$this->target_day ,$this->first_hour);
			echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
			"set_data":'.'{"'.$this->target_day.'":'.$res[$this->target_day].'} }';
		}
		else {
			$msg['status'] = 'Error';
			if (empty($errno) ) $msg['message'] = $this->msg;
			else $msg['message'] = $errstr.$detail_msg.'('.$errno.')';
			echo json_encode($msg);
		}


	}
}