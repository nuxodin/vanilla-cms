<?php
namespace qg;

global $openPageNodes;

G()->SET['cms']['admin']['openPageNodes']->custom();
G()->SET['cms']['admin']['rootPageNode']->custom();

// openNodes
$openPageNodes = array_flip(explode(',', G()->SET['cms']['admin']['openPageNodes'] ));
if (isset($_GET['opns'])) {
	foreach ($_GET['opns'] as $p => $v) {
		if ($v == '1') {
			$openPageNodes[$p] = randString(18);
		} else {
			unset($openPageNodes[$p]);
		}
	}
	G()->SET['cms']['admin']['openPageNodes']->setUser(implode(',', array_flip($openPageNodes)));
}
if (isset($vars['toggleOpen'])) {
	$p = $vars['toggleOpen'];
	if ($vars['value'] == '1') {
		$openPageNodes[$p] = randString(18);
	} else {
		unset($openPageNodes[$p]);
	}
	G()->SET['cms']['admin']['openPageNodes']->setUser(implode(',', array_flip($openPageNodes)));
}
// rootNode
if (isset($_GET['rp'])) {
	G()->SET['cms']['admin']['rootPageNode']->setUser((int)$_GET['rp']);
}
$rootPageNode  = (int)G()->SET['cms']['admin']['rootPageNode']->v ?: 1;

return ['rootPageNode' => $rootPageNode];
