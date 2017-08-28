<?php namespace qg ?>
<div class=beBoxCont>
	<div class=c1-box>
		<div class=-head>
			Tabellen
			<span onclick="dbAddTable(<?=$Cont?>)" style="font-size:30px; display:block; cursor:pointer; font-weight:normal">+</span>
		</div>
		<ul class=-body>
			<?php foreach (D()->Tables() as $T) { ?>
				<li><a class="qgShowTable" href="#"><?=$T?></a>
			<?php } ?>
		</ul>
	</div>
	<div class=c1-box data-part=tStruct></div>
	<div class=c1-box data-part=tEntries></div>
	<div class=c1-box data-part=fStruct></div>
</div>
