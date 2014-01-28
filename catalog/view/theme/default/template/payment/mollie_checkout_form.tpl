<form action="<?php echo htmlspecialchars($action); ?>" method="post">
	<?php if (sizeof($payment_methods) == 1): ?>
	<!-- only one payment method through Mollie available -->
	<input type="hidden" name="mollie_method" value="<?php echo htmlspecialchars($payment_methods[0]->id);?>">

	<?php else: ?>
	<!-- multiple payment methods through Mollie available, customer must choose. -->
	<p><?php echo htmlspecialchars($message->get('text_payment_method'));?></p>

	<table class="radio">
		<tbody>
		<?php foreach ($payment_methods as $index => $payment_method): ?>
		<tr>
			<td>
				<input type="radio"<?php if ($index == 0): ?> checked="checked"<?php endif;?> name="mollie_method" value="<?php echo htmlspecialchars($payment_method->id);?>"  id="mollie_method_<?php echo htmlspecialchars($payment_method->id);?>">
			</td>
			<td>
				<label for="mollie_method_<?php echo htmlspecialchars($payment_method->id);?>">
					<?php echo htmlspecialchars($payment_method->description); ?>
				</label>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php endif; ?>

	<div class="buttons">
		<div class="right">
			<input type="button" value="<?php echo $message->get('button_confirm'); ?>" id="button-confirm" class="button">
		</div>
	</div>
</form>