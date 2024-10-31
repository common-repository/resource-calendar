<?php

class Category_Component extends ResourceCalendar_Component {

	public function __construct(&$datas) {
		$this->datas = $datas;
	}



	public function editTableData () {

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['category_cd'] = intval($_POST['rcal_category_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['category_cd'] = intval($_POST['rcal_category_cd']);
				$set_data['display_sequence'] = intval($_POST['rcal_display_sequence']);
			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('rcal_category')+1;
			}

			$set_data['category_name'] = stripslashes($_POST['rcal_category_name']);
			$set_data['category_patern'] =  intval($_POST['rcal_category_patern']);
			$set_data['category_values'] =  stripslashes($_POST['rcal_category_value']);

		}
		return $set_data;

	}

	public function serverCheck($set_data) {

		if ($_POST['type'] != 'deleted') {
			if ($set_data['category_patern'] == ResourceCalendar_Category::MAIL) {
				$mailCategory = $this->datas->getCategoryDatas(" AND  category_patern = ".ResourceCalendar_Category::MAIL);
				if (count($mailCategory) > 0 ) {
					if (($_POST['type'] == 'inserted' )
					|| ($_POST['type'] == 'updated'
							&& $set_data['category_cd'] != $mailCategory[0]['category_cd']  )) {
						throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E218'),2);
					}
				}
			}
		}
	}


	public function editSeqData() {
		$keys = explode(',',$_POST['rcal_category_cd']);
		$values = explode(',',$_POST['value']);
		$set_data = array($keys[0] => $values[1],$keys[1] => $values[0]);
		return $set_data;
	}

}