var TimePicker = function(holder, options) {
	var my = this;
	this.options = {
		startTime: {hour:new Date().getHours(),
		minute: new Date().getMinutes()},
		selectedTime: null,
		format24: false,
		clockSize: {width:142,height:142},
		lang: {'am':'AM','pm':'PM'},
		onChange: function() {}
	};
	$.extend(this.options, options);

	this.moveEl = {move:false};

	this.time = this.options.selectedTime || this.options.startTime;

	this.holder = $(holder).css({width:this.options.clockSize.width, height:this.options.clockSize.height});

	$('<div class=clockFace />').appendTo(this.holder);
	this.minuteHand = $('<div class=minuteHand />').appendTo(this.holder);
	this.hourHand 	= $('<div class=hourHand />').appendTo(this.holder);
	this.ampm 		= $('<a href=# class=ampm />').appendTo(this.holder);
	this.updateAmPm();

	this.ampm.on('click', function(e) {
		e.preventDefault();
		my.time.hour = ((my.time.hour + 12) %24);
		my.updateAmPm();
		my.options.onChange();
	});

	this.moveHands();

	this.holder.on({
		'mousedown': function(e) {
			coord = my.holder.offset();
			coord.width =  my.holder[0].offsetWidth;
			coord.height = my.holder[0].offsetHeight;

			var ang = my.clickAngle({x:e.clientX, y:e.clientY}, coord);
			var h_ang = (my.time.hour%12) * 30;
			var m_ang = my.time.minute * 6;

			my.moveEl.move = true;
			my.moveEl.coord = coord;
			
			if (Math.abs(ang - m_ang) < Math.abs(ang - h_ang))
				my.moveEl.el = "minute";
			else if (Math.abs(ang - m_ang) > Math.abs(ang - h_ang))
				my.moveEl.el = "hour";
			else {
				if ($(e.target).css("backgroundImage").indexOf(my.options.hourHandImage) != -1)
					my.moveEl.el = "hour";
				else
					my.moveEl.el = "minute";
			}
		},
		'mouseup': function() {
			my.moveEl = {move: false};
		},
		'mousemove': function(e) {
			if (my.moveEl.move) {
				var ang = my.clickAngle({x:e.clientX, y:e.clientY}, my.moveEl.coord);
				var ang_by = my.moveEl.el == "hour" ? 30 : 6;
			
				if (my.moveEl.el == "hour") {
					var h = parseInt(ang/ang_by);
					if (!isNaN(h))
						my.time.hour = h;
					
					if (my.ampm.innerHTML == my.options.lang.pm)
						my.time.hour = (my.time.hour+12)%24;
				}
				else{
					var m = parseInt(ang/ang_by);
					!isNaN(m) && (my.time.minute = m);
				}
				my.moveHands();
				my.options.onChange();
			}
		}
	});
};

TimePicker.prototype = {
	updateAmPm: function() {
		this.ampm[0].innerHTML = ( this.time.hour < 12 ? this.options.lang.am : this.options.lang.pm );
	},
	moveHands: function() {
		this.hourHand.css("backgroundPosition", (((this.time.hour % 12) *  67) * -1));
		this.minuteHand.css("backgroundPosition", ((this.time.minute * 111) * -1));
	},
	clickAngle: function(pnt, coord) {
		var c_x = coord.width/2;
		var c_y = coord.height/2;

		var x = pnt.x + pageXOffset - coord.left;
		var y = pnt.y + pageYOffset - coord.top;
		
		var t_x = c_x;
		var t_y = y;
		
		var CA = t_x - x;
		var CO = t_y - c_y;
		var AO = Math.sqrt(Math.pow(CA, 2) + Math.pow(CO, 2));
		
		var ang = Math.round((Math.acos((Math.pow(Math.abs(CA), 2) - Math.pow(Math.abs(AO), 2) - Math.pow(CO, 2))/(2 * CO * AO))) * 180/Math.PI);
		
		if (x < c_x) ang = 360 - ang;
		return ang;
	}
};
