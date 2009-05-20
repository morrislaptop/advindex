<?php
	$linkId = 'toggle' . intval(mt_rand());
	$url = array('action' => 'toggle', $id, $field);
	echo $html->link($html->image($value ? 'on.png' : 'off.png'), $url, array('id' => $linkId), false, false);
	echo $javascript->codeBlock('
		$("#' . $linkId . '").click(function() {
			$(this).parent().load(this.href);
			return false;
		});
	');
?>