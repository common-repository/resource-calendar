<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'control/resource-calendar-control.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'data/reservation-data.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'comp/reservation-component.php');

class Reservation_Control extends ResourceCalendar_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Reservation_Page';
// 			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Reservation_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Reservation_Component($this->datas);
		$this->permits = array('Reservation_Page','Reservation_Init','Reservation_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class();
		$this->pages->set_config_datas($this->config);


		if ($this->action_class == 'Reservation_Page' ) {
			$this->pages->set_resource_datas($this->datas->getTargetResourceData());
			if ($this->config['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
				$this->pages->set_category_datas($this->datas->getCategoryDatas());
			}

		}
		elseif ($this->action_class == 'Reservation_Init' ) {
			$this->pages->set_init_datas($this->datas->getAllEventDataFromAdmin());
		}

		$this->pages->show_page();
		if ($this->action_class != 'Reservation_Page' ) wp_die();

	}
}		//class
