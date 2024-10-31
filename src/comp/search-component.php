<?php

class Search_Component extends ResourceCalendar_Component {
	
	
	private $file_name = '';
	private $csv_data = null;
	
	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function setSearchCustomerData($keys) {
		$datas = $this->datas->getSearchCustomerData($keys);
		$button = '<input type="button" value="'.__('close',RCAL_DOMAIN).'" onclick="fnRemoveModalResult(this);" class="rcal_button"/>';
		$html = "";
		if (count($datas) == 0 ) {
			return false;
		}
		$html .= '<div id="rcal_search_result_head"><ol>'
					.'<li>'.__('No',RCAL_DOMAIN).'</li>'
					.'<li>'.__('name',RCAL_DOMAIN).'</li>'
					.'<li>'.__('tel',RCAL_DOMAIN).'</li>'
					.'<li>'.__('mobile',RCAL_DOMAIN).'</li>'
					.'<li>'.__('mail',RCAL_DOMAIN).'</li>'
				.'</ol></div>';
		$cnt = 0;
		$html .= '<div id="rcal_search_result_data">';
		global $wpdb;
		foreach ($datas as $k1 => $d1 ) {
			$is_exist = false;
			if (is_multisite() ) {
				if (!isset($d1[$wpdb->prefix.'capabilities']) ) {
					continue;
				}
			}
			if (strstr($d1[$wpdb->prefix.'capabilities'],'subscriber') ) {
				$tr = '';
				if (strcasecmp(get_locale(), "ja") == 0 ) {
					if ( $this->_setSearchCustomerDataEditTr($keys['name'],$d1['last_name'].' '.$d1['first_name'],$tr) ) $is_exist = true;
				}
				else {
					if ( $this->_setSearchCustomerDataEditTr($keys['name'],$d1['first_name'].' '.$d1['last_name'],$tr) ) $is_exist = true;
				}
				if ( isset($d1['tel'])  ) {
					if ( $this->_setSearchCustomerDataEditTr($keys['tel'],$d1['tel'],$tr) ) $is_exist = true;
				}
				else {
					$this->_setSearchCustomerDataEditTr($keys['tel'],'',$tr );
				}
				if ( isset($d1['mobile'])  ) {
					if ( $this->_setSearchCustomerDataEditTr($keys['tel'],$d1['mobile'],$tr) ) $is_exist = true;
				}
				else {
					$this->_setSearchCustomerDataEditTr($keys['tel'],'',$tr );
				}
				if ( $this->_setSearchCustomerDataEditTr($keys['mail'],$d1['mail'],$tr) ) $is_exist = true;
				if ($is_exist) {
					$cnt++;
					$html .= '<dl><dt><dtf>'.$cnt.'</dtf></dt>'.$tr.'<input type="hidden" value="'.$d1['user_login'].'" /></dl>';
				}
			}
		}
		$html .= '</div>';
		if ($cnt == 0) {
			return false;
		}
		else  {
			$html = $button.$html.$button;
		}
		return $html;
	}
	
	private function _setSearchCustomerDataEditTr($key,$data,&$tr) {
		if (isset($data) && empty($data) ) {
			$tr .='<dd ></dd>';
			return false;
		}

		if ($data && $key && strpos($data,$key) !== false ) {
			$tr .='<dd class="rcal_search_display">'.htmlspecialchars($data,ENT_QUOTES).'</dd>';
			return true;
		}
		else {
			$tr .='<dd >'.htmlspecialchars($data,ENT_QUOTES).'</dd>';
			return false;
		}
	}
}
