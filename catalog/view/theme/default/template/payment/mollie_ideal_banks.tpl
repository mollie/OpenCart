<?php
function clean_echo ($string)
{
	echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<div class="buttons">
	<form action="<?php echo $action; ?>" method="post" id="cof">
		<div class="left">
		</div>
		<div class="right">
			<select name="bank_id" id="bank_id">
				<option value='0'><?php echo $message->get('select_your_bank') ?></option>
				<?php foreach ($banks as $bank_id => $bank_name): ?>
					<option value="<?php clean_echo($bank_id); ?>"><?php clean_echo($bank_name); ?></option>
				<?php endforeach; ?>
			</select>
			<a id="button-confirm" class="button"><span><?php echo $message->get('button_confirm') ?></span></a>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#button-confirm').click(function() {
		if ($('#bank_id').val() != 0) {
			$('#cof').submit();
		} else {
			alert('<?php clean_echo($message->get("select_your_bank")) ?>');
		}
	});
</script>