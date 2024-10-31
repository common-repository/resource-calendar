<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'data/booking-data.php');



class Reservation_Data extends Booking_Data {

	const TABLE_NAME = 'salon_reservation';

	function __construct() {
		parent::__construct();
	}




}