<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'control/resource-calendar-control.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'data/resource-data.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'comp/resource-component.php');

class Resource_Control extends ResourceCalendar_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Resource_Page';
			$this->set_response_type(ResourceCalendar_Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Resource_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Resource_Component($this->datas);
		$this->permits = array('Resource_Page','Resource_Init','Resource_Edit','Resource_Col_Edit','Resource_Seq_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class();
		$this->pages->set_config_datas($this->config);
		if ($this->action_class == 'Resource_Page' ) {
			$this->pages->set_setting_patern_datas($this->datas->getSettingPaternDatas());
		}
		elseif ($this->action_class == 'Resource_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitData());
		}
		elseif ($this->action_class == 'Resource_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);

			if ($_POST['type'] == 'inserted' ) {
				$this->datas->fixedPhoto($_POST['type'],$res['photo']);
				$res['resource_cd'] = $this->datas->insertTable( $res);
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateResourcePhotoData($res['resource_cd'],$res['photo']);
				$this->datas->updateTable( $res);
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteResourcePhotoData($res['resource_cd']);
				$this->datas->deleteTable( $res);
			}
			if ($_POST['type'] != 'deleted' ) {
				$reRead = $this->comp->editInitData($res['resource_cd']);
				$this->pages->set_table_data($reRead[0]);
			}
		}
		elseif ($this->action_class == 'Resource_Col_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editColumnData();
			$this->datas->updateColumn($res);
			$this->pages->set_table_data($res);
		}
		elseif ($this->action_class == 'Resource_Seq_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editSeqData();
			$this->pages->set_table_data($res);
			$this->datas->updateSeq($res,'resource','resource_cd');
		}

		$this->pages->show_page();
		if ($this->action_class != 'Resource_Page' ) wp_die();
	}
}		//class


