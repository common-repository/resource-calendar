<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Photo_Edit extends ResourceCalendar_Page {


	private $photo_id = null;
	private $resize_file_path = null;


	function __construct() {
		parent::__construct();
	}

	public function check_request() {
		if (empty($_REQUEST['type'])) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		//nonceのチェックのみ
		if (ResourceCalendar_Page::serverCheck(array(),$msg) == false) {
			throw new ResourceCalendarException($msg,__LINE__ );
		}
		if	( ($_REQUEST['type'] == 'deleted' ) && ( !isset($_POST['photo_id']) || '' == strval($_POST['photo_id']) ) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3 );
		}
		$msg = null;
		if ($_REQUEST['type'] == 'inserted' ) {
			//ファイル名などのチェック。不正に対するチェックなので$_FILESはみないで直接ファイルをチェックする
			$attr = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
			if ($attr == 'jpg' || $attr == 'png' ||$attr == 'gif'){}
			else {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E904',__('FILE TYPE ERROR(1)',RCAL_DOMAIN)),4);
			}
			if (filesize( $_FILES['file']['tmp_name']) > RCAL_MAX_FILE_SIZE * 1000 * 1000) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E904',__('FILE MAX SIZE ERROR('.RCAL_MAX_FILE_SIZE.'M)',RCAL_DOMAIN)),6);
			}
			try {
				$size = getimagesize( $_FILES['file']['tmp_name']);
			} catch (Exception $e) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E904',__('FILE TYPE ERROR(2)',RCAL_DOMAIN)),5);
				}
			//拡張子だけを変更するといきなりおちる場合がある？
			if ($size !== false && ($size[2] == IMAGETYPE_JPEG || $size[2] == IMAGETYPE_PNG || $size[2] != IMAGETYPE_GIF)) {}
			else {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E904',__('FILE TYPE ERROR(3)',RCAL_DOMAIN)),5);
			}
		}
	}

	public function set_photo_id($photo_id) {
		$this->photo_id = $photo_id;
	}
	public function set_resize_file_path($resize_file_path) {
		$this->resize_file_path = $resize_file_path;

	}

	public function show_page() {
		if ($_REQUEST['type'] == 'deleted')
			echo '{	"status":"Ok" }';
		else
			echo '{	"status":"Ok","photo_id":"'.$this->photo_id.'","resize_path":"'.$this->resize_file_path.'"}';
	}	//show_page
}		//class

