<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');

	
class Booking_Get_Month extends ResourceCalendar_Page {
	
	private $target_day_from = '';
	private $target_day_to = '';
	private $month_datas = null;
	
	private $checkOk = true;
	private $msg = '';
		
	public function __construct() {
		parent::__construct();
		$this->target_day_from = $_POST["from"];
		$this->target_day_to = $_POST["to"];
	}
	
	public function get_target_day_from() {
		return $this->target_day_from;
	}
	public function get_target_day_to() {
		return $this->target_day_to;
	}
	
	
	

	public function set_month_datas($month_datas) {
		$this->month_datas = $month_datas;
		
	}


	public function check_request() {

//		$check_item = array('target_day_mobile');
//		$this->checkOk = parent::serverCheck($check_item,$this->msg);
//		if ($this->checkOk) {
//			$target = date("Y-m-d H:i:s", strtotime($_POST['target_day']));
//			$before = ResourceCalendar_Component::computeDate(-1*$this->config_datas['RCAL_CONFIG_BEFORE_DAY']);
//			$after = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY']);
//			if ($target < $before || $after < $target) {
//			  $this->checkOk = false;
//			  $this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM005 '.__('Date is out of ranges.',RCAL_DOMAIN);
//			}
//		}
		return $this->checkOk;		
		
	}

	public function show_page() {
		
		if ($this->checkOk ) {
			$yyyymm = "";
			foreach ( $this->month_datas as $k1 => $d1 ) {
				$yyyymm = $k1;
				foreach ( $d1 as $k2 => $d2 ) {
					$title_data = array();
					foreach ($d2 as $k3=>$d3 ) {
						if ($k3 == ResourceCalendar_Reservation_Status::COMPLETE ){
							$title_data [] = sprintf(__('Completed Reservation',RCAL_DOMAIN).":%d ",$d3);
						}
						else if ($k3 == ResourceCalendar_Reservation_Status::TEMPORARY ){
							$title_data[] = sprintf(__('Temporary Reservation',RCAL_DOMAIN).":%d  ",$d3);
						}
						unset($this->month_datas[$k1][$k2][$k3]);
					}
					$this->month_datas[$k1][$k2] = implode('\n',$title_data);
				}
			}


			echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
			"set_data":'.'{"yyyymm":"'.$yyyymm.'","cnt":"'.count($this->month_datas[$yyyymm]).'","datas":'.json_encode($this->month_datas[$yyyymm]).'} }';
		}
		else {
			$msg['status'] = 'Error';
			if (empty($errno) ) $msg['message'] = $this->msg;
			else $msg['message'] = $errstr.$detail_msg.'('.$errno.')';
			echo json_encode($msg);
		}


	}
}