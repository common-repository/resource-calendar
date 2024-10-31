<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Config_Edit extends ResourceCalendar_Page {

	private $table_data = null;

	public function __construct() {
		parent::__construct();
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}


	public function check_request() {
		if (empty($_POST['type'])) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		$checks = array();
		if ($_POST['type'] == 'updated' ) {
			$checks = array('open_time','close_time','time_step','closed_day_check','name','address','mail','tel','cal_size');
		}
		else {
			$checks = array('sp_date');
		}

		if (ResourceCalendar_Page::serverCheck($checks,$msg) == false) {
			throw new ResourceCalendarException($msg,__LINE__ );
		}
		if ($_POST['type'] == 'updated' ) {
			//開始時刻と終了時刻は２４時間以内にする
			if (! $this->checkTimeWidth($_POST['rcal_open_time'], $_POST['rcal_close_time']) ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E214', array(__('Open Time',RCAL_DOMAIN),__('Close Time',RCAL_DOMAIN))),1);
			}
			$open_time = str_replace(":","",$_POST['rcal_open_time']);
			$close_time = str_replace(":","",$_POST['rcal_close_time']);

			//時間のチェック
			$edit_holidays = array();
			$_POST["rcal_closed_day"] = "";
			if ( isset($_POST["rcal_closed_day_check"]) && '' != strval($_POST["rcal_closed_day_check"])) {
				$holidays_arr = explode(',',$_POST["rcal_closed_day_check"]);
				$holidays_detail_arr = explode(',',$_POST["rcal_closed_day_detail"]);

				foreach ($holidays_arr as $k1 => $d1 ) {
					$tmp_detail = "";
					if (!empty($holidays_detail_arr[$k1]) ) {
						$tmp_detail_arr = array();
						$tmp_detail_arr = explode(';',str_replace(":","",$holidays_detail_arr[$k1]));
						foreach ($tmp_detail_arr as $k2 => $d2 ) {
							$edit_tmp =  substr("0" . $d2,-4);
							if (! ResourceCalendar_Component::checkTime($edit_tmp)  ) {
								throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E202', __LINE__),1);
							}
							$tmp_detail_arr[$k2] = $edit_tmp;
						}
						if (! $this->checkTimeWidth($tmp_detail_arr[0], $tmp_detail_arr[1]) ) {
							throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E214', array(__(" Detailed time schedule of ",RCAL_DOMAIN),$holidays_detail_arr[$k1])),1);
						}
						//開始時刻と終了時刻の範囲内であること
						if (($open_time <= +$tmp_detail_arr[0])
							&& (+$tmp_detail_arr[1] <= $close_time) ) {

						}
						else {
							throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E214', array(__(" Detailed time schedule of ",RCAL_DOMAIN),$holidays_detail_arr[$k1])),2);
						}
					}
					$edit_holidays[] = $d1 . ';' . implode(';',$tmp_detail_arr);
				}
				$_POST["rcal_closed_day"] = implode(',',$edit_holidays);

			}
		}
	}

	private function checkTimeWidth($from ,$to) {
		$from = str_replace(":","",$from);
		$to = str_replace(":","",$to);
		if ( 2400 <= ($to - $from)   ) {
			return false;
		}
		if ($to <= $from) {
			return false;
		}

		return true;
	}


	public function show_page() {

		$this->table_data['no'] = __($_POST['type'],RCAL_DOMAIN);
		$this->table_data['check'] = '';
		if ($_POST['type'] != 'updated' ) {
// 			$setDate = htmlspecialchars($_POST['rcal_sp_date'],ENT_QUOTES);
// 			$target_date = __('%%m/%%d/%%Y',RCAL_DOMAIN);
// 			$target_date = str_replace('%%Y',substr($setDate,0,4),$target_date);
// 			$target_date = str_replace('%%m',substr($setDate,4,2),$target_date);
// 			$target_date = str_replace('%%d',substr($setDate,6,2),$target_date);
			$target_date = $_POST['rcal_sp_date'];
			$this->table_data['target_date'] = $target_date;
			if  ($_POST['type']	== 'inserted' ) {
				$title = __('Special holiday',RCAL_DOMAIN);
				if ($_POST['rcal_status']==ResourceCalendar_Status::OPEN) $title = __('Business day',RCAL_DOMAIN);
				$this->table_data['status_title'] = $title;
				$this->table_data['status'] = htmlspecialchars($_POST['rcal_status'],ENT_QUOTES);
			}
		}

		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}