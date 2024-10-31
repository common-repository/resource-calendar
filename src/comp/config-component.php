<?php

class Config_Component extends ResourceCalendar_Component {

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editTableData () {

		if ( $_POST['type'] == 'updated' ) {
			$set_data['RCAL_CONFIG_SHOW_DETAIL_MSG'] = empty($_POST['rcal_config_show_detail_msg']) ? ResourceCalendar_Config::DETAIL_MSG_NG : ResourceCalendar_Config::DETAIL_MSG_OK;
			$set_data['RCAL_CONFIG_BEFORE_DAY'] = intval($_POST['rcal_config_before_day']);
			$set_data['RCAL_CONFIG_AFTER_DAY'] = intval($_POST['rcal_config_after_day']);
			$set_data['RCAL_CONFIG_CAL_SIZE'] = intval($_POST['rcal_cal_size']);
			$set_data['RCAL_CONFIG_RESERVE_DEADLINE'] = intval($_POST['rcal_config_reserve_deadline']);

			$set_data['RCAL_CONFIG_OPEN_TIME'] = ResourceCalendar_Component::replaceTimeToDb($_POST['rcal_open_time']);
			$set_data['RCAL_CONFIG_CLOSE_TIME'] = ResourceCalendar_Component::replaceTimeToDb($_POST['rcal_close_time']);
			$set_data['RCAL_CONFIG_TIME_STEP'] = intval($_POST['rcal_time_step']);



			$set_data['RCAL_CONFIG_CONFIRM_STYLE'] = intval($_POST['rcal_confirm_style']);
			$set_data['RCAL_CONFIG_ENABLE_RESERVATION'] = intval($_POST['rcal_enable_reservation']);

			$set_data['RCAL_CONFIG_CLOSED'] = $_POST['rcal_closed_day'];

			$set_data['RCAL_CONFIG_NAME'] =  stripslashes($_POST['rcal_name']);
			$set_data['RCAL_CONFIG_ADDRESS'] =  stripslashes($_POST['rcal_address']);
			$set_data['RCAL_CONFIG_TEL'] =  stripslashes($_POST['rcal_tel']);
			$set_data['RCAL_CONFIG_MAIL'] =  stripslashes($_POST['rcal_mail']);

			$set_data['RCAL_CONFIG_RESOURCE_NAME'] =  stripslashes($_POST['rcal_resource_name']);
			$set_data['RCAL_CONFIG_USE_SESSION_ID'] = empty($_POST['rcal_config_use_session']) ? ResourceCalendar_Config::USE_NO_SESSION : ResourceCalendar_Config::USE_SESSION;
			$set_data['RCAL_CONFIG_USE_SUBMENU'] = empty($_POST['rcal_config_use_submenu']) ? ResourceCalendar_Config::USE_NO_SUBMENU : ResourceCalendar_Config::USE_SUBMENU;
			$set_data['RCAL_CONFIG_REQUIRED'] = stripslashes($_POST['rcal_config_required']);

		}
		else {
// 			$target_date = str_replace('/','',$_POST['rcal_sp_date']);
			$target_date = str_replace('-','',ResourceCalendar_Component::checkAndEditRequestYmdForDb($_POST['rcal_sp_date']));
			$sp_dates =  unserialize(get_option( 'RCAL_SP_DATES'));
			if ($_POST['type']	== 'inserted' ) {
				$sp_dates[substr($target_date,0,4)][$target_date] = intval($_POST['rcal_status']);
			}
			elseif ($_POST['type']	== 'deleted' ) {
				unset($sp_dates[substr($target_date,0,4)][$target_date]);
				if (isset($sp_dates[substr($target_date,0,4)]) && count($sp_dates[substr($target_date,0,4)]) == 0 ) {
					unset($sp_dates[substr($target_date,0,4)]);
				}
			}
			$set_data['RCAL_SP_DATES'] = $sp_dates;

		}
		return $set_data;

	}



}