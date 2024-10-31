<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Confirm_Edit extends ResourceCalendar_Page {

	private $table_data = null;

	private $reservation_cd = '';
	private $activation_key = '';

	private $datas = null;

	private $error_msg = '';




	public function __construct() {
		parent::__construct(false);
		$this->reservation_cd = intval($_POST['target']);
		$this->activation_key = $_POST['P2'];
	}


	public function get_reservation_cd () {
		return $this->reservation_cd;
	}
	public function set_reservation_datas ( $datas ) {
		$this->datas = $datas;
	}




	public function check_request() {
		$nonce = RCAL_PLUGIN_DIR;
		if ($this->config_datas['RCAL_CONFIG_USE_SESSION_ID'] == ResourceCalendar_Config::USE_SESSION) $nonce = session_id();
		if (wp_verify_nonce($_POST['nonce'],$nonce) === false) {

			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E021',basename(__FILE__).':'.__LINE__),1 );
		}
		if ( !isset($_POST['target']) || '' == strval($_POST['target']) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( $_POST['type'] !== 'exec' && $_POST['type'] !== 'cancel' )  {
			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( count($this->datas) == 0   ) {
			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( $this->datas['activate_key'] !== $this->activation_key ) {
			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E909',basename(__FILE__).':'.__LINE__),1 );
		}
		$now =  date_i18n("YmdHi");
		if ($this->datas['check_day'] < $now )  {
			throw new ResourceCalendarException(ResourceCalendar_Component	::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']));
		}

	}



	public function show_page() {
		$status = array();
		if ($_POST['type'] == 'exec' ) $result = array('status_name'=>__('reservation completed',RCAL_DOMAIN));
		else $result = array('status_name'=>__('reservation deleted',RCAL_DOMAIN));

		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component	::getMsg('N001').'",
				"set_data":'.json_encode($result).' }';
	}


}