<?php

class Confirm_Component extends ResourceCalendar_Component {
	
	private $version = '1.0';
	

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	

	function editTargetReservationData($reservation_cd) {	
		$result = $this->datas->getTargetReservationData($reservation_cd);
		if ( count($result) == 0 ) return array();
		
//		if ($result[0]['status'] == ResourceCalendar_Reservation_Status::COMPLETE) $result[0]['status_name'] = __('reservation completed',RCAL_DOMAIN);
//		elseif ($result[0]['status'] == ResourceCalendar_Reservation_Status::TEMPORARY) $result[0]['status_name'] = __('reservation temporary',RCAL_DOMAIN);
//		elseif ($result[0]['status'] == ResourceCalendar_Reservation_Status::CANCELED) $result[0]['status_name'] = __('reservation canceled',RCAL_DOMAIN);
//		else $result[0]['status_name'] = __('no status',RCAL_DOMAIN);
		
		$result[0]['status_name'] = $this->_setStatus($result[0]['status'] );
		
		return $result[0];
	}
	

	
	public function editTableData (&$reservation_data) {
		//メールの内容設定でも使用するのでreservation_dataも更新する。		
		$set_data['reservation_cd'] = $reservation_data['reservation_cd'];
		if ( $_POST['type'] == 'cancel' ) {
			$set_data['status'] = ResourceCalendar_Reservation_Status::CANCELED;
		}
		elseif  ( $_POST['type'] == 'exec' ){
			$set_data['status'] = ResourceCalendar_Reservation_Status::COMPLETE;
		}
		$reservation_data['status'] = $set_data['status'];
		$reservation_data['status_name'] = $this->_setStatus($set_data['status']);
		return $set_data;
		
	}

	function _setStatus($status) {
		if ($status == ResourceCalendar_Reservation_Status::COMPLETE) return __('reservation completed',RCAL_DOMAIN);
		elseif ($status == ResourceCalendar_Reservation_Status::TEMPORARY) return __('reservation temporary',RCAL_DOMAIN);
		elseif ($status == ResourceCalendar_Reservation_Status::CANCELED) return __('reservation canceled',RCAL_DOMAIN);
		
		return __('no status',RCAL_DOMAIN);
	}
	
}