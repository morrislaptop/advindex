<?php
	if ( isset($include) ) {
		$cols = $include;
	}
	else {
		$cols = $advindex->cols();
		if ( isset($exclude) ) {
			$cols = array_diff($cols, $exclude);
		}
	}
?>
<tr>
	<?php
		$first = true;
		$last = end($cols);
		foreach ($cols as $col)
		{
			$sortOptions = array();
			$key = null;
			if ( isset($$col) ) {
				$options = $$col;
				if ( isset($options['label']) ) {
					$key = $col;
					$col = $options['label'];
					unset($options['label']);
				}
			}

			$class = '';
			if ( $first ) {
				$class = 'headerLeft';
				$first = false;
			}
			?>
			<th class="<?php echo $class; ?>"><?php echo $paginator->sort($col, $key, $sortOptions);?></th>
			<?php
		}
	?>
	<th class="headerRight actions"><?php __('Actions');?></th>
</tr>
<tr class="filter">
	<?php
		foreach ($cols as $col)
		{
			$filterOptions = array();
			if ( isset($options[$col]['filter']) ) {
				$filterOptions = $options[$col]['filter'];
			}
			?>
			<td><?php echo $advindex->filter($col, $filterOptions); ?></td>
			<?php
		}
	?>
	<td><?php echo $advindex->search(); ?></td>
</tr>