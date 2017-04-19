<?php namespace qg ?>
<div>
	<?php if (!$Cont->SET['hide input']->setType('bool')->v) { ?>
	<form>
		<?php
		foreach ($_GET as $name => $value) {
			if (is_array($value)) continue;
			echo '<input type=hidden name="'.hee($name).'" value="'.hee($value).'">';
		}
		?>
		<input <?=$Cont->SET['input no autofocus']->setType('bool')->v?'':'autofocus'?> name=CmsPage<?=$Cont?> value="<?=hee($search)?>" placeholder="<?=L('suchen')?>..." type=search>
		<button>suchen</button>
	</form>
	<?php } ?>
	<div data-part=res><?php include $Cont->modPath.'parts/res.php' ?></div>
</div>
