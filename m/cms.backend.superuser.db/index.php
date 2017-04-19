<?php namespace qg ?>
<div class=beBoxCont>
	<div class=beBox>
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
	<div class=beBox data-part=tStruct></div>
	<div class=beBox data-part=tEntries></div>
	<div class=beBox data-part=fStruct></div>
</div>
