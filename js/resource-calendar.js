if (!window.rcalSchedule)
	window.rcalSchedule = {};


rcalSchedule._events = {};
rcalSchedule._days = {};
rcalSchedule._width = [];

rcalSchedule._daysResource = {};
rcalSchedule._months = {};

/*
rcalSchedule.config={
	days: []
	,days_detail:[]
	,on_business :[]
	,holidays: []
	,open_position : 0
	,close_width : 0
	,full_half : []
};
*/
rcalSchedule.setEventDetail = function(ev,detail) {
	this._events[ev]["remark"] = detail[0];
	this._events[ev]["p2"] = detail[1];
	this._events[ev]["name"] = detail[2];
	this._events[ev]["tel"] = detail[3];
	this._events[ev]["mail"] = detail[4];
	this._events[ev]["memo"] = detail[5];
	this._events[ev]["repeat_every"] = detail[6];
	this._events[ev]["valid_from"] = detail[7];
	this._events[ev]["repeat_end_patern"] = detail[8];
	this._events[ev]["valid_to"] = detail[9];
	this._events[ev]["repeat_cnt"] = detail[10];
	this._events[ev]["repeat_weeks"] = detail[11];
}

rcalSchedule.chkHoliday = function(yyyymmdd) {
	//特別な営業日
	for	(var i=0,to=rcalSchedule.config.on_business.length;i < to ; i++ ){
		if (yyyymmdd.getTime()== rcalSchedule.config.on_business[i].getTime() ) return false;
	}
	//定休日　曜日で判断
	var tmp_days = yyyymmdd.getDay();
	for	(var i=0,to=rcalSchedule.config.days.length;i < to ; i++ ){
		if ( tmp_days == rcalSchedule.config.days[i] ) return true;
	}
	//特別な休業日
	if (rcalSchedule.date.existHolidays(yyyymmdd) ) return true;
	return false;
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

rcalSchedule.chkFullHoliday = function(yyyymmdd) {
	if (rcalSchedule.date.existHolidays(yyyymmdd) ) return true;
	var idx = rcalSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (idx == -1) return false;
//半休の場合は、falseで返す
	var open = rcalSchedule.config.days_detail[idx][2];
	var close = rcalSchedule.config.days_detail[idx][3];
	if (open == rcalSchedule.config.open_time && close == rcalSchedule.config.close_time ) return true;
	return false;
}

rcalSchedule.getLeft = function(base,max_cnt) {
	return base + rcalSchedule._calcLeft(max_cnt);
}

rcalSchedule.getWidth = function(first_cnt,max_cnt) {
	return rcalSchedule._calcWidth (first_cnt,max_cnt);
}

rcalSchedule.getHolidayLeft = function(yyyymmdd,base) {
	var idx = rcalSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (rcalSchedule.config.days_detail[idx]){
		var max_cnt = +rcalSchedule.config.days_detail[idx][0];
		return base + rcalSchedule._calcLeft(max_cnt);
	}
	else if  (rcalSchedule.date.existHolidays(yyyymmdd) ) {
		return base;
	}
	else {
		alert("E099 rcalSchedule.getLeft is wrong.");
	}
}



rcalSchedule.getHolidayWidth = function(yyyymmdd) {
	var idx = rcalSchedule.config.days.indexOf(yyyymmdd.getDay());
	if (rcalSchedule.config.days_detail[idx]) {
		var first_cnt = +rcalSchedule.config.days_detail[idx][0];
		var max_cnt = first_cnt + rcalSchedule.config.days_detail[idx][1] ;
		return rcalSchedule._calcWidth (first_cnt,max_cnt);
	}
	else if  (rcalSchedule.date.existHolidays(yyyymmdd) ) {
		var max_cnt = rcalSchedule._width.length;
		return rcalSchedule._calcWidth (0,max_cnt);
	}
	else {
		alert("E099 rcalSchedule.getWidth is wrong.");
	}
}

/*
rcalSchedule.calcWidthBase = function(base,width) {
	var calc = 0;
	var max_cnt = +base + width;
	if (base == 0 ) base =1;
	for(var i = +base-1 ;i < max_cnt; i++ ) {
		calc += rcalSchedule._width[i];
	}
	return calc;
}
*/

rcalSchedule._calcLeft = function(max_cnt) {
	var calc = 0;
	for(var i=0;i < max_cnt; i++ ) {
		calc += rcalSchedule._width[i];
	}
	return calc;
}


rcalSchedule._calcWidth = function(first_cnt,max_cnt) {
	var calc = 0;
	var i = first_cnt;
	for(i;i < max_cnt; i++ ) {
		calc += rcalSchedule._width[i];
	}
	return calc;
}

rcalSchedule.setWidth = function(setWidth) {
	rcalSchedule._width.length = 0;
	var tmp_array = setWidth.split(",");
	for(var i = 0,max_cnt = tmp_array.length;i < max_cnt  ; i++ ){
		var setWidth = tmp_array[i] /12 ;
		for(var j = 0 ; j < 12 ; j++ ) {
			rcalSchedule._width.push(setWidth);
		}
	}
}

rcalSchedule.date = {
	toYYYYMMDD:function(yyyymmdd) {
		var y = yyyymmdd.getFullYear();
		var m = yyyymmdd.getMonth() + 1;
		var d = yyyymmdd.getDate();
		return y+('0' + m).slice(-2)+('0' + d).slice(-2);
	},
	existHolidays:function(yyyymmdd) {
		for	(var i=0,to=rcalSchedule.config.holidays.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === rcalSchedule.config.holidays[i].getTime() ) return true;
		}
	}
}


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


