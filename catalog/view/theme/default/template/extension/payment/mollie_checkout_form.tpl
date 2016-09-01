<?php
    if (!function_exists('clean_echo'))
    {
        function clean_echo ($string)
        {
            echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }
    }
?>

<div class="checkout-content">
    <form action="<?php echo clean_echo($action) ?>" method="post" id="mollie_payment_form">

        <div class="buttons">
			<?php if (!empty($issuers)) { ?>
			<div class="left pull-left">
				<label style="width:auto !important">
					<img src="https://www.mollie.com/images/payscreen/methods/ideal.png" width="20" style="float:left; margin-right:0.5em; margin-top:-3px" />
					<strong><?php clean_echo($text_issuer) ?>:</strong>

					<select name="mollie_issuer" style="margin-left:1em" id="mollie_issuers">
						<option value="">&mdash;</option>
						<?php foreach ($issuers as $issuer) { ?>
						<option value="<?php clean_echo($issuer->id) ?>"><?php clean_echo($issuer->name) ?></option>
						<?php } ?>
					</select>
				</label>
			</div>
			<?php } ?>
            <div class="right pull-right">
                <input type="submit" value="<?php echo $message->get('button_confirm') ?>" id="button-confirm" class="button btn btn-primary" form="mollie_payment_form">
            </div>
        </div>

		<script type="text/javascript">
			(function ($)
			{

				function initiateMollieConfirmClick(confirm_button_exists)
				{
					if (confirm_button_exists) 
					{
						$("#qc_confirm_order").click(function ()
						{
							$("#mollie_payment_form").submit();
						});
					}
					else
					{
						$("#button-confirm").click(function ()
						{
							$("#mollie_payment_form").submit();
						});
					}
				}

				// Run after pageload.
				$(function ()
				{
					var issuers               = $("#mollie_issuers"),
						confirm_button_exists = ($("#qc_confirm_order").length > 0);

					if (issuers.find("option").length == 1)
					{
						$.post("<?php clean_echo ($set_issuer_url) ?>", {mollie_issuer_id:issuers.val()});
					}

					issuers.bind("change", function() {
						$.post("<?php clean_echo ($set_issuer_url) ?>", {mollie_issuer_id:$(this).val()});
					});

					// See if we can find a a confirmation button on the page (i.e. ajax checkouts).
					if (confirm_button_exists)
					{
						// If we have issuers, show the form.
						if (issuers.length)
						{
							$("#mollie_payment_form").parent().show();
						}

						// render after change (for ajax checkouts)
						initiateMollieConfirmClick(confirm_button_exists);
						return;
					}

					// No confirmation button found. Show our own confirmation button.
					$("#button-confirm").show();

					$("#button-confirm").click(function ()
					{
						$("#mollie_payment_form").submit();
					});

					// default confirm button render
					initiateMollieConfirmClick(confirm_button_exists);
				});
			}) (window.jQuery || window.$);
		</script>
    </form>
</div>
