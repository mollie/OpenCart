<?php
	if (!function_exists('clean_echo'))
	{
		function clean_echo ($string)
		{
			echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
	}
?>

<?php echo $header ?>

<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<?php clean_echo($breadcrumb['separator']) ?><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a>
		<?php endforeach ?>
	</div>

	<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form">

	<div class="tabs">

		<ul class="tab-links">
	        <?php foreach($shops as $store) { ?>
	        	<li class="<?php if ($store['id'] == 0) { echo "active"; } ?> "><a href="#store<?php echo $store['id'];?>"><?php echo $store['name']; ?></a></li>
	        <?php } ?>
	    </ul>

		<div class="box">
			<div class="left"></div>
			<div class="right"></div>

			<div class="heading">
				<h1><img src="view/image/payment.png" alt="" /> <?php clean_echo($heading_title) ?></h1>
				<div class="buttons"><a onclick="(window.jQuery || window.$)('#form').submit()" class="button"><span><?php clean_echo($button_save) ?></span></a><a href="<?php echo $cancel ?>" class="button"><span><?php clean_echo($button_cancel) ?></span></a></div>
			</div>
	 
	    <div class="tab-content">
		    <?php foreach($shops as $store) { ?>
	    	<div id="store<?php echo $store['id']; ?>" class="tab <?php if ($store['id'] == 0) { echo 'active'; } ?>">
				<?php if ($error_warning): ?>
					<div class="warning"><?php clean_echo($error_warning) ?></div>
				<?php elseif (!mb_strlen($stores[$store['id']]['mollie_api_key'])): ?>
					<div class="attention"><?php echo $help_view_profile ?></div>
				<?php endif ?>
				<div class="content">
					
						<table class="form">
							<tr align="left">
								<th style="padding-left:10px"></th>
								<th style="padding-left:10px"><?php clean_echo($entry_payment_method) ?></th>
								<th style="padding-left:10px"><?php clean_echo($entry_activate) ?></th>
								<th style="padding-left:10px"><?php clean_echo($entry_sort_order) ?></th>
							</tr>

							<?php foreach ($stores[$store['id']]['payment_methods'] as $module_id => $payment_method) { ?>
							<tr>
								<td></td>
								<td>
									<img src="<?php clean_echo($payment_method['icon']) ?>" width="20" style="float:left; margin-right:1em; margin-top:-3px" />
									<?php clean_echo($payment_method['name']) ?>
								</td>
								<td>
									<?php
									// Hide the checkbox in case of an error, but don't remove the input entirely to make sure we keep our setting.
									$show_checkbox = TRUE;

									if (!mb_strlen($stores[$store['id']]['mollie_api_key']) || !empty($stores[$store['id']]['error_api_key']))
									{
										$show_checkbox = FALSE;
										echo $text_missing_api_key;
									}
									elseif (!$payment_method['allowed'])
									{
										$show_checkbox = FALSE;
										echo $text_activate_payment_method;
									}
									?>
									<input type="checkbox" name="stores[<?php echo $store['id']; ?>][mollie_<?php echo $module_id ?>_status]"<?php if ($payment_method['status']) { ?> checked<?php } ?> style="cursor:pointer<?php if (!$show_checkbox) { ?>; display:none<?php } ?>" />
								</td>
								<td>
									<input type="text" name="stores[<?php echo $store['id']; ?>][mollie_<?php echo $module_id ?>_sort_order]" value="<?php clean_echo($payment_method['sort_order']) ?>" style="text-align:right; max-width:60px" />
								</td>
							</tr>
							<?php } ?>
						</table>

						<h3><?php clean_echo($title_global_options) ?></h3>
						<table class="form">
							<tr>
								<td><span class="required">*</span> <?php printf('%s:<br /><span class="help">%s</span>', $entry_api_key, $help_api_key) ?></td>
								<td><input type="text" name="stores[<?php echo $store['id']; ?>][mollie_api_key]" value="<?php clean_echo($stores[$store['id']]['mollie_api_key']) ?>" style="width:240px" placeholder="live_..."/>
									<br/>
									<?php if (!empty($stores[$store['id']]['error_api_key'])): ?>
										<span class="error"><?php clean_echo($stores[$store['id']]['error_api_key']) ?></span>
									<?php endif ?>
								</td>
							</tr>

							<tr>
								<td><span class="required">*</span> <?php printf('%s:<br /><span class="help">%s</span>', $entry_description, $help_description) ?></td>
								<td><input style="width:240px" maxlength="29" type="text" name="stores[<?php echo $store['id']; ?>][mollie_ideal_description]" value="<?php clean_echo($stores[$store['id']]['mollie_ideal_description']) ?>" />
									<br/>
									<?php if (!empty($stores[$store['id']]['error_description'])): ?>
									<span class="error"><?php clean_echo($stores[$store['id']]['error_description']) ?></span>
									<?php endif ?>
								</td>
							</tr>

							<tr>
								<td><?php printf('%s:<br /><span class="help">%s</span>', $entry_show_icons, $help_show_icons) ?></td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_show_icons]">
										<option value="1" <?php if ($stores[$store['id']]['mollie_show_icons'] == 1) { echo 'selected="selected"'; } ?>><?php clean_echo($text_yes) ?></option>
										<option value="0" <?php if ($stores[$store['id']]['mollie_show_icons'] == 0) { echo 'selected="selected"'; } ?>><?php clean_echo($text_no) ?></option>
									</select>
								</td>
							</tr>

							<tr>
								<td><?php printf('%s:<br /><span class="help">%s</span>', $entry_show_order_canceled_page, $help_show_order_canceled_page) ?></td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_show_order_canceled_page]">
										<option value="1" <?php if ($stores[$store['id']]['mollie_show_order_canceled_page'] == 1) { echo 'selected="selected"'; } ?>><?php clean_echo($text_yes) ?></option>
										<option value="0" <?php if ($stores[$store['id']]['mollie_show_order_canceled_page'] == 0) { echo 'selected="selected"'; } ?>><?php clean_echo($text_no) ?></option>
									</select>
								</td>
							</tr>
						</table>

						<h3><?php clean_echo($title_payment_status) ?></h3>
						<table class="form">
							<tr>
								<td><?php clean_echo($entry_pending_status) ?>:</td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_ideal_pending_status_id]">
										<?php foreach ($order_statuses as $order_status): ?>
										<?php if ($order_status['order_status_id'] == $stores[$store['id']]['mollie_ideal_pending_status_id']): ?>
										<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
										<?php else: ?>
										<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
										<?php endif ?>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_failed_status) ?>:</td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_ideal_failed_status_id]">
										<?php if (empty($stores[$store['id']]['mollie_ideal_failed_status_id'])) { ?>
											<option value="0" selected="selected"><?php echo $text_no_status_id ?></option>
										<?php } else { ?>
											<option value="0"><?php echo $text_no_status_id ?></option>
										<?php } ?>
									<?php foreach ($order_statuses as $order_status): ?>
										<?php if ($order_status['order_status_id'] == $stores[$store['id']]['mollie_ideal_failed_status_id']): ?>
											<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
										<?php else: ?>
											<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
										<?php endif ?>
									<?php endforeach ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_canceled_status) ?>:</td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_ideal_canceled_status_id]">
										<?php if (empty($stores[$store['id']]['mollie_ideal_canceled_status_id'])) { ?>
											<option value="0" selected="selected"><?php echo $text_no_status_id ?></option>
										<?php } else { ?>
											<option value="0"><?php echo $text_no_status_id ?></option>
										<?php } ?>
										<?php foreach ($order_statuses as $order_status): ?>
											<?php if ($order_status['order_status_id'] == $stores[$store['id']]['mollie_ideal_canceled_status_id']): ?>
												<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
											<?php else: ?>
												<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
											<?php endif ?>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_expired_status) ?>:</td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_ideal_expired_status_id]">
										<?php if (empty($stores[$store['id']]['mollie_ideal_expired_status_id'])) { ?>
										<option value="0" selected="selected"><?php echo $text_no_status_id ?></option>
										<?php } else { ?>
										<option value="0"><?php echo $text_no_status_id ?></option>
										<?php } ?>
										<?php foreach ($order_statuses as $order_status): ?>
											<?php if ($order_status['order_status_id'] == $stores[$store['id']]['mollie_ideal_expired_status_id']): ?>
												<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
											<?php else: ?>
												<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
											<?php endif ?>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_processing_status) ?>:</td>
								<td>
									<select name="stores[<?php echo $store['id']; ?>][mollie_ideal_processing_status_id]">
									<?php foreach ($order_statuses as $order_status): ?>
										<?php if ($order_status['order_status_id'] == $stores[$store['id']]['mollie_ideal_processing_status_id']): ?>
											<option value="<?php echo $order_status['order_status_id'] ?>" selected="selected"><?php echo $order_status['name'] ?></option>
										<?php else: ?>
											<option value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
										<?php endif ?>
									<?php endforeach ?>
									</select>
								</td>
							</tr>
						</table>

						<h3><?php clean_echo($title_mod_about) ?></h3>
						<table class="form">
							<tr>
								<td style="vertical-align: middle"><?php echo $entry_module ?>:</td>
								<td style="vertical-align: middle"><?php echo $entry_version ?></td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_mod_status) ?>:</td>
								<td>
									<?php
										if (is_array($entry_mstatus)) {
											foreach ($entry_mstatus as $file) {
												echo $error_file_missing . ": '$file'<br/>";
											}
										} else {
											echo $entry_mstatus;
										}
									?>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_comm_status) ?>:</td>
								<td>
									<?php echo $stores[$store['id']]['entry_cstatus'] ?>
								</td>
							</tr>
							<tr>
								<td><?php clean_echo($entry_support) ?>:</td>
								<td>
									<a href="https://www.mollie.com/bedrijf/contact" target="_blank">Mollie B.V.</a>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<a href="https://www.mollie.com/" target="_blank"><img src="https://www.mollie.com/images/badge-powered-medium.png" width="135" height="87" border="0" alt="Mollie" /></a><br/><br/>
									&copy; 2004-<?php echo date('Y') ?> Mollie B.V. <?php clean_echo($footer_text) ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</form>
