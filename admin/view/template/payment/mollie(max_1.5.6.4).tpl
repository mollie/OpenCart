<?php echo $header; ?>
<div id="content">

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $i => $breadcrumb) { ?>
			<?php echo $i > 0 ? ':: ' : ''; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php $api_key = false; ?>
	<?php foreach ($shops as $shop) { ?>
		<?php if ($error_warning) { ?>
			<div class="success">
				<?php echo $shop['name']; ?>: <?php echo $error_warning; ?>
			</div>
		<?php } ?>
		<?php if (!empty($shop[$code . '_api_key'])) { ?>
			<?php $api_key = true; ?>
		<?php } ?>
	<?php } ?>
	<?php if(!$api_key) { ?>
	<div class="attention">
		 <?php echo $help_view_profile; ?>
	</div>
	<?php } ?>

	<div class="box">
		<div class="heading">
			<h1><img src="view/image/payment.png" alt=""><?php echo $heading_title; ?></h1>
			<div class="buttons">
				<a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a>
				<a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<div class="htabs">
				<?php foreach ($shops as $shop) { ?>
					<a class="<?php echo $shop['store_id'] === 0 ? 'active' : ''; ?>" href="#store<?php echo $shop['store_id']; ?>"><?php echo $shop['name']; ?></a>
				<?php } ?>
			</div>
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
				<?php foreach ($shops as $shop) { ?>
					<div id="store<?php echo $shop['store_id']; ?>" class="<?php echo $shop['store_id'] === 0 ? 'active' : ''; ?>">
						<div id="tabs<?php echo $shop['store_id']; ?>" class="vtabs">
							<a class="active" href="#payment-methods-<?php echo $shop['store_id']; ?>"><?php echo $entry_payment_method; ?></a>
							<a href="#payment-statuses-<?php echo $shop['store_id']; ?>"><?php echo $title_payment_status; ?></a>
							<a href="#mollie-options-<?php echo $shop['store_id']; ?>"><?php echo $title_global_options; ?></a>
							<a href="#about-module-<?php echo $shop['store_id']; ?>"><?php echo $title_mod_about; ?></a>
							<a href="#support-<?php echo $shop['store_id']; ?>">Support</a>
						</div>

						<div id="payment-methods-<?php echo $shop['store_id']; ?>" class="active vtabs-content">
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

						<div id="payment-statuses-<?php echo $shop['store_id']; ?>" class="vtabs-content">
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

						<div id="mollie-options-<?php echo $shop['store_id']; ?>" class="vtabs-content">
							<div class="form-group">
								<label class="col-sm-2 control-label" for="<?php echo $code; ?>_api_key"> <?php echo $api_required ? '<span class="required">*</span>' : '' ?> <?php echo $entry_api_key; ?><br /><span class="help"><?php echo $help_api_key; ?></span></label>
								<div class="col-sm-10">
									<div class="input-group message-block">
										<input type="text" name="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_api_key" value="<?php echo $shop[$code . '_api_key']; ?>" placeholder="live_..." id="<?php echo $code; ?>_api_key" class="form-control" store="<?php echo $shop['store_id']; ?>" <?php echo $shop['store_id']; ?>-data-payment-mollie-api-key/>
									</div>
									<?php if ($shop['error_api_key']) { ?>
									<div class="text-danger"><?php echo $shop['error_api_key']; ?></div>
									<?php } ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="<?php echo $shop['store_id']; ?>_<?php echo $code; ?>_ideal_description"><span class="required">*</span> <?php echo $entry_description; ?> <span class="help"><?php echo $help_description; ?></span></label>
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

						<div id="about-module-<?php echo $shop['store_id']; ?>" class="vtabs-content">
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

						<div id="support-<?php echo $shop['store_id']; ?>" class="vtabs-content">
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
				<?php } ?>
				<div class="panel-body">
		            <?php if($update_url){ ?>
		            <a href="<?php echo $update_url; ?>" class="btn btn-success"><?php echo $button_update; ?></a>
		            <?php } ?>
		        </div>
			</form>
		</div>
	</div>
</div>
<?php echo $footer; ?>
<script type="text/javascript"><!--
	$('.htabs a').tabs();
	$('.vtabs a').tabs();
	//--></script>
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
				updateIcon($icon_container, 'empty', null, true);
				return;
			}

			clearTimeout(timeout);
			timeout = setTimeout(function () {
				updateIcon($icon_container, 'loading', null);

				checkIfAPIKeyIsValid(value).then(function (response) {
					if (response.valid) {
						updateIcon($icon_container, 'success');
						saveAPIKey(value, store_id);
					} else if (response.invalid) {
						updateIcon($icon_container, 'attention', response.message);
					} else if (response.error) {
						updateIcon($icon_container, 'warning', response.message);
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
			$container.removeClass('success loading empty attention warning').addClass(className);

			if (!dontClearErrors) {
				$container.find('#key-message').remove();
			}

			if (message) {
				$container.append('<span id="key-message" class="error">' + message + '</span>');
			}

			$('[data-communication-status]').html('<span class="' + (message ? 'error' : 'text-success') + '">' + (message || 'OK') + '</span>');
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


<style>
  .input-group.message-block {
    padding: 0;
    margin-bottom: 0;
    border: 0 none;
    background-color: transparent;
    background-position: 160px 3px;
  }
  .text-success {
    color: #4cb64c;
  }
  fieldset {padding: 0;
		margin: 0;
		border: 0;
		min-width: 0;
    overflow: hidden;
	}
	fieldset legend {
		padding-bottom: 5px;
	}
	legend {
		display: block;
		width: 100%;
		padding: 0;
		margin-bottom: 18px;
		font-size: 19.5px;
		line-height: inherit;
		color: #333;
		border: 0;
		border-bottom: 1px solid #e5e5e5;
	}
	.form-group{display:block;overflow:auto;padding:10px;border-bottom: 1px dotted #CCCCCC;}.form-group * {vertical-align: top;}.form-group .form-control-static{margin: 0;}.form-group::after{content:'';display:block;clear:both;}
	.row{margin-left:-15px;margin-right:-15px}.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12{position:relative;min-height:1px;padding-left:15px;padding-right:15px;box-sizing:border-box;}.col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12{float:left}.col-xs-12{width:100%}.col-xs-11{width:91.66666667%}.col-xs-10{width:83.33333333%}.col-xs-9{width:75%}.col-xs-8{width:66.66666667%}.col-xs-7{width:58.33333333%}.col-xs-6{width:50%}.col-xs-5{width:41.66666667%}.col-xs-4{width:33.33333333%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}.col-xs-pull-12{right:100%}.col-xs-pull-11{right:91.66666667%}.col-xs-pull-10{right:83.33333333%}.col-xs-pull-9{right:75%}.col-xs-pull-8{right:66.66666667%}.col-xs-pull-7{right:58.33333333%}.col-xs-pull-6{right:50%}.col-xs-pull-5{right:41.66666667%}.col-xs-pull-4{right:33.33333333%}.col-xs-pull-3{right:25%}.col-xs-pull-2{right:16.66666667%}.col-xs-pull-1{right:8.33333333%}.col-xs-pull-0{right:auto}.col-xs-push-12{left:100%}.col-xs-push-11{left:91.66666667%}.col-xs-push-10{left:83.33333333%}.col-xs-push-9{left:75%}.col-xs-push-8{left:66.66666667%}.col-xs-push-7{left:58.33333333%}.col-xs-push-6{left:50%}.col-xs-push-5{left:41.66666667%}.col-xs-push-4{left:33.33333333%}.col-xs-push-3{left:25%}.col-xs-push-2{left:16.66666667%}.col-xs-push-1{left:8.33333333%}.col-xs-push-0{left:auto}.col-xs-offset-12{margin-left:100%}.col-xs-offset-11{margin-left:91.66666667%}.col-xs-offset-10{margin-left:83.33333333%}.col-xs-offset-9{margin-left:75%}.col-xs-offset-8{margin-left:66.66666667%}.col-xs-offset-7{margin-left:58.33333333%}.col-xs-offset-6{margin-left:50%}.col-xs-offset-5{margin-left:41.66666667%}.col-xs-offset-4{margin-left:33.33333333%}.col-xs-offset-3{margin-left:25%}.col-xs-offset-2{margin-left:16.66666667%}.col-xs-offset-1{margin-left:8.33333333%}.col-xs-offset-0{margin-left:0}@media (min-width:768px){.col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12{float:left}.col-sm-12{width:100%}.col-sm-11{width:91.66666667%}.col-sm-10{width:83.33333333%}.col-sm-9{width:75%}.col-sm-8{width:66.66666667%}.col-sm-7{width:58.33333333%}.col-sm-6{width:50%}.col-sm-5{width:41.66666667%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}.col-sm-pull-12{right:100%}.col-sm-pull-11{right:91.66666667%}.col-sm-pull-10{right:83.33333333%}.col-sm-pull-9{right:75%}.col-sm-pull-8{right:66.66666667%}.col-sm-pull-7{right:58.33333333%}.col-sm-pull-6{right:50%}.col-sm-pull-5{right:41.66666667%}.col-sm-pull-4{right:33.33333333%}.col-sm-pull-3{right:25%}.col-sm-pull-2{right:16.66666667%}.col-sm-pull-1{right:8.33333333%}.col-sm-pull-0{right:auto}.col-sm-push-12{left:100%}.col-sm-push-11{left:91.66666667%}.col-sm-push-10{left:83.33333333%}.col-sm-push-9{left:75%}.col-sm-push-8{left:66.66666667%}.col-sm-push-7{left:58.33333333%}.col-sm-push-6{left:50%}.col-sm-push-5{left:41.66666667%}.col-sm-push-4{left:33.33333333%}.col-sm-push-3{left:25%}.col-sm-push-2{left:16.66666667%}.col-sm-push-1{left:8.33333333%}.col-sm-push-0{left:auto}.col-sm-offset-12{margin-left:100%}.col-sm-offset-11{margin-left:91.66666667%}.col-sm-offset-10{margin-left:83.33333333%}.col-sm-offset-9{margin-left:75%}.col-sm-offset-8{margin-left:66.66666667%}.col-sm-offset-7{margin-left:58.33333333%}.col-sm-offset-6{margin-left:50%}.col-sm-offset-5{margin-left:41.66666667%}.col-sm-offset-4{margin-left:33.33333333%}.col-sm-offset-3{margin-left:25%}.col-sm-offset-2{margin-left:16.66666667%}.col-sm-offset-1{margin-left:8.33333333%}.col-sm-offset-0{margin-left:0}}@media (min-width:992px){.col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12{float:left}.col-md-12{width:100%}.col-md-11{width:91.66666667%}.col-md-10{width:83.33333333%}.col-md-9{width:75%}.col-md-8{width:66.66666667%}.col-md-7{width:58.33333333%}.col-md-6{width:50%}.col-md-5{width:41.66666667%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}.col-md-pull-12{right:100%}.col-md-pull-11{right:91.66666667%}.col-md-pull-10{right:83.33333333%}.col-md-pull-9{right:75%}.col-md-pull-8{right:66.66666667%}.col-md-pull-7{right:58.33333333%}.col-md-pull-6{right:50%}.col-md-pull-5{right:41.66666667%}.col-md-pull-4{right:33.33333333%}.col-md-pull-3{right:25%}.col-md-pull-2{right:16.66666667%}.col-md-pull-1{right:8.33333333%}.col-md-pull-0{right:auto}.col-md-push-12{left:100%}.col-md-push-11{left:91.66666667%}.col-md-push-10{left:83.33333333%}.col-md-push-9{left:75%}.col-md-push-8{left:66.66666667%}.col-md-push-7{left:58.33333333%}.col-md-push-6{left:50%}.col-md-push-5{left:41.66666667%}.col-md-push-4{left:33.33333333%}.col-md-push-3{left:25%}.col-md-push-2{left:16.66666667%}.col-md-push-1{left:8.33333333%}.col-md-push-0{left:auto}.col-md-offset-12{margin-left:100%}.col-md-offset-11{margin-left:91.66666667%}.col-md-offset-10{margin-left:83.33333333%}.col-md-offset-9{margin-left:75%}.col-md-offset-8{margin-left:66.66666667%}.col-md-offset-7{margin-left:58.33333333%}.col-md-offset-6{margin-left:50%}.col-md-offset-5{margin-left:41.66666667%}.col-md-offset-4{margin-left:33.33333333%}.col-md-offset-3{margin-left:25%}.col-md-offset-2{margin-left:16.66666667%}.col-md-offset-1{margin-left:8.33333333%}.col-md-offset-0{margin-left:0}}@media (min-width:1200px){.col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12{float:left}.col-lg-12{width:100%}.col-lg-11{width:91.66666667%}.col-lg-10{width:83.33333333%}.col-lg-9{width:75%}.col-lg-8{width:66.66666667%}.col-lg-7{width:58.33333333%}.col-lg-6{width:50%}.col-lg-5{width:41.66666667%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}.col-lg-pull-12{right:100%}.col-lg-pull-11{right:91.66666667%}.col-lg-pull-10{right:83.33333333%}.col-lg-pull-9{right:75%}.col-lg-pull-8{right:66.66666667%}.col-lg-pull-7{right:58.33333333%}.col-lg-pull-6{right:50%}.col-lg-pull-5{right:41.66666667%}.col-lg-pull-4{right:33.33333333%}.col-lg-pull-3{right:25%}.col-lg-pull-2{right:16.66666667%}.col-lg-pull-1{right:8.33333333%}.col-lg-pull-0{right:auto}.col-lg-push-12{left:100%}.col-lg-push-11{left:91.66666667%}.col-lg-push-10{left:83.33333333%}.col-lg-push-9{left:75%}.col-lg-push-8{left:66.66666667%}.col-lg-push-7{left:58.33333333%}.col-lg-push-6{left:50%}.col-lg-push-5{left:41.66666667%}.col-lg-push-4{left:33.33333333%}.col-lg-push-3{left:25%}.col-lg-push-2{left:16.66666667%}.col-lg-push-1{left:8.33333333%}.col-lg-push-0{left:auto}.col-lg-offset-12{margin-left:100%}.col-lg-offset-11{margin-left:91.66666667%}.col-lg-offset-10{margin-left:83.33333333%}.col-lg-offset-9{margin-left:75%}.col-lg-offset-8{margin-left:66.66666667%}.col-lg-offset-7{margin-left:58.33333333%}.col-lg-offset-6{margin-left:50%}.col-lg-offset-5{margin-left:41.66666667%}.col-lg-offset-4{margin-left:33.33333333%}.col-lg-offset-3{margin-left:25%}.col-lg-offset-2{margin-left:16.66666667%}.col-lg-offset-1{margin-left:8.33333333%}.col-lg-offset-0{margin-left:0}}.clearfix:before,.clearfix:after,.container:before,.container:after,.container-fluid:before,.container-fluid:after,.row:before,.row:after{content:" ";display:table}.clearfix:after,.container:after,.container-fluid:after,.row:after{clear:both}.center-block{display:block;margin-left:auto;margin-right:auto}.pull-right{float:right !important}.pull-left{float:left !important}.hide{display:none !important}.show{display:block !important}.invisible{visibility:hidden}.text-hide{font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.hidden{display:none !important}.affix{position:fixed}@-ms-viewport{width:device-width}.visible-xs,.visible-sm,.visible-md,.visible-lg{display:none !important}.visible-xs-block,.visible-xs-inline,.visible-xs-inline-block,.visible-sm-block,.visible-sm-inline,.visible-sm-inline-block,.visible-md-block,.visible-md-inline,.visible-md-inline-block,.visible-lg-block,.visible-lg-inline,.visible-lg-inline-block{display:none !important}@media (max-width:767px){.visible-xs{display:block !important}table.visible-xs{display:table !important}tr.visible-xs{display:table-row !important}th.visible-xs,td.visible-xs{display:table-cell !important}}@media (max-width:767px){.visible-xs-block{display:block !important}}@media (max-width:767px){.visible-xs-inline{display:inline !important}}@media (max-width:767px){.visible-xs-inline-block{display:inline-block !important}}@media (min-width:768px) and (max-width:991px){.visible-sm{display:block !important}table.visible-sm{display:table !important}tr.visible-sm{display:table-row !important}th.visible-sm,td.visible-sm{display:table-cell !important}}@media (min-width:768px) and (max-width:991px){.visible-sm-block{display:block !important}}@media (min-width:768px) and (max-width:991px){.visible-sm-inline{display:inline !important}}@media (min-width:768px) and (max-width:991px){.visible-sm-inline-block{display:inline-block !important}}@media (min-width:992px) and (max-width:1199px){.visible-md{display:block !important}table.visible-md{display:table !important}tr.visible-md{display:table-row !important}th.visible-md,td.visible-md{display:table-cell !important}}@media (min-width:992px) and (max-width:1199px){.visible-md-block{display:block !important}}@media (min-width:992px) and (max-width:1199px){.visible-md-inline{display:inline !important}}@media (min-width:992px) and (max-width:1199px){.visible-md-inline-block{display:inline-block !important}}@media (min-width:1200px){.visible-lg{display:block !important}table.visible-lg{display:table !important}tr.visible-lg{display:table-row !important}th.visible-lg,td.visible-lg{display:table-cell !important}}@media (min-width:1200px){.visible-lg-block{display:block !important}}@media (min-width:1200px){.visible-lg-inline{display:inline !important}}@media (min-width:1200px){.visible-lg-inline-block{display:inline-block !important}}@media (max-width:767px){.hidden-xs{display:none !important}}@media (min-width:768px) and (max-width:991px){.hidden-sm{display:none !important}}@media (min-width:992px) and (max-width:1199px){.hidden-md{display:none !important}}@media (min-width:1200px){.hidden-lg{display:none !important}}.visible-print{display:none !important}@media print{.visible-print{display:block !important}table.visible-print{display:table !important}tr.visible-print{display:table-row !important}th.visible-print,td.visible-print{display:table-cell !important}}.visible-print-block{display:none !important}@media print{.visible-print-block{display:block !important}}.visible-print-inline{display:none !important}@media print{.visible-print-inline{display:inline !important}}.visible-print-inline-block{display:none !important}@media print{.visible-print-inline-block{display:inline-block !important}}@media print{.hidden-print{display:none !important}}
</style>