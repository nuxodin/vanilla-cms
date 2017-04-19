<?php
namespace qg;

if (isset($_GET['module'])) return include 'module.php';

module::sync();

?>
<div class=beBox>
	<div class=-head>Module</div>
	<div class=-body>
		<button class=btnUpdateAll style="float:right">Alle updaten</button>
		<form id=searchForm>
			<input type=search placeholder="<?=hee(L('suchen'))?>..." name=search style="width:300px" autofocus>
			<label><input type=checkbox name=installed checked> Installiert?</label>
		</form>
	</div>
	<table class=c1-style>
		<thead>
			<tr>
				<th style="width:250px"> Name
				<!--th style="width:100px"-->
				<th style="width:10px">
				<th style="width:25px; text-align:center"> Access
				<th style="width:60px">
				<th style="width:20px">
				<th style="width:20px">
				<th style="width:60px;"> Local:
				<th style="width:140px"> Server:
				<th style="width:60px"> Version:
				<?php if (Usr()->superuser) { ?>
					<th> Export
					<th style="width:20px">
				<?php } ?>
				<th>Size:
		<tbody data-part=list>
			<?php include $Cont->modPath.'parts/list.php' ?>
	</table>
</div>
