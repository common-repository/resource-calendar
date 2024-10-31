if (!window.rcalSchedule)
	window.rcalSchedule = {};

rcalSchedule.makeSelectDate = function(yyyymmdd) {
	//休みの時間帯を除く
	var calcDate = new Date(yyyymmdd);
	calcDate.setHours(+rcalSchedule.config.open_time.slice(0,2));
	calcDate.setMinutes(+rcalSchedule.config.open_time.slice(-2),0,0);

	var closeDate = new Date(yyyymmdd)
	closeDate.setHours(+rcalSchedule.config.close_time.slice(0,2));
	closeDate.setMinutes(+rcalSchedule.config.close_time.slice(-2),0,0);

	var holiday_from = new Date(closeDate)
	var holiday_to = new Date(calcDate)

	var idx = rcalSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (rcalSchedule.config.days_detail[idx]) {

		holiday_from.setHours(+rcalSchedule.config.days_detail[idx][2].slice(0,2));
		holiday_from.setMinutes(+rcalSchedule.config.days_detail[idx][2].slice(-2),0,0);


		holiday_to.setHours(+rcalSchedule.config.days_detail[idx][3].slice(0,2));
		holiday_to.setMinutes(+rcalSchedule.config.days_detail[idx][3].slice(-2),0,0);



		//開店時刻と休みの開始時刻が同一ならば
		//休みの終了時刻を開店時刻にする。
		if (calcDate == holiday_from ) {
			calcDate.setHours(holiday_to.getHours());
			calcDate.setMinutes(holiday_to.getMinutes());
		}

		//閉店時刻と休みの終了時刻が同一ならば
		//閉店時刻を休みの開始時刻にする。
		if (closeDate.getTime() == holiday_to.getTime() ) {
			closeDate.setHours(holiday_from.getHours());
			closeDate.setMinutes(holiday_from.getMinutes());
		}
	}


	var setTime = Array();
	for(;;) {
		if ((calcDate <= holiday_from ) || (holiday_to <= calcDate)) {
			var hhmm = ('0' + calcDate.getHours()).slice(-2) + ':' + ('0' + calcDate.getMinutes()).slice(-2);
			var yyyymmdd = calcDate.getFullYear() + '/' + ('0' + (calcDate.getMonth() + 1)).slice(-2) + '/' + ('0' + calcDate.getDate()).slice(-2) + ' ';

			setTime.push('<option value="'+yyyymmdd+hhmm+'">'+hhmm+'</option>');
		}
		calcDate.setMinutes(calcDate.getMinutes()+rcalSchedule.config.step);
		if (calcDate.getTime() > closeDate.getTime()) break;
	}

	return setTime.join(" ");


}

rcalSchedule.chkFullHolidayInWeek = function(idx) {
	for	(var i=0,to=rcalSchedule.config.days.length;i < to ; i++ ){
		if ( idx == rcalSchedule.config.days[i] ) {
			var open = rcalSchedule.config.days_detail[i][2];
			var close = rcalSchedule.config.days_detail[i][3];
			if (open == rcalSchedule.config.open_time && close == rcalSchedule.config.close_time ) return true;
			else return false;
		}
	}
	return false;
}

