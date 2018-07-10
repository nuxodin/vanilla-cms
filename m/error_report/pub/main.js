window.error_report_count = window.error_report_count || 0; // global: make it possible to stop reporting (eg. browser-warning is shown)
!function() {
	function send(data){
		if (error_report_count++ > 50) return;
		if (data.message) {
			var req = new XMLHttpRequest();   // new HttpRequest instance
			req.open('POST', appURL+'js-error');
			req.setRequestHeader('Content-Type','application/json');
			data.referer = document.referrer;
			data.request = location.href;
			req.send(JSON.stringify(data));
		}
		if (window.error_report_debug && error_report_count < 2) {
			if (document.body) {
				var div = document.createElement('div');
				div.style.cssText = 'position:fixed; top:0; left:0; right:0; background:rgba(255,40,0,.9); color:#fff; padding:20px; font-size:17px; z-index:1000; text-align:center';
				div.innerHTML = data.message;
				document.body.append(div);
				setTimeout(function(){ div.remove(); }, 2000);
			} else {
				alert(data.message);
			}
		}
	}
	window.addEventListener('error', function(e){
		var error = e.error;
		var stack = error && error.stack ? unserializeStack(error.stack) : [];
		send({
			message: e.message,
			file: e.filename,
			line: e.lineno,
			col:  e.colno,
			backtrace: stack
		});
  	});
	window.addEventListener("unhandledrejection", function(e) {
		var message = e.reason.stack.split('\n')[0];
		var stack = unserializeStack(e.reason.stack);
		send({
			message: 'Unhandled rejection in Promise: '+message,
			file: stack[0].file,
			line: stack[0].line,
			col:  stack[0].col,
			backtrace: stack
		});
	});
	function wrapConsole(method){
		var original = console[method];
		console[method] = function(message){
			var stack = new Error().stack;
			stack = unserializeStack(stack)
			stack.shift();
			var latest = stack[0];
			var data = {
				message:message,
				function: latest ? latest.function : '-1',
				file:     latest ? latest.file : '-1',
				line:     latest ? latest.line : '-1',
				col:      latest ? latest.col  : '-1',
				backtrace: stack,
			}
			send(data);
			return original.apply(console, arguments);
		}
	}
	wrapConsole('error');
	wrapConsole('warn');

	function unserializeStack(asString){
		var stack = [];
		if (!asString) return stack; // ie11
		var parts = asString.split('\n');
		for (var i=0, line; line = parts[i++];) {
			// firefox:
			// wrapConsole/console[method]@http://localhost/v6_full/m/error_report/pub/main.js?1519123438:39:16
			// chrome:
			// at console.(anonymous function) [as warn] (http://localhost/v6_full/m/error_report/pub/main.js?1519123580:39:16)
			// edge:
			// at console[method] (http://localhost/v6_full/m/error_report/pub/main.js?1519123438:39:4)
			//var x = line.match(/(.*)(http[^)]+)/);
			var x = line.match(/(.*)(http[^)]+):([0-9]+):([0-9]+)/);
			//console.log('-------------------');
			//console.log(line)
			//console.log(x)
//			var x = line.match(/(.*)[\(@](.+)/);
			if (!x) continue; // first line chrome / ie
			var data = {
				function: x[1].replace(/^ *at /,'').trim().replace(/ \($/,'').replace(/@$/,''),
				file: x[2].trim(),
				line: x[3],
				col: x[4],
			};
			// var fn   = x[1].replace(/^ *at /,'').trim();
			// var file = x[2].trim();
			// var line = x[3];
			// var col  = x[4];
			// x = file.match(/:[0-9]+/g);
			// file = file.replace(/:[0-9]+/g, '').replace(/\)$/,'');
			// var line = x ? x[0].replace(':','') : '';
			// var col  = x ? x[1].replace(':','') : ''; // todo
			stack.push(data);
		}
		return stack;
	}
}();
