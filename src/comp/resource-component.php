<?php

class Resource_Component extends ResourceCalendar_Component {


	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editInitData($resource_cd = '') {
		$edit_result = array();

		$result = 	$this->datas->getTargetResourceData($resource_cd);


		foreach ($result as $k1 => $d1 ) {
			//[PHOTO]
			$photo_result = $this->datas->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				$tmp[] = $photo_result[$i];
			}
			$result[$k1]['photo_result'] = $tmp;
			//[PHOTO]

			if (str_replace('/','',$d1['chk_from']) == '00000000' ) $result[$k1]['valid_from'] = '';
			if (str_replace('/','',$d1['chk_to']) == '20991231' ) $result[$k1]['valid_to'] = '';
		}

		return $result;
	}

	public function editTableData () {
		$set_data = array();

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['resource_cd'] = intval($_POST['resource_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['resource_cd'] = intval($_POST['resource_cd']);
				$set_data['display_sequence'] = intval($_POST['display_sequence']);

			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('rcal_resource')+1;
			}
			$set_data['remark'] = stripslashes($_POST['rcal_remark']);
			$set_data['name'] = stripslashes($_POST['rcal_name']);

			$set_data['photo'] = str_replace("photo_id_","",stripslashes($_POST['photo']));
			//既存の情報を表示させた状態で、他の項目を変更して写真はそのままで追加する場合
//			if ($_POST['type'] == 'inserted' && !empty($_POST['used_photo']) ) {
			if ($_POST['type'] == 'inserted' && (isset($_POST['used_photo']) && '' != strval($_POST['used_photo'])) ) {
				$new_photo_id_array = $this->_copyPhotoData($_POST['used_photo']);
				$edit_tmp_array = explode(',',$set_data['photo']);
				for($i = 0 ; $i < count($edit_tmp_array) ; $i++) {
					if (array_key_exists($edit_tmp_array[$i],$new_photo_id_array) ) {

						$edit_tmp_array[$i] = $new_photo_id_array[$edit_tmp_array[$i]];
					}
				}
				$set_data['photo'] = implode(',',$edit_tmp_array);
			}



			$set_data['valid_from'] = '';
			if (!empty($_POST['rcal_valid_from'])) $set_data['valid_from'] = ResourceCalendar_Component::checkAndEditRequestYmdForDb($_POST['rcal_valid_from']);
			$set_data['valid_to'] = '2099/12/31 12:00';
			if (!empty($_POST['rcal_valid_to'])) $set_data['valid_to'] = ResourceCalendar_Component::checkAndEditRequestYmdForDb($_POST['rcal_valid_to']);
			$set_data['max_setting'] = intval($_POST['rcal_max_setting']);

			$set_data['setting_patern_cd'] = intval($_POST['rcal_setting_patern_cd']);
			$set_data['setting_data'] = stripslashes($_POST['setting_data']);
		}
		return $set_data;

	}

	private function _copyPhotoData($ids,$target_width=100,$target_height=100) {

		$new_photo_id_array = array();

		$vals = explode(',',$ids);
		foreach ($vals as  $d1 ) {
			$photo_datas = explode(':',$d1);
			$photo_id =  $photo_datas[0];
			$base_name = $photo_datas[1];
			$attr = substr($base_name, strrpos($base_name, '.') );
			$randam_file_name = substr(md5(uniqid(mt_rand())),0,8).$attr;
			if (!copy(RCAL_UPLOAD_DIR.$base_name,RCAL_UPLOAD_DIR.$randam_file_name) ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),__('PHOTO IMAGE CAN\'T COPY',RCAL_DOMAIN));
			}
			if (!copy(RCAL_UPLOAD_DIR. $target_width."_".$target_height."_".$base_name,RCAL_UPLOAD_DIR. $target_width."_".$target_height."_".$randam_file_name) ) {
				throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),__('PHOTO IMAGE CAN\'T COPY',RCAL_DOMAIN));
			}

			$new_photo_id_array[$photo_id] =	$this->datas->insertPhotoData($photo_id,$randam_file_name);
		}
		return $new_photo_id_array;

	}


	public function editColumnData() {
		$column = array();
		$column[2]="name = %s ";
		$column[3]="valid_from = %s ";
		$column[4]="valid_to = %s ";
		$column[6]="remark = %s ";


		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['resource_cd'] = intval($_POST['resource_cd']);

		return $set_data;
	}

	public function editSeqData() {
		$keys = explode(',',$_POST['resource_cd']);
		$values = explode(',',$_POST['value']);
		$set_data = array($keys[0] => $values[1],$keys[1] => $values[0]);
		return $set_data;
	}


}