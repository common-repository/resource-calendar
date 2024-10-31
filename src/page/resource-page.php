<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Resource_Page extends ResourceCalendar_Page {

	private $set_items = null;
	private $setting_patern_datas = null;


	const SEQ_COLUMN = 5;

	function __construct() {
		parent::__construct();
		$this->set_items = array('name','valid_from','valid_to','remark','max_setting','setting_patern_cd','original_name');

	}

	public function set_setting_patern_datas($datas) {
		$this->setting_patern_datas = $datas;
	}

/*
	private function echo_script_tag() {
?>
<script type="text/javascript">
		var $j = jQuery
		var target;
		var save_k1 = "";
		<?php //登録または削除しているのに、確定していないかを判断 ?>
		var insert_photo = false;
		var delete_photo = false;
		var insert_photo_ids = Array();
<?php
	}
*/

	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		var target;
		var save_k1 = "";
		<?php //登録または削除しているのに、確定していないかを判断 ?>
		var insert_photo = false;
		var delete_photo = false;
		var insert_photo_ids = Array();
		var save_result_select_id = "";

		function delete_photo_datas() {
			<?php //写真を登録したけどやめてしまった場合 ?>
			$j.ajax({
				type: "post",
				url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalphoto",
				dataType : "json",
					data: {
						"photo_id":insert_photo_ids.join(","),
						"type":"deleted",
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Photo_Edit"
					},
				success: function(data) {
					if (data === null || data.status == "Error" ) {
						alert(data.message);
					}
				},
				error:  function(XMLHttpRequest, textStatus){
					alert (textStatus);
				}
			 });
		}

		<?php parent::echoClientItem($this->set_items);  ?>
		<?php parent::set_datepicker_date(); ?>

		$j(document).ready(function() {

<?php //［PHOTO]ここから ?>
			Dropzone.autoDiscover = false;
			$j("#rcal_image_drop_area").dropzone({
				url: "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalphoto&menu_func=Photo_Edit&type=inserted&nonce=<?php echo $this->nonce; ?>"
				,maxFilesize:<?php echo RCAL_MAX_FILE_SIZE; ?>
				,init: function() {
					$j(this.element).addClass("dropzone");

					this.on("addedfile",function(file) {
						$j(file.previewElement).addClass("ui-state-default");
					});
					this.on("success", function(file, text) {
						var res = JSON.parse(text);
						if (res.status == "Ok" ) {
							$j(file.previewElement).attr("id","photo_id_"+res.photo_id);
							$j(".lightbox").colorbox({rel:"resource",width:"<?php echo RCAL_COLORBOX_SIZE; ?>", height:"<?php echo RCAL_COLORBOX_SIZE; ?>"});
							insert_photo = true;
							insert_photo_ids.push($j(file.previewElement).attr("id"));
						}
						else {
							alert(res.message);
						}
					});
					this.on("removedfile",function(file) {
		<?php 			//実際の削除はUPDATEまたはDELETEときに行う ?>
							delete_photo = true;
					});
				}
				,accept: function(file, done) {
					if(file.name.match(/\.(jpg|png|gif)$/i))  {
						done();
					}
					else {
						this.removeFile(file);
						alert("<?php _e('FILE TYPE ERROR',RCAL_DOMAIN); ?>");
					}
				}
				,error: function(file, message) {
					this.removeFile(file);
					alert(message);
				}
				,addRemoveLinks:true
				,dictDefaultMessage: "<?php _e('Drop files here to upload',RCAL_DOMAIN); ?>"
				,dictFileTooBig: "<?php _e('File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.',RCAL_DOMAIN); ?>"
				,dictInvalidFileType: "<?php _e('You can\'t upload files of this type.',RCAL_DOMAIN); ?>"
				,dictRemoveFile:"<?php _e('Delete',RCAL_DOMAIN) ?>"
				,dictFallbackMessage:"<?php _e('Your browser does not support drag&drop file uploads.',RCAL_DOMAIN); ?>"
				,dictMaxFilesExceeded: "<?php _e('You can only upload {{maxFiles}} files.',RCAL_DOMAIN); ?>"

			});
			$j("#rcal_image_drop_area").sortable();
<?php  //ここまで ?>
			$j("#rcal_setting_patern_cd").change(function(){
				save_result_select_id = "";
				$j("#rcal_original_result").children().remove();

				if ($j(this).val() == <?php echo ResourceCalendar_Config::SETTING_PATERN_TIME; ?> ) {
					$j("#rcal_setting_data_wrap").hide();
					$j("#rcal_original_name").val("");
					$j("#rcal_original_from").val("");
					$j("#rcal_original_to").val("");
				}
				else {
					$j("#rcal_setting_data_wrap").show();
				}
			});

			$j("#rcal_original_add").click(function(){
				if ($j("#rcal_original_name").val() && $j("#rcal_original_from").val() && $j("#rcal_original_to").val() ) {
					var from = +$j("#rcal_original_from").val().replace(":","");
					var to = +$j("#rcal_original_to").val().replace(":","");
					if (from < to )  {

						var last = $j("#rcal_original_result div:last-child");
						var id = 1;
						if (last[0]) {
							var tmp_id = last.attr("id").split("rcal_res_each_");
							id = +tmp_id[1]+1;
						}
						_fnEditSetting(id,"add");
					}
				}

			});
			$j("#rcal_original_upd").click(function(){
				if (save_result_select_id == "") return;
				_fnEditSetting(save_result_select_id,"upd");

			});

			<?php parent::echoSetItemLabel(); ?>
			<?php  parent::set_datepickerDefault(); ?>
			<?php  parent::set_datepicker("rcal_valid_from",true); ?>
			<?php  parent::set_datepicker("rcal_valid_to",true); ?>



			<?php parent::echoCommonButton();			//共通ボタン	?>


			target = $j("#rcal_lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalresource",
				<?php parent::echoDataTableLang(); ?>
				<?php
					parent::echoTableItem(array('name','valid_from','valid_to','display_sequence','remark'),false,"180px");
				?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Resource_Init" } )
				},
				"fnDrawCallback": function () {
					<?php		parent::echoEditableCommon("resource"); 					?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("rcal_name"); ?>
					<?php parent::echoDataTableDisplaySequence(self::SEQ_COLUMN,"rcal_");
					?>
				}

			});




		});

		function _fnEditSetting(id,func) {
			var set_data = $j("#rcal_original_name").val() + ":" + $j("#rcal_original_from").val() + "-" + 	$j("#rcal_original_to").val();

			var setcn = '<div id="rcal_res_each_'+id+'" ><span class="rcal_in_span">'+set_data+'</span><input type="button" class="rcal_in_button rcal_button rcal_button_short rcal_short_width_no_margin" value="<?php _e('Select',RCAL_DOMAIN); ?>" id="rcal_res_each_sel_'+id+'"/><input  type="button"  class="rcal_in_button rcal_button rcal_button_short rcal_short_width_no_margin" value="<?php _e('Delete ',RCAL_DOMAIN); ?>"id="rcal_res_each_del_'+id+'"/><input type="hidden" id="rcal_res_each_name_'+id+'" value="'+$j("#rcal_original_name").val()+'" /><input type="hidden" id="rcal_res_each_from_'+id+'" value="'+$j("#rcal_original_from").val()+'" /><input type="hidden" id="rcal_res_each_to_'+id+'" value="'+$j("#rcal_original_to").val()+'" /></div>';
			if (func == "add" )
				$j("#rcal_original_result").append(setcn);
			else
				$j("#rcal_res_each_"+save_result_select_id).replaceWith(setcn);

			$j("#rcal_res_each_sel_"+id).click(function () {
				$j("#rcal_original_name").val($j("#rcal_res_each_name_"+id).val());
				$j("#rcal_original_from").val($j("#rcal_res_each_from_"+id).val());
				$j("#rcal_original_to").val($j("#rcal_res_each_to_"+id).val());
				save_result_select_id = id;
			});
			$j("#rcal_res_each_del_"+id).click(function () {
				$j(this).parent().remove();
				save_result_select_id = "";

			});
			$j("#rcal_original_name").val("");
			$j("#rcal_original_from").val("");
			$j("#rcal_original_to").val("");
			save_result_select_id = "";
		}

		<?php parent::echoDataTableSeqUpdateRow("resource","resource_cd","rcal_"); ?>
		function fnSelectRow(target_col) {

			if (insert_photo || delete_photo ) {
				if (confirm("<?php _e('Photo data has been uploaded or deleted, but no addition/update has been conducted. Do you want to continue?',RCAL_DOMAIN); ?>") ) {
					<?php //削除は実際に更新していないのでメッセージのみ ?>
					if (insert_photo) 	delete_photo_datas();
				}
				else return;
			}
			insert_photo_ids.length = 0;
			insert_photo = false;
			delete_photo = false;

			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];
			$j("#rcal_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_name']));
			$j("#rcal_valid_from").val(setData['aoData'][position[0]]['_aData']['rcal_valid_from']);
			$j("#rcal_valid_to").val(setData['aoData'][position[0]]['_aData']['rcal_valid_to']);
			$j("#rcal_remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_remark']));
			$j("#rcal_max_setting").val(setData['aoData'][position[0]]['_aData']['rcal_max_setting']);


			$j("#rcal_button_update").removeAttr("disabled");
			$j("#rcal_button_clear").show();

			$j("#rcal_data_detail").show();
			$j("#rcal_button_detail").val("<?php _e('Hide Details',RCAL_DOMAIN); ?>");

			var size = setData['aoData'][position[0]]['_aData']['photo_result'].length;
			for(var i = 0; i < size ; i++ ) {
				var mockFile = Array();
				mockFile['name'] = setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_name'];
				mockFile['size'] = 0;	<?php //表示しないようにCSSでdisplay:noneにしている ?>
				mockFile['photo_id'] = "photo_id_" + setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_id'];
				$j("#rcal_image_drop_area")[0].dropzone.options.addedfile.call($j("#rcal_image_drop_area")[0].dropzone, mockFile);
				$j("#rcal_image_drop_area")[0].dropzone.options.thumbnail.call($j("#rcal_image_drop_area")[0].dropzone, mockFile,setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_resize_path'],setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_path']);

			}


			$j("#rcal_setting_patern_cd").val(setData['aoData'][position[0]]['_aData']['rcal_setting_patern_cd']).change();

			if (setData['aoData'][position[0]]['_aData']['rcal_setting_patern_cd'] ==  <?php echo ResourceCalendar_Config::SETTING_PATERN_ORIGINAL; ?> ) {
				var setting_array = setData['aoData'][position[0]]['_aData']['rcal_setting_data'].split(";");
				for(var i = 0 , max_loop = setting_array.length; i<max_loop;i++){
					var setting_item_array = setting_array[i].split(",");
					$j("#rcal_original_name").val(setting_item_array[0]);
					$j("#rcal_original_from").val(setting_item_array[1]);
					$j("#rcal_original_to").val(setting_item_array[2]);
					_fnEditSetting(i+1,"add");
				}
			}
			$j("#rcal_original_name").val("");
			$j("#rcal_original_from").val("");
			$j("#rcal_original_to").val("");


			$j(".lightbox").colorbox({rel:"resource",width:"<?php echo RCAL_COLORBOX_SIZE; ?>", height:"<?php echo RCAL_COLORBOX_SIZE; ?>"});


		}

		<?php parent::echoDataTableEditColumn("resource"); ?>
		<?php parent::echoDataTableDeleteRow("resource"); ?>
		<?php parent::echoTextDateReplace(); ?>
		<?php parent::echoDayFormat(); ?>


		function fnClickAddRow(operate) {
			var except_item = "rcal_original_name,rcal_original_from,rcal_original_to,rcal_max_setting"
			if ( ! checkItem("rcal_data_detail",except_item) ) return false;
/*
			var valid_from = _fnTextDateReplace( $j("#rcal_valid_from").val() );
			if ( valid_from === false ) return;
			var valid_to = _fnTextDateReplace( $j("#rcal_valid_to").val() );
			if ( valid_to === false ) return;

			var set_from = [valid_from.slice(0,4 ),valid_from.slice(4,6 ),valid_from.slice(6,8 )].join( "-" );
			var set_to = [valid_to.slice(0,4 ),valid_to.slice(4,6 ),valid_to.slice(6,8 )].join( "-" ) ;
*/
			set_from = $j("#rcal_valid_from").val();
			set_to = $j("#rcal_valid_to").val();

			var resource_cd = "";

			var photo_id_array = [];

			<?php //photo ?>
			$j(".dz-preview").each(function() {
				var id = $j(this).attr('id');
				photo_id_array.push(id);
				<?php //インサート時に既存の写真を使用しているかどうかをチェックする ?>
				<?php //既存の写真の場合はコピーする ?>
			});
			<?php //photo ?>

			var photo = photo_id_array.join(",");

			var display_sequence = 0;
			var used_photo_id_array = [];
			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				resource_cd = setData['aoData'][save_k1]['_aData']['resource_cd'];
				display_sequence = setData['aoData'][save_k1]['_aData']['rcal_display_sequence'];
				<?php //photo ?>
				for(var i = 0 ; i < photo_id_array.length ; i++ ) {
					for(var j = 0; j < setData['aoData'][save_k1]['_aData']['photo_result'].length ; j++ ) {
						if (photo_id_array[i] == "photo_id_" + setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_id']) {
								used_photo_id_array.push(setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_id'] + ":" +
											_getFileName(setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_path']));
							break;
						}
					}
				}
				<?php //photo ?>
			}
			var used_photo = used_photo_id_array.join(",");

			var setting_data = "";
			if ($j("#rcal_setting_patern_cd").val() == <?php echo ResourceCalendar_Config::SETTING_PATERN_ORIGINAL; ?> ) {
				var setting_data_array = Array();
				$j("#rcal_original_result").children().each(function (){
					var tmp_id = $j(this).attr("id").split("rcal_res_each_");
					setting_data_array.push($j("#rcal_res_each_name_"+tmp_id[1]).val()+','+$j("#rcal_res_each_from_"+tmp_id[1]).val()+','+$j("#rcal_res_each_to_"+tmp_id[1]).val());
				});
				if (setting_data_array.length == 0 ) {
					alert("<?php _e('Original select data is empty',RCAL_DOMAIN); ?>");
					return;
				}
				setting_data = setting_data_array.join(";");
			}


			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalresource",
					dataType : "json",
					data: {
						"resource_cd":resource_cd,
						"no":save_k1,
						"type":operate,
						"rcal_name":$j("#rcal_name").val(),
						"rcal_remark":$j("#rcal_remark").val(),
						"photo":photo,
						"used_photo":used_photo,
						"rcal_valid_from":set_from,
						"rcal_valid_to":set_to,
						"rcal_max_setting":1,
						"rcal_setting_patern_cd":$j("#rcal_setting_patern_cd").val(),
						"setting_data":setting_data,
						"menu_func":"Resource_Edit",
						"nonce":"<?php echo $this->nonce; ?>",
						"display_sequence":display_sequence
					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if ( (operate =="inserted")  ) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}

							insert_photo_ids.length = 0;
							insert_photo = false;
							delete_photo = false;

							fnDetailInit();
							$j(target.fnSettings().aoData).each(function (){
								$j(this.nTr).removeClass("row_selected");
							});
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert ('<?php echo ResourceCalendar_Component::getMsg('E401'); ?>['+textStatus+']');
					}
			 });
		}


		function fnDetailInit() {
			$j("#rcal_data_detail input[type=\"text\"]").val("");
			$j("#rcal_data_detail select").val("");
			$j("#rcal_data_detail textarea").val("");

			$j("#rcal_button_update").attr("disabled", true);
			$j("#rcal_button_insert").attr("disabled", false);

			$j("#rcal_max_setting").val("1");
			$j("#rcal_setting_patern_cd").val("<?php echo ResourceCalendar_Config::SETTING_PATERN_TIME; ?>");
			$j("#rcal_setting_data_wrap").hide();

			fnPhotoClear();
			save_k1 = "";
			save_result_select_id = "";
			$j("#rcal_original_result").children().remove();

			$j("#rcal_max_setting_wrap").hide();

			<?php parent::echo_clear_error(); ?>

		}


		function fnPhotoClear (){
			$j("#rcal_image_drop_area").empty();
			$j("#rcal_image_drop_area").append("<div class=\"drag-drop-info\"><?php _e('Photos<br> Drop files here or click here and select files',RCAL_DOMAIN);?></div>");
		}
		function _getFileName(file_path) {
			file_name = file_path.substring(file_path.lastIndexOf('/')+1, file_path.length);
			return file_name;
		}


	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','reqOther','num','reqCheck')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>

	</script>

	<h2 id="rcal_admin_title"><?php echo $this->config_datas['RCAL_CONFIG_RESOURCE_NAME']." ".__('Information',RCAL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="rcal_button_div" >
	<input id="rcal_button_insert" type="button" value="<?php _e('Add',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_update" type="button" value="<?php _e('Update',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_clear" type="button" value="<?php _e('Clear',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_detail" type="button" />
	</div>

	<div id="rcal_data_detail" >

		<input type="text" id="rcal_name" />
		<input type="text" id="rcal_valid_from"/>
		<input type="text" id="rcal_valid_to" value="" />
		<?php echo parent::echoSettingPaternSelect("rcal_setting_patern_cd",$this->setting_patern_datas); ?>
		<div id="rcal_setting_data_wrap" >
			<input type="text" id="rcal_original_name"/>
			<label id="rcal_original_time_lbl" for="original_from" ><?php _e('Selected time',RCAL_DOMAIN); ?>:</label>
			<?php parent::echoOpenCloseTime("rcal_original_from",$this->config_datas['RCAL_CONFIG_OPEN_TIME'],$this->config_datas['RCAL_CONFIG_CLOSE_TIME'],$this->config_datas['RCAL_CONFIG_TIME_STEP'],'rcal_short_width_no_margin');?>
			<?php parent::echoOpenCloseTime("rcal_original_to",$this->config_datas['RCAL_CONFIG_OPEN_TIME'],$this->config_datas['RCAL_CONFIG_CLOSE_TIME'],$this->config_datas['RCAL_CONFIG_TIME_STEP'],'rcal_short_width_no_margin');?>
			<input type="button" id="rcal_original_add" value="<?php _e('Add',RCAL_DOMAIN); ?>" class="rcal_button rcal_button_short rcal_short_width_no_margin" >

			<input type="button" id="rcal_original_upd" value="<?php _e('Update',RCAL_DOMAIN); ?>" class="rcal_button rcal_button_short rcal_short_width_no_margin " >

			<div id="rcal_original_result" ></div>
		</div>
		<div id="rcal_max_setting_wrap"><input type="text" id="rcal_max_setting" value="" /></div>
		<textarea id="rcal_remark" ></textarea>
		<div class="spacer"></div>
		<div id="rcal_image_drop_area" ></div>

	</div>

	<table class="flexme" id='rcal_lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class

