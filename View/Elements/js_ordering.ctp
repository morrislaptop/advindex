<?php
	// added to row index so the order is preserved over pages...
	$offset = $paginator->params['paging'][$model]['options']['limit'] * ($paginator->params['paging'][$model]['page'] - 1);

	echo $javascript->link('/advindex/js/jquery.tablednd_0_5', false);
	echo $javascript->codeBlock('
$(document).ready(function() {
	$("div.index table").tableDnD({
		url: "' . Router::url(array('action' => 'save_order')) . '",
		originalOrder: null,
		onDragClass: "myDragClass",
		onDragStart: function(table, row) {
			originalOrder = jQuery.inArray(row, $("tbody tr", table));
		},
		onDrop: function(table, row) {
			var newOrder = jQuery.inArray(row, $("tbody tr", table));
			newOrder += ' . $offset . ';
			if (newOrder != originalOrder) {
				$.post(this.url+"/"+row.id, { "data[' . $model . '][order]": newOrder });
			}
		},
		dragHandle: "dragHandle"
	});
});
	', array('inline' => false));
?>