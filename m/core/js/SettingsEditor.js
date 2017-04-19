window.c1Use('c1',function(){
	'use strict';
	window.qgSettingsEditor = function(classOrEl) {
		const div = classOrEl.tagName ? $(classOrEl) : $('.'+classOrEl);
		div.addClass('qgSettingsEditor');
		const change = function(e){
			const el = e.target;
			const id = el.name;
			const value = el.type === 'checkbox' ? el.checked : $(el).val();
			$fn('SettingsEditor::set')(id, value);
	        div[0].dispatchEvent(new CustomEvent('qgSettingsEditorChange', {bubbles:true}));
		};
		div.on('change', change);
		div.on('input', change.c1Debounce(400));
		div.on('click', '.toggle.-plus', function(e) {
			$(this).removeClass('-plus').addClass('-minus');
			const li = $(e.target).closest('li');
			const id = li.find('[name]').attr('name');
			$fn('SettingsEditor::open')(id).run(res=>li.append(res));
		});
		div.on('click', '.toggle.-minus', function(e) {
			$(this).removeClass('-minus').addClass('-plus');
			const li = $(e.target).closest('li');
			const id = li.find('[name]').attr('name');
			$fn('SettingsEditor::close')(id);
			li.children('ul').remove();
		});
		div.on('click', '.-rem', function(e) {
			if (!confirm('Möchten Sie die Einstellung Wirklich löschen?')) return;
			const li = $(e.target).closest('li');
			const id = li.find('[name]').attr('name');
			$fn('SettingsEditor::remove')(id);
			li.remove();
		});
		if (document.getElementById('qgSettingsEditorCss')) return;
		const css =
		'.qgSettingsEditor > ul { '+
		'	max-width:700px; '+
		'	background:#fff; '+
		'} '+
		'.qgSettingsEditor > ul:not(.-hasSub) > li > .-row > .-toggle { '+
		'	display:none; '+
		'} '+
		'.qgSettingsEditor ul { '+
		'	list-style:none; '+
		'	padding:0; '+
		'	margin:0; '+
		'} '+
		'.qgSettingsEditor ul ul { '+
		'	padding-left:20px; '+
		'} '+
		'.qgSettingsEditor .-row { '+
		'	border-bottom:1px solid #f4f4f4; '+
		'	display:flex; '+
		'	align-items:center; '+
		'} '+
		'.qgSettingsEditor .-row:hover { '+
		'	background:#f4f4f4; '+
		'} '+
		'.qgSettingsEditor .-row > * { '+
		'	padding:5px; '+
		'} '+
		'.qgSettingsEditor .-row > .-name { '+
		'	flex:1 0 auto; '+
		'} '+
		'.qgSettingsEditor .-row > .-inp { '+
		'	flex:0 1 320px; '+
		'} '+
		'.qgSettingsEditor input:not([type=checkbox]), '+
		'.qgSettingsEditor textarea { '+
		'	width:100%; '+
		'	box-sizing:border-box; '+
		'} '+
		'.qgSettingsEditor textarea { '+
		'	height:120px; '+
		'} '+
		'.qgSettingsEditor .-rem { '+
		'	text-align:center; '+
		'   color:transparent; '+
		'} '+
		'.qgSettingsEditor .-toggle > a,'+
		'.qgSettingsEditor .-rem > a { '+
		'   display:block; '+
		'   color:transparent; '+
		'	width:1.8em; '+
		'	height:1.8em; '+
		'	background-repeat:no-repeat; '+
		'	background-position:50%; '+
		'} '+
		'.qgSettingsEditor .-rem    > a       { background-image:url(data:image/svg+xml;utf8,'+encodeURIComponent(remImgData)+');   cursor:pointer; } '+
		'.qgSettingsEditor .-toggle > .-minus { background-image:url(data:image/svg+xml;utf8,'+encodeURIComponent(closeImgData)+'); cursor:pointer; background-size:95%; } '+
		'.qgSettingsEditor .-toggle > .-plus  { background-image:url(data:image/svg+xml;utf8,'+encodeURIComponent(openImgData)+');  cursor:pointer; background-size:95%; } '+
		'';
		$('<style id=qgSettingsEditorCss>'+css+'</style>').appendTo(document.head);
	};
	const remImgData =
	'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" /></svg>';
	const openImgData =
	'<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" stroke="#000" stroke-width="4.6">'+
		'<line x1="8" y1="32" x2="56" y2="32"/>'+
		'<line x1="32" y1="8" x2="32" y2="56"/>'+
	'</svg>';
	const closeImgData =
	'<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" stroke="#000" stroke-width="4.6">'+
		'<line x1="8" y1="32" x2="56" y2="32"/>'+
	'</svg>';

	c1.onElement('.qgSettingsEditor',qgSettingsEditor);
})
