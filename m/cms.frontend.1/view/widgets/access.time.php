<?php
namespace qg;
$format = "%d.%m.%Y %H:%M";
?>
<table class="-styled -noborder qgCmsFront1AccessTimeManager" pid=<?=$Cont?>>
	<tbody style="vertical-align:middle">
	<tr>
		<td style="width:100px"> <?=L('von')?>:
		<td style="padding-left:0">
			<?php
			$value = $Cont->vs['online_start'] ? strftime($format, $Cont->vs['online_start']) : '';
			?>
			<span class=-accessTimeBtns>
				<button class=-start_always  <?=$Cont->vs['online_start']==='0' ? 'disabled':''?>><?=L('unbeschränkt')?></button>
				<button class=-start_inherit <?=$Cont->vs['online_start']===null ? 'disabled':''?>><?=L('vererbt')?></button>
				<button class=-start_now><?=L('terminiert')?></button>
				<input  type=qg-date class=-start value="<?=$value?>" format="<?=$format?>" style="width:122px">
			</span>
	<tr>
		<td> <?=L('bis')?>:
		<td style="padding-left:0">
			<?php
			$value = $Cont->vs['online_end'] ? strftime($format, $Cont->vs['online_end']) : '';
			?>
			<span  class=-accessTimeBtns>
				<button class=-end_always <?=$Cont->vs['online_end']==='0' ? 'disabled':''?>><?=L('unbeschränkt')?></button>
				<button class=-end_inherit <?=$Cont->vs['online_end']===null ? 'disabled':''?>><?=L('vererbt')?></button>
				<button class=-end_now ><?=L('terminiert')?></button>
				<input  class=-end type=qg-date value="<?=$value?>" format="<?=$format?>" style="width:122px">
			</span>
</table>
<style>
body .-accessTimeBtns {
	display:inline-flex;
}
body .-accessTimeBtns > * {
	border-radius:0;
	margin:0 -.5px;
}
body .-accessTimeBtns > [type='qg-date'] {
	background:var(--cms-dark);
	border-color:var(--cms-dark);
	color:#fff;
}
</style>
