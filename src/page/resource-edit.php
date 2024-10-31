<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Resource_Edit extends ResourceCalendar_Page {

	private $table_data = null;

	public function __construct() {
		parent::__construct();
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}



// 	public function set_resource_cd($resource_cd) {
// 		$this->table_data['resource_cd'] = $resource_cd;
// 	}

	public function editRequestYmd($in,$setValueWhenEmpty = "") {
		if (empty($in) ) {
			return $setValueWhenEmpty;
		}
		if (preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',RCAL_DOMAIN).'$/',$in,$matches) == 0 )
		   preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',RCAL_DOMAIN).'$/',$in,$matches);
		return sprintf("%4d%02d%02d",+$matches['year'],+$matches['month'],+$matches['day']);
	}

	public function check_request() {

		if (empty($_POST['type'])) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}

		if	( ($_POST['type'] != 'inserted' ) && ( !isset($_POST['resource_cd']) || '' == strval($_POST['resource_cd']) ) ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (ResourceCalendar_Page::serverCheck(array('name','valid_from','valid_to','remark','setting_patern_cd'),$msg) == false) {
				throw new ResourceCalendarException($msg,__LINE__ );
			}

			if (+$this->editRequestYmd($_POST['rcal_valid_from'],'00000000') > +$this->editRequestYmd($_POST['rcal_valid_to'],'20991231') ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E215' ) ,__LINE__ );
			}

			if ($_POST['rcal_setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL ) {

				$time_array = array();

				$set_array = explode(';',$_POST['setting_data']);
				foreach ($set_array as $k1 => $d1 ) {
					$each_data = explode(',',$d1);
					$from = +str_replace(":","",$each_data[1]);
					$to = +str_replace(":","",$each_data[2]);
					if ($from >= $to ) {
						throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E214',array(__('from >= to ',RCAL_DOMAIN),$d1) ) ,__LINE__ );
					}

					if ( $from  < +$this->config_datas['RCAL_CONFIG_OPEN_TIME']
					  || +$this->config_datas['RCAL_CONFIG_CLOSE_TIME'] < $from
					  || $to  < +$this->config_datas['RCAL_CONFIG_OPEN_TIME']
					  || +$this->config_datas['RCAL_CONFIG_CLOSE_TIME'] < $to ) {
						throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E302'),__LINE__);
					}


					foreach ($time_array as $k2 => $d2 ) {
						if (($to <=$d2['from']) || ($d2['to'] <= $from)) {
						}
						else {
							throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E214',array(__('overlaped time ',RCAL_DOMAIN),$d1) ) ,__LINE__ );
						}
					}
					$time_array[] = array('from' => $from ,'to' => $to );

				}

			}



		}
	}

	public function show_page() {
		$res = array();
		$res['no'] = __($_POST['type'],RCAL_DOMAIN);
		$res['check'] = '';
		$res['resource_cd'] = $this->table_data['resource_cd'];
		if ( $_POST['type'] == 'deleted' ) {
			$res['resource_cd'] ='';

		}
		else {
			$res['rcal_name'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);
			$res['photo'] = $this->table_data['photo'];
			$res['photo_result'] = $this->table_data['photo_result'];
			$res['rcal_valid_from'] = $this->table_data['valid_from'];
			$res['rcal_valid_to'] = $this->table_data['valid_to'];
			$res['rcal_display_sequence'] = $this->table_data['display_sequence'];
			$res['rcal_remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
			$res['rcal_setting_patern_cd'] = $this->table_data['setting_patern_cd'];
			$res['rcal_setting_data'] = htmlspecialchars($this->table_data['setting_data'],ENT_QUOTES);
		}
		echo '{	"status":"Ok","message":"'.ResourceCalendar_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}