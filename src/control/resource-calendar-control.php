<?php


abstract class ResourceCalendar_Control  {


	protected $respons_type = ResourceCalendar_Response_Type::JASON;
	private $is_show_detail_msg = false;
	protected $config = null;


	public function __construct() {
		set_exception_handler(  array( &$this, '_error_action') );
	}

	public function set_config ($config) {
		$this->is_show_detail_msg = (ResourceCalendar_Config::DETAIL_MSG_OK == $config[ 'RCAL_CONFIG_SHOW_DETAIL_MSG' ]);
		$this->config = $config;
	}

	public function set_response_type ( $response_type) {
		$this->respons_type = $response_type;
	}


	public function exec() {

		try {
			$this->_checkRole();
			$this->do_action();

		}
		//以下はUNITTEST用
		catch ( WPAjaxDieStopException $e ) {
			throw new WPAjaxDieStopException( '0' );
		} catch ( WPAjaxDieContinueException $e ) {
			throw new WPAjaxDieContinueException( '0' );
		} catch (Exception $e) {
			$this->_error_action($e);
		}
	}

	public function do_require($class_name,$type,$permits) {
		if (! in_array($class_name,$permits) )  throw new ResourceCalendarException(__('invalid request',RCAL_DOMAIN));

		$path = RCAL_PLUGIN_SRC_DIR.$type.'/'.strtolower(str_replace('_','-',$class_name) ).'.php';
		if ( file_exists($path)) {
			require_once($path);
		}
		else {
			throw new ResourceCalendarException(__('no class file',RCAL_DOMAIN));
		}
		if (!class_exists($class_name)) {
			throw new ResourceCalendarException(__('no class ',RCAL_DOMAIN));
		}

	}

	abstract  function do_action();

	private function _error_action($e) {
		$this->_error_handler($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(),$e->getTraceAsString());
	}

	public function _error_handler ( $errno, $errstr, $errfile, $errline, $errcontext ) {

//		if (error_reporting() === 0) return;
		$detail_msg = '';
		if ($this->is_show_detail_msg ) {
			$detail_msg ="\n".$errfile.$errline."\n".$errcontext;
		}
		//現状JSON形式のみなので、他のパターンは削除する
		$msg['status'] = 'Error';
//		if (empty($errno) ) {
		if ('' == strval($errno) ) {
			$msg['message'] = ResourceCalendar_Component::getMsg('E007',$errstr.$detail_msg);
		}
		else {
			$msg['message'] = $errstr.$detail_msg.'('.$errno.')';
		}
		echo json_encode($msg);
		wp_die();
	}

	private function _checkRole() {
		ResourceCalendar_Component::checkRole(get_class($this));
	}


}		//class