</div>




<?php echo $footer ?>


<style>

/*----- Tabs -----*/
.tabs {
    width:100%;
    display:inline-block;
}
 
    /*----- Tab Links -----*/
    /* Clearfix */
    .tab-links:after {
        display:block;
        clear:both;
        content:'';
    }
 
    .tab-links {
    	margin-left: -43px;
    }

    .tab-links li {
        margin:0px 5px;
        float:left;
        list-style:none;
        
    }
 
        .tab-links a {
            padding:9px 15px;
            display:inline-block;
            border-radius:3px 3px 0px 0px;
            background:#fff;
            font-size:16px;
            font-weight:600;
            color:#4c4c4c;
            transition:all linear 0.15s;
            text-decoration: none;
        }
 
        .tab-links a:hover {
            background:#eaeaea;
            text-decoration:none;
        }
 
    li.active a, li.active a:hover {
        background:#eaeaea;
        color:#4c4c4c;
    }
 
    /*----- Content of Tabs -----*/
    .tab-content {
        padding:15px;
        border-radius:3px;
        box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
        background:#fff;
    }
 
        .tab {
            display:none;
        }
 
        .tab.active {
            display:block;
        }

</style>

<script type="text/javascript">

$(document).ready(function() {
    $('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = $(this).attr('href');
 
        // Show/Hide Tabs
        $('.tabs ' + currentAttrValue).show().siblings().hide();
 
        // Change/remove current tab to active
        $(this).parent('li').addClass('active').siblings().removeClass('active');
 
        e.preventDefault();
    });
});

</script>