<?php namespace qg ?>
<div class=c1-box style="flex:0 1 1200px">
	<div class=-head>Struktur</div>
	<div class=-body>
		<?php foreach (Page($rootPageNode)->Path() as $C) { ?>
			<a href="<?=hee(Url()->addParam('rp',$C))?>"><?=trim($C->title())?$C->title():'(kein Text)'?></a> >
		<?php } ?>
	</div>
	<table class="c1-style cmsBeTree">
		<thead>
			<tr>
				<th style="width:20px"> Nr.
				<th style="min-width:250px; width:100%"> Seite
				<th> Online ab
				<th> Online bis
				<th> Öffentlich
				<th> Sichtbar
				<th> Durchsuchbar
				<th> Layout
		<tbody data-part=list>
			<?php include 'parts/list.php'; ?>
	</table>
	<script>
	toggleVisible = function(el, pid) {
		var callb = function() {
			if (el.style.color === 'green') {
				el.innerHTML = 'unsichtbar';
				el.style.color = 'red';
			} else {
				el.innerHTML = 'sichtbar';
				el.style.color = 'green';
			}
		}
		var v = el.style.color === 'green' ? 0 : 1;
		$fn('page::setVisible')(pid, v).run(callb);
		return false;
	}
	toggleSearchable = function(el, pid) {
		var callb = function() {
			if (el.style.color==='green') {
				el.innerHTML = 'nicht durchsuchbar';
				el.style.color = 'red';
			} else {
				el.innerHTML = 'durchsuchbar';
				el.style.color = 'green';
			}
		}
		v = el.style.color === 'green' ? 0:1;
		$fn('page::setSearchable')(pid, v).run(callb);
		return false;
	}
	toggleAccess = function(el, pid) {
		var callb = function() {
			if (el.style.color === 'green') {
				el.innerHTML = 'private';
				el.style.color = 'red';
			} else {
				el.innerHTML = 'public';
				el.style.color = 'green';
			}
		}
		v = el.style.color === 'green' ? 0:1;
		$fn('page::setPublic')(pid,v).run(callb);
		return false;
	}
	</script>
</div>
