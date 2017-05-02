window.c1Use('c1',function(){
	'use strict';

	if (window.qgSettingsEditor) return; // ugly

	window.qgSettingsEditor = function(classOrEl) {
		const div = classOrEl.tagName ? classOrEl : document.querySelector('.'+classOrEl);
		div.classList.add('qgSettingsEditor');
		const change = function(e){
			const el = e.target;
			const id = el.name;
			const value = el.type === 'checkbox' ? el.checked : (el.tagName === 'SELECT' ? el.options[el.selectedIndex].value : el.value);
			$fn('SettingsEditor::set')(id, value);
	        div.dispatchEvent(new CustomEvent('qgSettingsEditorChange', {bubbles:true}));
		};
		div.addEventListener('change', change);
		div.addEventListener('input', change.c1Debounce(400));
		div.addEventListener('click', e=>{
			let toggle = e.target.closest('.toggle');
			if (toggle) {
				toggle.classList.toggle('-plus');
				let open = toggle.classList.toggle('-minus');
				const li = toggle.closest('li');
				const id = li.c1Find('[name]').getAttribute('name');
				if (open) {
					$fn('SettingsEditor::open')(id).run(res=>li.append(c1.dom.fragment(res)));
				} else {
					$fn('SettingsEditor::close')(id);
					li.c1Find('>ul').remove();
				}
			}
			let remove = e.target.closest('.-rem');
			if (remove) {
				if (!confirm('Möchten Sie die Einstellung Wirklich löschen?')) return;
				const li = remove.closest('li');
				const id = li.c1Find('[name]').getAttribute('name');
				$fn('SettingsEditor::remove')(id);
				li.remove();
			}
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
		document.head.append(c1.dom.fragment('<style id=qgSettingsEditorCss>'+css+'</style>'));
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
