<?php
	if ( !isset($cols) ) {
		$cols = count($advindex->cols());
	}
?>
<tfoot>
	<tr>
		<td colspan="<?php echo $cols; ?>" class="footerLeft">
			<div class="show">
				Show <?php echo $advindex->perPage(); ?> per page
				<span>|</span>
			</div>
			<div class="paging">
				<ul>
					<li class="previous"><?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?></li>
				 	<?php
				 		$defaultModel = $paginator->defaultModel();
				 		$params = $paginator->params['paging'][$defaultModel];
				 		$between = '<li class="dots">...</li>';
				 		echo $paginator->numbers(array('separator' => null, 'tag' => 'li', 'first' => 1, 'last' => 1, 'afterFirst' => $between, 'beforeLast' => $between));
				 	?>
				 	<li class="next"><?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?></li>
				</ul>
			</div>
			<span>|</span>
			<?php
			echo $paginator->counter(array(
			'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
			));
			?>
		</td>
		<td class="footerRight">&nbsp;</td>
	</tr>
</tfoot>