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
				file: latest ? latest.file : '-1',
				line: latest ? latest.line : '-1',
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
		var parts = asString.split('\n');
		for (var i=0, part; part = parts[i++];) {
			var x = part.match(/(.*)[\(@](.+)/);
			if (!x) continue; // first line chrome / ie
			var fn = x[1].replace(/^ *at /,'').trim();
			var file = x[2].trim();
			x = file.match(/:[0-9]+/g);
			file = file.replace(/:[0-9]+/g, '').replace(/\)$/,'');
			var line = x ? x[0].replace(':','') : '';
			var col  = x ? x[1].replace(':','') : ''; // todo
			stack.push({
				'function': fn,
				file: file,
				line: line,
				col: col
			});
		}
		return stack;
	}
}();
