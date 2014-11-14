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
<?php echo $column_left ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-mollie" data-toggle="tooltip" title="<?php clean_echo($button_save) ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $cancel ?>" data-toggle="tooltip" title="<?php clean_echo($button_cancel) ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
			<h1><?php clean_echo($heading_title) ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger">
			<i class="fa fa-exclamation-circle"></i>
			<?php clean_echo($error_warning) ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } elseif (!mb_strlen($mollie_api_key)) { ?>
		<div class="alert alert-info">
			<i class="fa fa-info-circle"></i>
			<?php echo $help_view_profile ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php clean_echo($text_edit) ?></h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form-mollie" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php clean_echo($help_status) ?>"><?php clean_echo($entry_status) ?></span></label>

						<div class="col-sm-10">
							<select name="mollie_ideal_status" id="input-status" class="form-control">
								<?php if ($mollie_ideal_status) { ?>
								<option value="1" selected="selected"><?php clean_echo($text_enabled) ?></option>
								<option value="0"><?php clean_echo($text_disabled) ?></option>
								<?php } else { ?>
								<option value="1"><?php clean_echo($text_enabled) ?></option>
								<option value="0" selected="selected"><?php clean_echo($text_disabled) ?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="form-group required">
						<label class="col-sm-2 control-label" for="mollie_api_key"><span data-toggle="tooltip" title="<?php clean_echo($help_api_key) ?>"><?php clean_echo($entry_api_key) ?></span></label>

						<div class="col-sm-10">
							<input type="text" name="mollie_api_key" value="<?php clean_echo($mollie_api_key) ?>" placeholder="live_..." id="mollie_api_key" class="form-control"/>
							<?php if ($error_api_key) { ?>
							<div class="text-danger"><?php clean_echo($error_api_key) ?></div>
							<?php } ?>
						</div>
					</div>

					<div class="form-group required">
						<label class="col-sm-2 control-label" for="mollie_ideal_description"><span data-toggle="tooltip" title="<?php clean_echo($help_description) ?>"><?php clean_echo($entry_description) ?></span></label>

						<div class="col-sm-10">
							<input type="text" name="mollie_ideal_description" value="<?php clean_echo($mollie_ideal_description) ?>" id="mollie_ideal_description" class="form-control"/>
							<?php if ($error_description) { ?>
							<div class="text-danger"><?php clean_echo($error_description) ?></div>
							<?php } ?>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-sort-order"><?php clean_echo($entry_sort_order) ?></label>

						<div class="col-sm-10">
							<input type="text" name="mollie_ideal_sort_order" value="<?php clean_echo($mollie_ideal_sort_order) ?>" placeholder="<?php clean_echo($entry_sort_order) ?>" id="input-sort-order" class="form-control"/>
						</div>
					</div>

					<fieldset>
						<legend><?php clean_echo($title_payment_status) ?></legend>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="mollie_ideal_failed_status_id"><?php clean_echo($entry_failed_status) ?></label>

							<div class="col-sm-10">
								<select name="mollie_ideal_failed_status_id" id="mollie_ideal_failed_status_id" class="form-control">
									<?php foreach ($order_statuses as $order_status): ?>
									<?php if ($order_status['order_status_id'] == $mollie_ideal_failed_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
									<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
									<?php endif ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="mollie_ideal_canceled_status_id"><?php clean_echo($entry_canceled_status) ?></label>

							<div class="col-sm-10">
								<select name="mollie_ideal_canceled_status_id" id="mollie_ideal_canceled_status_id" class="form-control">
									<?php foreach ($order_statuses as $order_status): ?>
									<?php if ($order_status['order_status_id'] == $mollie_ideal_canceled_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
									<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
									<?php endif ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="mollie_ideal_expired_status_id"><?php clean_echo($entry_expired_status) ?></label>

							<div class="col-sm-10">
								<select name="mollie_ideal_expired_status_id" id="mollie_ideal_expired_status_id" class="form-control">
									<?php foreach ($order_statuses as $order_status): ?>
									<?php if ($order_status['order_status_id'] == $mollie_ideal_expired_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
									<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
									<?php endif ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="mollie_ideal_pending_status_id"><?php clean_echo($entry_pending_status) ?></label>

							<div class="col-sm-10">
								<select name="mollie_ideal_pending_status_id" id="mollie_ideal_pending_status_id" class="form-control">
									<?php foreach ($order_statuses as $order_status): ?>
									<?php if ($order_status['order_status_id'] == $mollie_ideal_pending_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
									<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
									<?php endif ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="mollie_ideal_processing_status_id"><?php clean_echo($entry_processing_status) ?></label>

							<div class="col-sm-10">
								<select name="mollie_ideal_processing_status_id" id="mollie_ideal_processing_status_id" class="form-control">
									<?php foreach ($order_statuses as $order_status): ?>
									<?php if ($order_status['order_status_id'] == $mollie_ideal_processing_status_id): ?>
									<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
									<?php else: ?>
									<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
									<?php endif ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend><?php clean_echo($title_mod_about) ?></legend>

						<div class="form-group">
							<label class="col-sm-2 control-label"><?php clean_echo($entry_module) ?></label>

							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $entry_version ?></p>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label"><?php clean_echo($entry_status) ?></label>

							<div class="col-sm-10">
								<p class="form-control-static">
									<?php
										if (is_array($entry_mstatus)) {
											foreach ($entry_mstatus as $file) {
												printf('%s: "%s"<br />', $error_file_missing, $file);
											}
										} else {
											echo $entry_mstatus;
										}
									?>
								</p>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label"><?php clean_echo($entry_comm_status) ?></label>

							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $entry_cstatus ?></p>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label"><?php clean_echo($entry_support) ?></label>

							<div class="col-sm-10">
								<p class="form-control-static"><a href="https://www.mollie.nl/bedrijf/contact" target="_blank">Mollie B.V.</a></p>
							</div>
						</div>

						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<a href="https://www.mollie.nl/" target="_blank"><img src="https://www.mollie.nl/images/badge-powered-medium.png" width="135" height="87" border="0" alt="Mollie"/></a><br/><br/>
								&copy; 2004-<?php echo date('Y') ?> Mollie B.V. <?php clean_echo($footer_text) ?>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
<?php echo $footer ?>
