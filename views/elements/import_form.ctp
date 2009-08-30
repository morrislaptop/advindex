<?php
	$sessionKey = 'Advindex.' . $model . '.import';
	$import = $session->read($sessionKey);
	$session->del($sessionKey);

	if ( $import )
	{
		?>
		<div id="flashMessage" class="message">
			<p>Results of your import:</p>
			<ul>
				<li><?php echo $import['created']; ?> <?php __n('record', 'records', $import['created']); ?> created</li>
				<li><?php echo $import['updated']; ?> <?php __n('record', 'records', $import['created']); ?> updated</li>
				<?php
					if ( $import['errors'] )
					{
						$errorsCount = count($import['errors']);
						?>
						<li>
							<?php echo $errorsCount; ?> <?php __n('error', 'errors', $errorsCount); ?> occured:
							<ul>
								<?php
									foreach ($import['errors'] as $line => $whys)
									{
										?>
										<li>
											Line <?php echo $line; ?>
											<?php
												foreach ($whys as $why)
												{
													?>
													<li><?php echo $why; ?></li>
													<?php
												}
											?>
										</li>
										<?php
									}
								?>
							</ul>
						</li>
						<?php
					}
				?>
			</ul>
		</div>
		<?php
	}

?>
<?php echo $form->create($model, array('action' => 'import', 'type' => 'file', 'style' => 'border: 1px solid #999; display: none; padding: 10px; margin: 10px;')); ?>
	<?php echo $form->checkbox('truncate'); ?>
	<label style="display: inline; width: auto; float: none;">Empty table before import?</label>
	<?php echo $form->input('csv', array('type' => 'file', 'label' => false, 'div' => false)); ?>
	<?php echo $form->submit('Upload', array('div' => false, 'label' => false)); ?>
<?php echo $form->end(); ?>