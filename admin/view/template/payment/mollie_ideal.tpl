<?php echo $header; ?>
<?php
function clean_echo ($string)
{
	echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']); ?><a href="<?php clean_echo($breadcrumb['href']); ?>"><?php clean_echo($breadcrumb['text']); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ($error_warning): ?>
		<div class="warning"><?php clean_echo($error_warning); ?></div>
	<?php endif; ?>
	
	<div class="box"> 
		<div class="left"></div> 
		<div class="right"></div> 

		<div class="heading">
			<h1><img src="view/image/payment.png" alt="" /> <?php clean_echo($heading_title); ?></h1>
			<div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php clean_echo($button_save); ?></span></a><a href="<?php clean_echo($cancel); ?>" class="button"><span><?php clean_echo($button_cancel); ?></span></a></div>
		</div>

		<div class="content">
			<form action="<?php clean_echo($action); ?>" method="post" enctype="multipart/form-data" id="form">
				<input type="hidden" name="mollie_ideal_returnurl" value="<?php clean_echo($mollie_ideal_returnurl); ?>" />
				<input type="hidden" name="mollie_ideal_reporturl" value="<?php clean_echo($mollie_ideal_reporturl); ?>" />

				<table class="form">
					<tr>
						<td><?php clean_echo($entry_status); ?></td>
						<td>
							<select name="mollie_ideal_status">
								<option value="1" <?php if ($mollie_ideal_status == 1) { echo 'selected="selected"'; } ?>><?php clean_echo($text_enabled); ?></option>
								<option value="0" <?php if ($mollie_ideal_status == 0) { echo 'selected="selected"'; } ?>><?php clean_echo($text_disabled); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td><?php clean_echo($entry_testmode); ?></td>
						<td>
							<select name="mollie_ideal_testmode">
								<option value="true" <?php if ($mollie_ideal_testmode == 'true') { echo 'selected="selected"'; } ?>><?php clean_echo($test_yes); ?></option>
								<option value="false" <?php if ($mollie_ideal_testmode == 'false') { echo 'selected="selected"'; } ?>><?php clean_echo($test_no); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php clean_echo($entry_partnerid); ?><br /></td>
						<td><input type="text" name="mollie_ideal_partnerid" value="<?php clean_echo($mollie_ideal_partnerid); ?>" />
							<br />
							<?php if (!empty($error_partnerid)): ?>
								<span class="error"><?php clean_echo($error_partnerid); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php clean_echo($entry_profilekey); ?></td>
						<td><input type="text" name="mollie_ideal_profilekey" value="<?php clean_echo($mollie_ideal_profilekey); ?>" />
							<br/>
							<?php if (!empty($error_profilekey)): ?>
								<span class="error"><?php clean_echo($error_profilekey); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php clean_echo($entry_description); ?></td>
						<td><input style="width:240px;" maxlength="29" type="text" name="mollie_ideal_description" value="<?php clean_echo($mollie_ideal_description); ?>" />
							<br/>
							<?php if (!empty($error_description)): ?>
								<span class="error"><?php clean_echo($error_description); ?></span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td><?php clean_echo($entry_sort_order); ?></td>
						<td><input type="text" name="mollie_ideal_sort_order" value="<?php clean_echo($mollie_ideal_sort_order); ?>" size="1" /></td>
					</tr>

					<tr>
						<td height="24"></td>
						<td></td>
					</tr>
					<tr>
						<td style="vertical-align: middle;"><?php clean_echo($entry_module); ?></td>
						<td style="vertical-align: middle;"><?php clean_echo($entry_version); ?></td>
					</tr>
					<tr>
						<td><?php clean_echo($entry_support); ?></td>
						<td>Mollie B.V.<br />
							<a href="https://www.mollie.nl/bedrijf/contact" target="new">https://www.mollie.nl/bedrijf/contact</a><br />
						</td>
					</tr>
				</table>
			</form>
			<center>
				<a href="http://www.mollie.nl/" target="_new"><img src="http://www.mollie.nl/images/badge-powered-medium.png" width="135" height="87" border="0" alt="Mollie" /></a><br/>
				Copyright &copy; Mollie B.V. 2004-<?php echo date('Y'); ?> SMS & Betaaldiensten
			</center>
		</div>
		<script type="text/javascript">
		<!--
			$.tabs('.tabs a');
		//-->
		</script>
	</div>
</div>

<?php clean_echo($footer); ?>
