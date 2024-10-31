<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'control/resource-calendar-control.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'data/config-data.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'comp/config-component.php');

class Config_Control extends ResourceCalendar_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Config_Page';
			$this->set_response_type(ResourceCalendar_Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Config_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Config_Component($this->datas);
		$this->permits = array('Config_Page','Config_Init','Config_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class();
		$this->pages->set_config_datas($this->config);


		if ($this->action_class == 'Config_Page' ) {
		}
		elseif ($this->action_class == 'Config_Init' ) {
			$target_year = $this->pages->get_target_year();
			$this->pages->set_init_datas($this->datas->getAllSpDateData($target_year));
		}
		elseif ($this->action_class == 'Config_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);

			if ( ($_POST['type'] == 'inserted' ) || ($_POST['type'] == 'deleted' ) ) {
				$this->datas->updateSpDate( $res );
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->update( $res);
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Config_Page') wp_die();
	}
}		//class


