<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'control/resource-calendar-control.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'data/photo-data.php');
	require_once(RCAL_PLUGIN_SRC_DIR . 'comp/photo-component.php');

class Photo_Control extends ResourceCalendar_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = '';


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = '';
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Photo_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Photo_Component($this->datas);
		$this->permits = array('Photo_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class(true);
		$this->pages->set_config_datas($this->config);
		if ($this->action_class == 'Photo_Edit' ) {
			$this->pages->check_request();
			if ($_REQUEST['type'] == 'inserted' ) {

				$set_file_name = $this->comp->moveFile();
				if ($set_file_name === false) {
					throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E904','MOVE FILE ERROR') ,__LINE__);
				}
				$set_resize_file_name = $this->comp->resizeFile($set_file_name);
				$res = $this->comp->editTableData($set_file_name,$set_resize_file_name);
				$this->pages->set_resize_file_path($set_resize_file_name);
				$this->pages->set_photo_id($this->datas->insertTable( $res));
			}
			if ($_REQUEST['type'] == 'deleted' ) {
				$res = $this->comp->deletePhotoData();
				$this->datas->deleteTable( $res);
			}
		}

		$this->pages->show_page();
		wp_die();
	}
}		//class

