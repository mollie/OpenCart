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

        <!-- wrap the actual input in a div for JS purposes -->
        <div id="mollie_payment_form_input">
            <?php if (count($payment_methods) == 1): ?>

                <!-- only one payment method through Mollie available -->
                <input type="hidden" name="mollie_method" value="<?php clean_echo(reset($payment_methods)->id) ?>">

            <?php elseif (!empty($mollie_method)): ?>

                <!-- Mollie method recovered from session -->
                <input type="hidden" name="mollie_method" value="<?php clean_echo($mollie_method) ?>">

            <?php else: ?>

                <!-- multiple payment methods through Mollie available, customer must choose. -->
                <p><?php clean_echo($message->get('text_payment_method')) ?></p>

                <table class="radio">
                    <tbody>
                    <?php foreach ($payment_methods as $index => $payment_method): ?>
                        <tr>
                            <td>
                                <input type="radio"<?php if ($index == 0): ?> checked="checked"<?php endif ?> name="mollie_method" value="<?php clean_echo($payment_method->id) ?>"  id="mollie_method_<?php clean_echo($payment_method->id) ?>">
                            </td>
                            <td>
                                <label for="mollie_method_<?php clean_echo($payment_method->id) ?>">
                                    <img src="<?php clean_echo($payment_method->image->normal) ?>" height="24" align="left" style="margin-top:-5px" />
                                    &nbsp;<?php clean_echo($payment_method->description) ?>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>

            <?php endif ?>
        </div>

        <div class="buttons">
            <div class="right pull-right">
                <input type="submit" value="<?php echo $message->get('button_confirm') ?>" id="button-confirm" class="button btn btn-primary" form="mollie_payment_form">
            </div>
        </div>

		<script type="text/javascript">
			(function ($)
			{
				// Run after pageload.
				$(function ()
				{
					// Try to find an order confirmation button. If we can't find one, make our own button visible.
					if ($("#qc_confirm_order").length == 0)
					{
						$(".checkout-content, #button-confirm").show();
					}

					$("#button-confirm").click(function ()
					{
						$("#mollie_payment_form").submit();
					});
				});
			}) (window.jQuery || window.$);
		</script>
    </form>
</div>
