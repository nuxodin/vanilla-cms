
Date.prototype.setY 	= function(v) { this.y = v;   this.setFullYear(v); };
Date.prototype.setM 	= function(v) { this.m = v;   this.setMonth(v); };
Date.prototype.setD 	= function(v) { this.d = v;   this.setDate(v); };
Date.prototype.setH 	= function(v) { this.h = v;   this.setHours(v); };
Date.prototype.setMin 	= function(v) { this.min = v; this.setMinutes(v); };
Date.prototype.setSec 	= function(v) { this.sec = v; this.setSeconds(v); };

Date.prototype.getY 	= function() { return 														  this.getFullYear();			};
Date.prototype.getM 	= function() { return (this.sec || this.min || this.h || this.d || this.m ) ? this.getMonth() 		: null; };
Date.prototype.getD 	= function() { return (this.sec || this.min || this.h || this.d )			? this.getDate() 		: null; };
Date.prototype.getH 	= function() { return (this.sec || this.min || this.h )					 	? this.getHours() 		: null; };
Date.prototype.getMin 	= function() { return (this.sec || this.min ) 								? this.getMinutes() 	: null; };
Date.prototype.getSec 	= function() { return (this.sec )											? this.getSeconds() 	: null; };

Date.prototype.fromStr = function(val, format) {
	var my = this;
	if (!val) { val = my.toStr(format); }
	format.replace(/\%([a-zA-Z\%])|[^\%]+/g, function(m, t) {
		if (t && Date.tokens[t]) {
			var token = Date.tokens[t];
			val = val.replace(token.reg, function(m) {
				token.setDate(my, m);
				return '';
			});
		} else {
			val = val.substr(m.length);
		}
	});
};
Date.prototype.toStr = function(format) {
	var my = this;
	return format.replace(/\%[a-zA-Z\%]/g, function(match) {
		var token = Date.tokens[match.substr(1)]; 
		if (token) {
			return token.getDate(my); 
		} else {
			return match;
		}
	});
};
Date.tokens = {
	'%':{	
		reg: /%/,
		setDate: function(date, match) { },
		getDate: function(date) { return '%'; }
	},
	d:{	
		reg: /[0-9]{2}/,
		setDate: function(date, match) { date.setD(parseInt(match)); },
		getDate: function(date) { return (100+date.getDate()+'').substr(1); }
	},
	m:{	
		reg:/[0-9]{2}/,
		setDate: function(date, match) { date.setM(parseInt(match)-1); },
		getDate: function(date) { return (101+date.getMonth()+'').substr(1); }
	},
	e:{
		reg:/[0-9]{1,2}/,
		setDate: function(date, match) { date.setM(parseInt(match)-1); },
		getDate: function(date) { return date.getMonth()+1; }
	},
	Y:{	
		reg:/[0-9]{4}/,
		setDate: function(date, match) { date.setY(parseInt(match)); },
		getDate: function(date) { return date.getFullYear(); }
	},
	y:{	
		reg:/[0-9]{1,2}/,
		setDate: function(date, match) { date.setY(parseInt(match)+2000); },
		getDate: function(date) { return date.getYear(); }
	},
	p:{	
		reg:/[ap]m/i,
		setDate: function(date, match) { date.setH( date.getH + (match=='am'?0:12) ); },
		getDate: function(date) { return date.getHours() >= 12 ? 'pm' : 'am'; }
	},
	I:{	
		reg:/[0-9]{2}/,
		setDate: function(date, match) { date.setH( date.getH + parseInt(match) ); },
		getDate: function(date) { return (date.getHours()) >= 12 ? date.getHours()-12 : date.getHours(); }
	},
	H:{
		reg:/[0-9]{2}/,
		setDate: function(date, match) { date.setH( parseInt(match) ); },
		getDate: function(date) { return (100+date.getHours()+'').substr(1); }
	},
	M:{	
		reg:/[0-9]{1,2}/,
		setDate: function(date, match) { date.setMin( parseInt(match) ); },
		getDate: function(date) { return (100+date.getMinutes()+'').substr(1); }
	},
	S:{	
		reg:/[0-9]{1,2}/,
		setDate: function(date, match) { date.setSec(parseInt(match)); },
		getDate: function(date) { return (100+date.getSeconds()+'').substr(1); }
	},
	T:{
		reg:/[0-9]{2}:[0-9]{2}:[0-9]{2}/,
		setDate: function(date, match) {
			var parts = match.split(':');
			date.setH( parts[0].toInt() );
			date.setMin( parts[1].toInt() );
			date.setSec( parts[2].toInt() );
		},
		getDate: function(date) { return Date.tokens.H.getDate(date)+':'+Date.tokens.M.getDate(date)+':'+Date.tokens.S.getDate(date); }
	},
	D:{	
		reg:/[0-9]{2}\/[0-9]{2}\/[0-9]{2}/,
		setDate: function(date, match) { 
			var parts = match.split('/');
			date.setM(parts[0].toInt());
			date.setD(parts[1].toInt());
			date.setY(parts[2].toInt()+2000); 
		},
		getDate: function(date) { return Date.tokens.m.getDate(date)+'/'+Date.tokens.d.getDate(date)+'/'+Date.tokens.y.getDate(date); }
	}
};

Date.prototype.getWeek = function (dowOffset) {
	dowOffset = typeof(dowOffset) == 'int' ? dowOffset : 0;
	var newYear = new Date(this.getFullYear(),0,1);
	var day = newYear.getDay() - dowOffset;
	day = (day >= 0 ? day : day + 7);
	var daynum = Math.floor((this.getTime() - newYear.getTime() - (this.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
	var weeknum;
	if (day < 4) {
		weeknum = Math.floor((daynum+day-1)/7) + 1;
		if (weeknum > 52) {
			nYear = new Date(this.getFullYear() + 1,0,1);
			nday = nYear.getDay() - dowOffset;
			nday = nday >= 0 ? nday : nday + 7;
			weeknum = nday < 4 ? 1 : 53;
		}
	} else {
		weeknum = Math.floor((daynum+day-1)/7);
	}
	return weeknum;
};
