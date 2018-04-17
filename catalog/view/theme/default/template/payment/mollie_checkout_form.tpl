<div class="checkout-content">
	<form action="<?php echo $action; ?>" method="post" id="mollie_payment_form">
		<div class="buttons">
			<?php if (!empty($issuers)) { ?>
			<div class="left pull-left">
				<label style="width:auto !important">
					<img src="<?php echo $image; ?>" width="20" style="float:left; margin-right:0.5em; margin-top:-3px" />
					<strong><?php echo $text_issuer; ?>:</strong>

					<select name="mollie_issuer" style="margin-left:1em" id="mollie_issuers">
						<option value="">&mdash;</option>
						<?php foreach ($issuers as $issuer) { ?>
						<option value="<?php echo $issuer->id; ?>"><?php echo $issuer->name; ?></option>
						<?php } ?>
					</select>
				</label>
			</div>
			<?php } ?>
			<div class="right pull-right">
				<input type="submit" value="<?php echo $message->get('button_confirm'); ?>" id="button-confirm" class="button btn btn-primary" form="mollie_payment_form">
			</div>
		</div>

		<script type="text/javascript">
			(function ($) {
				$(function () {
					var issuers = $("#mollie_issuers"),
							confirm_button_exists = ($("#qc_confirm_order").length > 0);

					if (issuers.find("option").length === 1) {
						$.post("<?php echo $set_issuer_url; ?>", {mollie_issuer_id: issuers.val()});
					}

					issuers.bind("change", function () {
						$.post("<?php echo $set_issuer_url; ?>", {mollie_issuer_id: $(this).val()});
					});

					// See if we can find a a confirmation button on the page (i.e. ajax checkouts).
					if (confirm_button_exists) {
						// If we have issuers, show the form.
						if (issuers.length) {
							$("#mollie_payment_form").parent().show();
						}

						return;
					}

					// No confirmation button found. Show our own confirmation button.
					$("#button-confirm").show().click(function () {
						$("#mollie_payment_form").submit();
					});
				});
			})(window.jQuery || window.$);
		</script>
	</form>
</div>
