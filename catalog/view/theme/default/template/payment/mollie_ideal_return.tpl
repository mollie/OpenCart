<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<?php
function clean_echo ($string)
{
	echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<div id="content">
	<?php echo $content_top ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']); ?><a href="<?php clean_echo($breadcrumb['href']); ?>"><?php clean_echo($breadcrumb['text']); ?></a>
		<?php endforeach; ?>
	</div>

	<h1>iDEAL Status</h1>
	<div class="content">
		<?php if ($payment['bank_status'] == 'Success'): ?>
			<?php echo $message->get('response_success') ?>
		<?php elseif ($payment['bank_status'] == ''): ?>
			<?php echo $message->get('response_none') ?>
		<?php else: ?>
			<?php echo sprintf($message->get('response_failed'), number_format($order['total'], 2)) ?>
	</div>
			<div class="buttons">
				<div class="right">
					<form action="<?php clean_echo ($action); ?>" method="post" id="rty">
						<input type="hidden" name="transaction_id" value="<?php clean_echo($payment['transaction_id']) ?>" />
						<select name="bank_id" id="bank_id">
							<option value='0'>Kies uw bank</option>
							<?php foreach ($banks as $bank_id => $bank_name): ?>
								<option value="<?php clean_echo($bank_id); ?>"><?php clean_echo($bank_name); ?></option>
							<?php endforeach; ?>
						</select>
						<a id="button-confirm" class="button"><span>Probeer opnieuw!</span></a>
					</form>
				</div>
			</div>
			<script type="text/javascript">
				$('#button-confirm').click(function() {
					if ($('#bank_id').val() != 0) {
						$('#rty').submit();
					} else {
						alert('Selecteer een bank'); 
					}
				});
			</script>
		<?php endif ?>
</div>

<?php echo $content_bottom; ?>

<?php echo $footer; ?>
