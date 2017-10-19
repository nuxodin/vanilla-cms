<?php namespace qg ?>
<div class=qgCmsFront1ClassesManager pid=<?=$Cont?>>
	<input placeholder="<?=hee(L('neuer Tag'))?>" class=-add>
	<div style="margin-top:10px">
		<?php
		$classes = $Cont->classes();
		foreach (D()->query("SELECT class, count(class) as count FROM ".table('page_class')." GROUP BY class ORDER BY page_id = '".$Cont."', count(*) DESC LIMIT 20") as $vs) {
			$cl = $vs['class'];
			if (!$cl || isset($classes[$cl])) continue;
			$classes[$cl] = $vs;
		}
		?>
		<div class=-classes>
			<?php foreach ($classes as $class => $vs) { ?>
				<label>
					<input type=checkbox <?=!isset($vs['count'])?'checked':''?> class=-added style="vertical-align:bottom;">
					<span><?=hee($class)?></span>
				</label>
			<?php } ?>
		</div>
	</div>
</div>

<style>
.qgCmsFront1ClassesManager  label {
	min-width:80px;
	min-width:25%;
	flex:0.01 1 auto;
	padding:8px;
	border:4px solid #fff;
	cursor:pointer;
	border-radius:3px;
	background:#eee;
	box-sizing:border-box;
}
.qgCmsFront1ClassesManager  label:hover {
	background:#ddd;
}
.qgCmsFront1ClassesManager  .-classes {
	margin:-1px;
	display:flex;
	flex-wrap:wrap;
}
.qgCmsFront1ClassesManager  input:checked + span {
	font-weight:bold;
}
.qgCmsFront1ClassesManager .-add {
	width:100%;
	box-sizing:border-box;
}
</style>
