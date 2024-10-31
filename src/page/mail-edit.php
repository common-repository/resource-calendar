<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Mail_Edit extends ResourceCalendar_Page {

	private $table_data = null;
	private $default_mail = '';

	public function __construct() {
		parent::__construct();
	}

	public function check_request() {
		if (defined ( 'RCAL_DEMO' ) && RCAL_DEMO ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('I001',null) ,1);
		}
		$msg = null;
		$checks = array("mail_returnPath_on_mail","send_mail_subject","information_mail_subject","send_mail_subject_admin","send_mail_subject_completed","send_mail_subject_accepted","send_mail_subject_canceled");
		if (ResourceCalendar_Page::serverCheck($checks,$msg) == false) {
			throw new ResourceCalendarException($msg,__LINE__ );
		}
		if (isset($_POST['rcal_mail_from']) && !empty($_POST['rcal_mail_from']) ) {
			$reg = '/.+<.+@.+\..+>$/';
			if (preg_match($reg,$_POST['rcal_mail_from'],$matches) == 0 ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E217'),1);
			}
		}

	}


	public function show_page() {
		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}