<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Category_Edit extends ResourceCalendar_Page {

	private $table_data = null;


	function __construct() {
		parent::__construct();
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function set_category_cd($category_cd) {
		 $this->table_data['category_cd'] = $category_cd;
	}

// 	public function get_category_cd() {
// 		return $this->table_data['category_cd'];
// 	}


	public function check_request() {
		if (empty($_POST['type'])) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		if	( ($_POST['type'] != 'inserted' ) && ( !isset($_POST['rcal_category_cd']) || '' == strval($_POST['rcal_category_cd']) ) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
					}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (!isset($_POST['rcal_category_patern'])) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
			}
			$checkItem = array('category_name','category_patern');
			if ($_POST['rcal_category_patern'] != ResourceCalendar_Category::TEXT
				&& $_POST['rcal_category_patern'] != ResourceCalendar_Category::MAIL) {
				$checkItem[] = 'category_value';
			}
			if (ResourceCalendar_Page::serverCheck($checkItem,$msg) == false) {
				throw new ResourceCalendarException($msg ,__LINE__);
			}
		}
	}

	public function show_page() {

		$this->table_data['no'] = __($_POST['type'],RCAL_DOMAIN);
		$this->table_data['check'] = '';


		if ( $_POST['type'] != 'deleted' ) {

			$this->table_data['rcal_category_cd'] = $this->table_data['category_cd'];
			$this->table_data['rcal_category_patern'] = $this->table_data['category_patern'];
			$this->table_data['rcal_category_name'] = htmlspecialchars($this->table_data['category_name']);
			$this->table_data['rcal_category_values'] = htmlspecialchars($this->table_data['category_values']);
			$this->table_data['rcal_display_sequence'] = $this->table_data['display_sequence'];
			$this->table_data['rcal_remark'] = '';
			unset ($this->table_data['category_cd'] );
			unset ($this->table_data['category_patern'] );
			unset ($this->table_data['category_name'] );
			unset ($this->table_data['category_values'] );
			unset ($this->table_data['display_sequence'] );
		}


		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}