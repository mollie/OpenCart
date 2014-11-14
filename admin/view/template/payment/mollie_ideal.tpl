<?php
	if (!function_exists('clean_echo'))
	{
		function clean_echo ($string)
		{
			echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
	}
?>

<?php echo $header ?>

<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']) ?><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a>
		<?php endforeach ?>
	</div>

	<?php if ($error_warning): ?>
		<div class="warning"><?php clean_echo($error_warning) ?></div>
	<?php elseif (!mb_strlen($mollie_api_key)): ?>
		<div class="attention"><?php echo $help_view_profile ?></div>
	<?php endif ?>

	<div class="box">
		<div class="left"></div>
		<div class="right"></div>

		<div class="heading">
			<h1><img src="view/image/payment.png" alt="" /> <?php clean_echo($heading_title) ?></h1>
			<div class="buttons"><a onclick="(window.jQuery || window.$)('#form').submit()" class="button"><span><?php clean_echo($button_save) ?></span></a><a href="<?php echo $cancel ?>" class="button"><span><?php clean_echo($button_cancel) ?></span></a></div>
		</div>

		<div class="content">
			<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form">
				<table class="form">
					<tr>
						<td><?php printf('%s:<br /><span class="help">%s</span>', $entry_status, $help_status) ?></td>
						<td>
							<select name="mollie_ideal_status">
								<option value="1" <?php if ($mollie_ideal_status == 1) { echo 'selected="selected"'; } ?>><?php clean_echo($text_enabled) ?></option>
								<option value="0" <?php if ($mollie_ideal_status == 0) { echo 'selected="selected"'; } ?>><?php clean_echo($text_disabled) ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php printf('%s:<br /><span class="help">%s</span>', $entry_api_key, $help_api_key) ?></td>
						<td><input type="text" name="mollie_api_key" value="<?php clean_echo($mollie_api_key) ?>" style="width:240px" placeholder="live_..."/>
							<br/>
							<?php if (!empty($error_api_key)): ?>
								<span class="error"><?php clean_echo($error_api_key) ?></span>
							<?php endif ?>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php printf('%s:<br /><span class="help">%s</span>', $entry_description, $help_description) ?></td>
						<td><input style="width:240px" maxlength="29" type="text" name="mollie_ideal_description" value="<?php clean_echo($mollie_ideal_description) ?>" />
							<br/>
							<?php if (!empty($error_description)): ?>
							<span class="error"><?php clean_echo($error_description) ?></span>
							<?php endif ?>
						</td>
					</tr>

					<tr>
						<td><?php clean_echo($entry_pending_status) ?>:</td>
						<td>
							<select name="mollie_ideal_pending_status_id">
								<?php foreach ($order_statuses as $order_status): ?>
								<?php if ($order_status['order_status_id'] == $mollie_ideal_pending_status_id): ?>
								<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
								<?php else: ?>
								<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
								<?php endif ?>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_failed_status) ?>:</td>
						<td>
							<select name="mollie_ideal_failed_status_id">
							<?php foreach ($order_statuses as $order_status): ?>
								<?php if ($order_status['order_status_id'] == $mollie_ideal_failed_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
								<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
								<?php endif ?>
							<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_canceled_status) ?>:</td>
						<td>
							<select name="mollie_ideal_canceled_status_id">
							<?php foreach ($order_statuses as $order_status): ?>
								<?php if ($order_status['order_status_id'] == $mollie_ideal_canceled_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
								<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
								<?php endif ?>
							<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_expired_status) ?>:</td>
						<td>
							<select name="mollie_ideal_expired_status_id">
							<?php foreach ($order_statuses as $order_status): ?>
								<?php if ($order_status['order_status_id'] == $mollie_ideal_expired_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
								<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
								<?php endif ?>
							<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_processing_status) ?>:</td>
						<td>
							<select name="mollie_ideal_processing_status_id">
							<?php foreach ($order_statuses as $order_status): ?>
								<?php if ($order_status['order_status_id'] == $mollie_ideal_processing_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
								<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
								<?php endif ?>
							<?php endforeach ?>
							</select>
						</td>
					</tr>

					<tr>
						<td><?php clean_echo($entry_sort_order) ?>:</td>
						<td><input type="text" name="mollie_ideal_sort_order" value="<?php clean_echo($mollie_ideal_sort_order ? $mollie_ideal_sort_order : 1) ?>" size="1" /></td>
					</tr>

					<tr>
						<td height="24"></td>
						<td></td>
					</tr>
					<tr>
						<td style="vertical-align: middle"><?php echo $entry_module ?>:</td>
						<td style="vertical-align: middle"><?php echo $entry_version ?></td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_mod_status) ?>:</td>
						<td>
							<?php
								if (is_array($entry_mstatus)) {
									foreach ($entry_mstatus as $file) {
										echo "Bestand bestaat niet: '$file'<br/>";
									}
								} else {
									echo $entry_mstatus;
								}
							?>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_comm_status) ?>:</td>
						<td>
							<?php echo $entry_cstatus ?>
						</td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_support) ?>:</td>
						<td>
							<a href="https://www.mollie.nl/bedrijf/contact" target="_blank">Mollie B.V.</a>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>

							<a href="https://www.mollie.nl/" target="_blank"><img src="https://www.mollie.nl/images/badge-powered-medium.png" width="135" height="87" border="0" alt="Mollie" /></a><br/><br/>
							&copy; 2004-<?php echo date('Y') ?> Mollie B.V. <?php clean_echo($footer_text) ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<script type="text/javascript">
		<!--
			$.tabs('.tabs a');
		//-->
		</script>
	</div>
</div>

<?php echo $footer ?>