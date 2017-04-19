<?php namespace qg; ?>
<table>
	<tr>
		<td> Start-Seite (Ursprung) ...
		<td> <input type=qgcms-page value="<?=$Cont->SET['startPage']?>" onblur="$fn('page::setDefault')(<?=$Cont?>,{'startPage': this.value}).run()">
	<tr>
		<td> ...oder Start-Tiefe:
		<td> <input value="<?=$Cont->SET['startLevel']?>" onblur="$fn('page::setDefault')(<?=$Cont?>,{'startLevel': this.value}).run()">
	<tr>
		<td> Tiefe:
		<td> <input value="<?=$Cont->SET['level']?>" oninput="$fn('page::setDefault')(<?=$Cont?>,{'level': this.value}).run()">
	<tr>
		<td> Sichtbarkeits-Filter für Seiten:
		<td>
			<select onchange="$fn('page::setDefault')(<?=$Cont?>,{filter_visible: $(this).val()}).run()">
				<option <?=$Cont->SET['filter_visible']->v==='visible'?'selected':''?> value=visible> sichtbar
				<option <?=$Cont->SET['filter_visible']->v==='hidden' ?'selected':''?> value=hidden> nicht sichtbar
				<option <?=$Cont->SET['filter_visible']->v==='all'    ?'selected':''?> value=all> beide
			</select>
</table>

<br>

<label style="display:block">
	<input onclick="$fn('page::setDefault')(<?=$Cont?>,{'pathOnly': this.checked}).run()" <?=$Cont->SET['pathOnly']->v?'checked':''?> type=checkbox>
	Nur Seiten welche den Weg zur aktuellen Seite beschreiben anzeigen.
</label>

<label style="display:block">
	<input onclick="$fn('page::setDefault')(<?=$Cont?>,{'include contents': this.checked}).run()" <?=$Cont->SET['include contents']->v?'checked':''?> type=checkbox>
	Inhalte auch auflisten (#-links)
</label>
