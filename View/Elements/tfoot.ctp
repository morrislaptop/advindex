<?php
	if ( !isset($cols) ) {
		$cols = count($advindex->cols());
	}
?>
<tr>
	<td colspan="<?php echo $cols; ?>" class="footerLeft">
		<div class="show">
			Show <?php echo $advindex->perPage(); ?> per page
		</div>
		<p><?php
		echo $this->Paginator->counter(array(
			'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
		));
		?></p>
		<div class="paging">
		<?php echo "\t" . $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class' => 'disabled')) . "\n";?>
		 | <?php echo $this->Paginator->numbers() . "\n"?>
		<?php echo "\t ". $this->Paginator->next(__('next', true) .' >>', array(), null, array('class' => 'disabled')) . "\n";?>
		</div>
	</td>
	<td class="footerRight">&nbsp;</td>
</tr>