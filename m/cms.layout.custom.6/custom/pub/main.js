/*
// --l1-head-height
!function(){
	function calcHeadHeight(){
		var height = document.getElementById('head').offsetHeight;
		console.log(height)
		document.documentElement.style.setProperty('--l1-head-height', height+'px');
	}
	addEventListener('resize',calcHeadHeight);
	requestAnimationFrame(calcHeadHeight)
}();

// --l1-height-with-ui | for fullscreen-intro on android and ios
!function(){
	var lastHeight = null;
	function check(){
		var height = window.innerHeight;
		if (lastHeight !== null) {
			height = Math.abs(height - lastHeight) > 60 ? height : lastHeight; // grösser oder kleiner geworden um mehr als 60 px
		}
		lastHeight = height;
		document.documentElement.style.setProperty('--l1-height-with-ui', height+'px');
	};
	check();
	addEventListener('resize',check);
	//requestAnimationFrame(check);
	//document.addEventListener('DOMContentLoaded',check);
}();
// ussage: 	max-height:var(--l1-height-with-ui);
*/