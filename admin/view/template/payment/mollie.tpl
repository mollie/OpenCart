<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-mollie" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-mollie" class="form-horizontal">
			<?php $api_key = false; ?>
			<?php foreach ($shops as $shop) { ?>
				<?php if ($error_warning) { ?>
				<div class="alert alert-danger alert-dismissable">
					<i class="fa fa-exclamation-circle"></i>
					<?php echo $shop['name']; ?>: <?php echo $error_warning; ?>
					<button type="button" class="close" data-dismiss="alert">&times;
					</button>
				</div>
				<?php } ?>
				<?php if (!empty($shop[$code . '_api_key'])) { ?>
					<?php $api_key = true; ?>
				<?php } ?>
			<?php } ?>
			<?php if(!$api_key) { ?>
			<div class="alert alert-info alert-dismissable">
				<i class="fa fa-info-circle"></i> <?php echo $help_view_profile; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
				</div>
				<div class="panel-body">
					<ul class="nav nav-tabs">
						<?php foreach ($shops as $shop) { ?>
							<li class="<?php echo $shop['store_id'] === 0 ? 'active' : ''; ?>"><a data-toggle="tab" href="#store<?php echo $shop['store_id']; ?>"><?php echo $shop['name']; ?></a></li>
						<?php } ?>
					</ul>

					<div class="tab-content">
						<?php foreach ($shops as $shop) { ?>
						<div id="store<?php echo $shop['store_id']; ?>" class="tab-pane fade in <?php echo $shop['store_id'] === 0 ? 'active' : ''; ?>">
							<ul id="tabs<?php echo $shop['store_id']; ?>" class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#payment-methods-<?php echo $shop['store_id']; ?>"><?php echo $entry_payment_method; ?></a></li>
								<li><a data-toggle="tab" href="#payment-statuses-<?php echo $shop['store_id']; ?>"><?php echo $title_payment_status; ?></a></li>
								<li><a data-toggle="tab" href="#mollie-options-<?php echo $shop['store_id']; ?>"><?php echo $title_global_options; ?></a></li>
								<li><a data-toggle="tab" href="#about-module-<?php echo $shop['store_id']; ?>"><?php echo $title_mod_about; ?></a></li>
								<li><a data-toggle="tab" href="#support-<?php echo $shop['store_id']; ?>">Support</a></li>
							</ul>

							<div class="tab-content">
								<div id="payment-methods-<?php echo $shop['store_id']; ?>" class="tab-pane fade in active">
									<div class="form-group">
										<div class="col-sm-4"><strong><?php echo $entry_payment_method; ?></strong></div>
										<div class="col-sm-3"><strong><?php echo $entry_activate; ?></strong></div>
										<div class="col-sm-3"><strong><?php echo $entry_geo_zone; ?></strong></div>
										<div class="col-sm-2"><strong><?php echo $entry_sort_order; ?></strong></div>
									</div>
									<?php foreach ($store[$shop['store_id'] . '_' . $code . '_payment_methods'] as $module_id => $payment_method) { ?>
									<div class="form-group">
										<div class="col-sm-4">
											<img src="<?php echo $payment_method['icon']; ?>" width="20" style="float:left; margin-right:1em; margin-top:-3px"/>
											<?php echo $payment_method['name']; ?>
										</div>
										<div class="col-sm-3">
											<?php $show_checkbox = true ?>
											<?php if (empty($shop[$code . '_api_key']) || !empty($shop['error_api_key'])) { ?>
											<?php $show_checkbox = false ?>
											<?php echo $text_missing_api_key; ?>
											<?php } elseif (!$payment_method['allowed']) { ?>
											<?php $show_checkbox = false ?>
											<?php echo $text_activate_payment_method; ?>
											<?php } ?>
											<input type="checkbox" name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_<?php echo $module_id; ?>_status" <?php echo $payment_method['status'] ? 'checked' : ''; ?> style="cursor:pointer;<?php echo !$show_checkbox ? 'display:none;' : ''; ?>" />
										</div>
										<div class="col-sm-3">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_<?php echo $module_id; ?>_geo_zone" class="form-control">
												<option value="0"><?php echo $text_all_zones; ?></option>
												<?php foreach ($geo_zones as $geo_zone) { ?>
													<?php if ($geo_zone['geo_zone_id'] === $shop[$code . '_' . $module_id . '_' . 'geo_zone']) { ?>
													<option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
										<div class="col-sm-2">
											<input type="text" name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_<?php echo $module_id; ?>_sort_order" value="<?php echo $shop[$code . '_' . $module_id . '_' . 'sort_order']; ?>" class="form-control" style="text-align:right; max-width:60px"/>
										</div>
									</div>
									<?php } ?>
								</div>

								<div id="payment-statuses-<?php echo $shop['store_id']; ?>" class="tab-pane fade in">
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_pending_status_id"><?php echo $entry_pending_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_pending_status_id" id="<?php echo $code; ?>_ideal_pending_status_id" class="form-control">
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_pending_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_failed_status_id"><?php echo $entry_failed_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_failed_status_id" id="<?php echo $code; ?>_ideal_failed_status_id" class="form-control">
												<?php if (empty($shop[$code . '_ideal_failed_status_id'])) { ?>
												<option value="0" selected="selected"><?php echo $text_no_status_id; ?></option>
												<?php } else { ?>
												<option value="0"><?php echo $text_no_status_id; ?></option>
												<?php } ?>
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_failed_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_canceled_status_id"><?php echo $entry_canceled_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_canceled_status_id" id="<?php echo $code; ?>_ideal_canceled_status_id" class="form-control">
												<?php if (empty($shop[$code . '_ideal_canceled_status_id'])) { ?>
												<option value="0" selected="selected"><?php echo $text_no_status_id; ?></option>
												<?php } else { ?>
												<option value="0"><?php echo $text_no_status_id; ?></option>
												<?php } ?>
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_canceled_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_expired_status_id"><?php echo $entry_expired_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_expired_status_id" id="<?php echo $code; ?>_ideal_expired_status_id" class="form-control">
												<?php if (empty($shop[$code . '_ideal_expired_status_id'])) { ?>
												<option value="0" selected="selected"><?php echo $text_no_status_id; ?></option>
												<?php } else { ?>
												<option value="0"><?php echo $text_no_status_id; ?></option>
												<?php } ?>
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_expired_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_processing_status_id"><?php echo $entry_processing_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_processing_status_id" id="<?php echo $code; ?>_ideal_processing_status_id" class="form-control">
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_processing_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_ideal_shipping_status_id"><?php echo $entry_shipping_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_shipping_status_id" id="<?php echo $code; ?>_ideal_shipping_status_id" class="form-control">
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_ideal_shipping_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>

								<div id="mollie-options-<?php echo $shop['store_id']; ?>" class="tab-pane fade in">
									<div class="form-group <?php echo $api_required ? 'required' : '' ?>">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_api_key"><span data-toggle="tooltip" title="<?php echo $help_api_key; ?>"><?php echo $entry_api_key; ?></span></label>
										<div class="col-sm-10">
											<div class="input-group">
												<span class="input-group-addon"><?php echo ($shop[$code . '_api_key']) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-minus"></i>' ;?></span>
												<input type="text" name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_api_key" value="<?php echo $shop[$code . '_api_key']; ?>" placeholder="live_..." id="<?php echo $code; ?>_api_key" class="form-control" store="<?php echo $shop['store_id']; ?>" <?php echo $shop['store_id']; ?>-data-payment-mollie-api-key/>
											</div>
											<?php if ($shop['error_api_key']) { ?>
											<div class="text-danger"><?php echo $shop['error_api_key']; ?></div>
											<?php } ?>
										</div>
									</div>
									<div class="form-group required">
										<label class="col-sm-2 control-label" for="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_description"><span data-toggle="tooltip" title="<?php echo $help_description; ?>"><?php echo $entry_description; ?></span></label>
										<div class="col-sm-10">
											<input type="text" name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_description" value="<?php echo $shop[$code . '_ideal_description']; ?>" id="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_description" class="form-control"/>
											<?php if ($shop['error_description']) { ?>
											<div class="text-danger"><?php echo $shop['error_description']; ?></div>
											<?php } ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $help_show_icons; ?>"><?php echo $entry_show_icons; ?></span></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_show_icons" id="input-status" class="form-control">
												<?php if ($shop[$code . '_show_icons']) { ?>
												<option value="1" selected="selected"><?php echo $text_yes; ?></option>
												<option value="0"><?php echo $text_no; ?></option>
												<?php } else { ?>
												<option value="1"><?php echo $text_yes; ?></option>
												<option value="0" selected="selected"><?php echo $text_no; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $help_show_order_canceled_page; ?>"><?php echo $entry_show_order_canceled_page; ?></span></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_show_order_canceled_page" id="input-status" class="form-control">
												<?php if ($shop[$code . '_show_order_canceled_page']) { ?>
												<option value="1" selected="selected"><?php echo $text_yes; ?></option>
												<option value="0"><?php echo $text_no; ?></option>
												<?php } else { ?>
												<option value="1"><?php echo $text_yes; ?></option>
												<option value="0" selected="selected"><?php echo $text_no; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $help_shipment; ?>"><?php echo $entry_shipment; ?></span></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_create_shipment" id="<?php echo $shop['store_id']; ?>-create-shipment" class="form-control">
												<?php if ($shop[$code . '_create_shipment'] == 1) { ?>
												<option value="1" selected="selected"><?php echo $text_create_shipment_automatically; ?></option>
												<option value="2"><?php echo $text_create_shipment_on_status; ?></option>
												<?php if($is_order_complete_status) { ?>
												<option value="3"><?php echo $text_create_shipment_on_order_complete; ?></option>
												<?php } ?>
												<?php } elseif ($shop[$code . '_create_shipment'] == 2) { ?>
												<option value="1"><?php echo $text_create_shipment_automatically; ?></option>
												<option value="2" selected="selected"><?php echo $text_create_shipment_on_status; ?></option>
												<?php if($is_order_complete_status) { ?>
												<option value="3"><?php echo $text_create_shipment_on_order_complete; ?></option>
												<?php } ?>
												<?php } else { ?>
												<option value="1"><?php echo $text_create_shipment_automatically; ?></option>
												<option value="2" selected="selected"><?php echo $text_create_shipment_on_status; ?></option>
												<option value="3" selected="selected"><?php echo $text_create_shipment_on_order_complete; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group" id="<?php echo $shop['store_id']; ?>-create-shipment-status">
										<label class="col-sm-2 control-label" for="<?php echo $code; ?>_create_shipping_status_id"><?php echo $entry_create_shipment_status; ?></label>
										<div class="col-sm-10">
											<select name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_create_shipment_status_id" id="<?php echo $code; ?>_create_shipment_status_id" class="form-control">
												<?php foreach ($order_statuses as $order_status) { ?>
													<?php if ($order_status['order_status_id'] == $shop[$code . '_create_shipment_status_id']) { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>

								<div id="about-module-<?php echo $shop['store_id']; ?>" class="tab-pane fade in">
									<div class="form-group">
										<label class="col-sm-2 control-label"><?php echo $entry_module; ?></label>
										<div class="col-sm-10">
											<p class="form-control-static"><?php echo $entry_version; ?></p>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label"><?php echo $entry_comm_status; ?></label>
										<div class="col-sm-10">
											<p class="form-control-static" data-communication-status><?php echo $shop['entry_cstatus']; ?></p>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-offset-2 col-sm-10">
											<a href="https://www.mollie.com/" target="_blank"><img src=" https://www.mollie.com/images/logo.png" border="0" alt="Mollie"/></a><br/><br/>
											&copy; 2004-<?php echo date('Y'); ?> Mollie
											B.V. <?php echo $footer_text; ?>
										</div>
									</div>
								</div>

								<div id="support-<?php echo $shop['store_id']; ?>" class="tab-pane fade in">
									<fieldset>
										<legend>Module by Quality Works - Technical Support</legend>
										<div class="row">
											<label class="col-sm-2">Quality Works</label>
											<div class="col-sm-10">Tel: +31(0)85 7430150<br>E-mail: <a href="mailto:support.mollie@qualityworks.eu">support.mollie@qualityworks.eu</a><br>Internet: <a href="https://www.qualityworks.eu" target="_blank">www.qualityworks.eu</a>
											</div>
										</div>
										<legend>Mollie - Support</legend>
									</fieldset>
									<fieldset>
										<div class="row">
											<label class="col-sm-2">Mollie B.V.</label>
											<div class="col-sm-10">
												<a href="https://www.mollie.com/bedrijf/contact" target="_blank">Contact</a>
											</div>
										</div>
									</fieldset>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="panel-body">
	            <?php if($update_url){ ?>
	            <a href="<?php echo $update_url; ?>" class="btn btn-success"><?php echo $button_update; ?></a>
	            <?php } ?>
	        </div>
		</form>
	</div>
</div>
<?php echo $footer; ?>

<script type="text/javascript">
	(function () {
		var timeout, xhr;
		var api_check_url = $('<div/>').html('<?php echo $api_check_url; ?>').text();

		function checkIfAPIKeyIsValid(key) {
			if (xhr) xhr.abort();

			xhr = $.get(api_check_url + '&key=' + key);

			return xhr;
		}

		function validateAPIKey(value, $icon_container, store_id) {
			if (value === '') {
				updateIcon($icon_container, 'fa-minus', null, true);
				return;
			}

			clearTimeout(timeout);
			timeout = setTimeout(function () {
				updateIcon($icon_container, 'fa-spinner fa-spin', null);

				checkIfAPIKeyIsValid(value).then(function (response) {
					if (response.valid) {
						updateIcon($icon_container, 'fa-check');
						saveAPIKey(value, store_id);
					} else if (response.invalid) {
						updateIcon($icon_container, 'fa-times', response.message);
					} else if (response.error) {
						updateIcon($icon_container, 'fa-exclamation-triangle', response.message);
					}
				});
			}, 400);
		}

		function saveAPIKey(key, store_id) {
			var data = {
                'api_key': key,
                'store_id': store_id
            };
			$.ajax({
			  type: "POST",
			  url: 'index.php?route=payment/mollie_<?php echo $module_name; ?>/saveAPIKey&<?php echo $token; ?>',
			  data: data,
			  success: function() {
			  	window.location.reload();
			  }
			});
		}

		function updateIcon($container, className, message, dontClearErrors) {
			var colorClass = '';
			var classPerIcon = {
				'fa-check': 'text-success',
				'fa-times': 'text-danger',
				'fa-exclamation-triangle': 'text-danger'
			};

			if (classPerIcon[className]) {
				colorClass += ' ' + classPerIcon[className];
			}

			var icon = '<i class="fa ' + className + colorClass + '"></i>';

			$container.html(icon);
			$container.popover('destroy');

			if (message) {
				$container.popover({
					content: '<span class="' + colorClass + '">' + message + '</span>',
					html: true,
					placement: 'top',
					trigger: 'hover manual'
				});

				if ($container.is(':visible')) {
					$container.popover('show');
				}
			}

			if (!message && -1 !== className.indexOf('spinner')) {
				message = icon;
			}

			if (!dontClearErrors && $container.closest('.form-group').hasClass('has-error')) {
				$container.parent().next().remove();
				$container.closest('.form-group').removeClass('has-error');
			}

			$container.closest('.tab-content').find('[data-communication-status]').html('<span class="' + colorClass + '">' + (message || 'OK') + '</span>');
		}

		<?php foreach($shops as $shop) { ?>

			$('[<?php echo $shop["store_id"] ?>-data-payment-mollie-api-key]').on('keyup', function () {
				validateAPIKey(this.value, $(this).siblings('.input-group-addon'), $(this).attr('store'));
			});

			var elem = document.getElementById('<?php echo $shop["store_id"] ?>-create-shipment');
			var hiddenDiv = document.getElementById('<?php echo $shop["store_id"] ?>-create-shipment-status');
			if(elem.value == 2) {
				hiddenDiv.style.display = "block";
			} else {
				hiddenDiv.style.display = "none";
			}
			
			elem.onchange = function(){
				var hiddenDiv = document.getElementById('<?php echo $shop["store_id"] ?>-create-shipment-status');

			    if(this.value == 2) {
					hiddenDiv.style.display = "block";
				} else {
					hiddenDiv.style.display = "none";
				}
			};
			
			$('.settings').click(function(){
		      $('#tabs<?php echo $shop["store_id"] ?> a[href=#mollie-options-<?php echo $shop["store_id"] ?>]').tab('show');
		    });

		<?php } ?>

	})();
</script>
