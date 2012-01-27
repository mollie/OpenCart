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
				<option value='0'>Kies uw bank</option>
				<?php foreach ($arr_banks as $bank_id => $bank_name): ?>
					<option value="<?php clean_echo($bank_id); ?>"><?php clean_echo($bank_name); ?></option>
				<?php endforeach; ?>
			</select>
			<a id="button-confirm" class="button"><span><?php echo clean_echo($button_confirm); ?></span></a>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#button-confirm').click(function() {
		if ($('#bank_id').val() != 0) {
			$('#cof').submit();
		} else {
			alert('Selecteer een bank'); 

		}
	});
</script> 