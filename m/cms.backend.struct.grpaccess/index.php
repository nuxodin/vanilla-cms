<?php
namespace qg;
html::addCssFile(sysURL.'cms.backend.struct/pub/main.css');
?>
<div class=beBox style="flex:1 1 1000px">
	<div class=-head>Struktur</div>
	<div class=-body style="display:flex; justify-content:space-between; align-items:center">
		<div>
			<?php foreach (Page($rootPageNode)->Path() as $C) { ?>
				<a href="<?=hee(Url()->addParam('rp',$C))?>"><?=trim($C->title())?$C->title():'(kein Text)'?></a> >
			<?php } ?>
		</div>
		<div style="display:flex; align-items:center">
			<div class=-access-1-bg style="width:30px; height:20px"></div>  Ansehen   
			<div class=-access-2-bg style="width:30px; height:20px"></div>  Editieren   
			<div class=-access-3-bg style="width:30px; height:20px"></div>  Administrieren
		</div>
	</div>
	<table class="c1-style cmsBeTree" id=table>
		<thead>
			<tr class=c1-vertical>
				<th style="width:20px">Nr.
				<th style="min-width:250px; width:100%"> Seite
				<th style="width:30px">
					<div>alle</div>
					<?php foreach (D()->query("SELECT * FROM grp WHERE page_access") as $vs) { ?>
				<th style="width:30px;">
					<div><?=$vs['name']?></div>
					<?php } ?>
		<tbody>
			<?php rec_createStruct(Page($rootPageNode), ['type'=>'*']) ?>
	</table>
	<?php
	function rec_createStruct($Page, $filter) {
		static $level = -1;
		global $openPageNodes;
		foreach ($Page->Children($filter) AS $id => $SubPage) {
			$level++;
			$nextFilter = $level === -1 || $SubPage->vs['type'] === 'c' ? ['type'=>'*'] : [];

			$AccessP = $SubPage->accessInheritParent();
			$open = isset($openPageNodes[$SubPage->id]);
			echo '<tr pid='.$SubPage.' class="'.($SubPage->vs['access']===null?'-inherited':'').($SubPage->vs['type']=='c'?' -isCont':'').'" data-inherited="'.$SubPage->accessInheritParent().'">';
				echo '<td style="text-align:right; font-weigth:bold">';
					echo '<a title="als Startpunkt setzen" href="'.Url()->addParam('rp',$id).'">'.$SubPage.'</a>';
				echo '<td style="padding-left:'.($level*15).'px; white-space:nowrap">';
					if ($SubPage->Children($nextFilter)) {
						echo '<a class="-toggle '.($open?'-minus':'-plus').'" href="'.URL()->addParam('opns['.$SubPage.']', ($open?0:1)).'"></a>';
					} else {
						echo '<span class=-toggle></span>';
					}
					echo '<a style="vertical-align:middle" href="'.$SubPage->url().'" title="'.$SubPage->Title().'">'.
							((string)$SubPage->Title()?cutText($SubPage->Title(),50):'(kein Text)').
							' <span style="color:#888">'.$SubPage->vs['name'].'</span>'.
							' <span style="color:#bbb">'.$SubPage->vs['module'].'</span>'.
						'</a>';
				if ($SubPage->access() > 2) {
					echo '<td v="'.$AccessP->vs['access'].'" class=-all>';
				} else {
					echo '<td style="font-style:italic;">';
				}
				foreach (D()->query("SELECT grp.id, grp.name, pg.access AS access FROM grp LEFT JOIN page_access_grp pg ON grp.id = pg.grp_id AND pg.page_id = '".$AccessP."' WHERE grp.page_access") AS $vs) {
					if ($SubPage->access() > 2) {
						echo '<td gid='.$vs['id'].' v="'.$vs['access'].'" title="'.$vs['name'].' ('.$vs['id'].')">';
					} else {
						echo '<td>';
					}
				}
			if ($open) rec_createStruct($SubPage, $nextFilter);
			$level--;
		}
	}
	?>

	<script>
	// set access
	$('#table').find('td').each(function(i,el) {
		$(el).on('click', function(ev) {
			if (!ev.target.hasAttribute('v')) return;
			var td = $(ev.target);
			var grp = td.attr('gid');
			var pid = td.parent('tr').attr('pid');
			var access = 1*td.attr('v');
			if (grp) {
				access = access > 2 ? 0 : access + 1;
				$fn('page::changeGroup')(pid,grp,access);
			} else {
				if (td.parent().hasClass('-inherited')) {
					access = 0;
				} else {
					access = access > 0 ? '' : access + 1;
				}
				$fn('page::setPublic')(pid,access).run();
			}
			setTimeout(()=>{
				$fn('page::reload')(<?=$Cont?>).run();
			},40);
		});
	});
	var table = document.getElementById('table');
	table.addEventListener('mouseover', function(e){
		var active = table.querySelector('tr.-mark');
		active && active.classList.remove('-mark')
		var tr = e.target.closest('tr');
		if (!tr) return;
		var inherited = tr.getAttribute('data-inherited');
		if (!inherited) return;
		var el = table.querySelector('[pid="'+inherited+'"]');
		if (!el) return;
		el.classList.add('-mark');
	})
	</script>
</div>
