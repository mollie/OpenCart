<div class="checkout-content">
	<form action="<?php echo $action; ?>" method="post" id="mollie_payment_form" <?php if(!$mollieComponents) { echo 'class="form-horizontal"'; } ?>>
		<div>
			<?php if (!empty($issuers)) { ?>
			<div class="form-group pull-left">
		        <label class="col-sm-6 control-label"><img src="<?php echo $image; ?>" width="20" /> <strong><?php echo $text_issuer; ?>:</strong></label>
		        <div class="col-sm-6">
		          <select name="mollie_issuer" id="mollie_issuers" class="form-control">
		            <option value="">&mdash;</option>
		            <?php foreach ($issuers as $issuer) { ?>
		            <option value="<?php echo $issuer->id; ?>"><?php echo $issuer->name; ?></option>
		            <?php } ?>
		          </select>
		        </div>
	      	</div>
			<?php } ?>
			<?php if($mollieComponents) { ?>
			<div class="left pull-left">
				<span><?php echo $text_card_details; ?></span>
			</div><br><br>
			<div id="mollie-response"></div>
			<div class="row">
				<div class="form-group col-sm-4">
					<label class="control-label"><?php echo $entry_card_holder; ?></label>
					<div id="card-holder"></div>
				</div>
				<div class="form-group col-sm-4">
					<label class="control-label"><?php echo $entry_card_number; ?></label>
					<div id="card-number"></div>
				</div>
				<div class="form-group col-sm-2">
					<label class="control-label"><?php echo $entry_expiry_date; ?></label>
					<div id="expiry-date"></div>
				</div>
				<div class="form-group col-sm-2">
					<label class="control-label"><?php echo $entry_verification_code; ?></label>
					<div id="verification-code"></div>
				</div>
			</div>
			<input type="hidden" id="card-token" name="cardToken" value="">
			<?php } ?>
			<div class="right pull-right buttons">
				<input type="submit" value="<?php echo $message->get('button_confirm'); ?>" id="button-confirm" class="button btn btn-primary" form="mollie_payment_form">
			</div>
			<div class="mollie-text">
				<span><i class="fa fa-lock"></i> <?php echo $text_mollie_payments; ?></span>
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

					<?php if($mollieComponents) { ?>
						// Initialize the mollie object
						var mollie = Mollie("<?php echo $currentProfile; ?>", { locale: "<?php echo $locale; ?>", testmode: "<?php echo $testMode; ?>" });

						// Styling
						var options = {
						   styles : {
						    base: {
						     	backgroundColor: '<?php echo $base_input_css['background_color']; ?>',
						    	color: '<?php echo $base_input_css['color']; ?>',
						        fontSize: '<?php echo $base_input_css['font_size']; ?>',
						       	'::placeholder' : {
						        	color: 'rgba(68, 68, 68, 0.2)',
						        }
						    },
						    valid: {
						        backgroundColor: '<?php echo $valid_input_css['background_color']; ?>',
						        color: '<?php echo $valid_input_css['color']; ?>',
						        fontSize: '<?php echo $valid_input_css['font_size']; ?>',
						    },
						    invalid: {
						     	backgroundColor: '<?php echo $invalid_input_css['background_color']; ?>',
						        color: '<?php echo $invalid_input_css['color']; ?>',
						        fontSize: '<?php echo $invalid_input_css['font_size']; ?>',
						    }
						   }
						 };

						// Mount credit card fileds
						var cardHolder = mollie.createComponent('cardHolder', options);
						cardHolder.mount('#card-holder');

						var cardNumber = mollie.createComponent('cardNumber', options);
						cardNumber.mount('#card-number');

						var expiryDate = mollie.createComponent('expiryDate', options);
						expiryDate.mount('#expiry-date');

						var verificationCode = mollie.createComponent('verificationCode', options);
						verificationCode.mount('#verification-code');

						document.getElementById("mollie_payment_form").addEventListener('submit', function(e) {
						  e.preventDefault();

						 mollie.createToken().then(function(result) {
						 	// Handle the result this can be either result.token or result.error.
						    // Add token to the form
						    if(result.error !== undefined) {
						    	<?php if ($isJournalTheme) { ?>
									triggerLoadingOff();
								<?php } ?>
						    	$('.alert-danger').remove();
						    	$("#mollie-response").after('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_card; ?></div>');
						    } else {
						    	$('.alert-danger').remove();
						    	$("#card-token").val(result.token);

						    	// Re-submit the form
						    	document.getElementById("mollie_payment_form").submit();
						    }
						  	
						  });  
						});

					<?php } ?>

					// See if we can find a a confirmation button on the page (i.e. ajax checkouts).
					if (confirm_button_exists) {
						// If we have issuers or mollie components are enabled, show the form.
						var mollieComponents = '<?php echo $mollieComponents; ?>';
						if (issuers.length || mollieComponents) {
							$("#mollie_payment_form").parent().show();
						}

						return;
					}

					// No confirmation button found. Show our own confirmation button.
					$("#button-confirm").show();
				});
				
				$("#mollie_payment_form").submit(function () {
					$("#button-confirm").attr("disabled", true);
					return true;
				});
			})(window.jQuery || window.$);
		</script>
		<style type="text/css">
		<?php if($mollieComponents) { ?>		
			.mollie-component {
				<?php echo $base_input_css['other_css']; ?>
			}
			.mollie-component.is-valid {
				<?php echo $valid_input_css['other_css']; ?>
			}
			.mollie-component.is-invalid {
				<?php echo $invalid_input_css['other_css']; ?>
			}			

			.journal-checkout #payment-confirm-button .buttons {
			     display: block !important; 
			     cursor: unset !important;
			}

			.journal-checkout #payment-confirm-button .buttons .btn {
			    pointer-events: unset !important;
			}

			.is-customer .journal-checkout .left {
			    display: block; 
			}

			<?php if ($isJournalTheme) { ?>
			#button-confirm {
				display: none !important;
			}

			.mollie-text img {
			    top: 4px;
			}
			<?php } ?>					
		<?php } ?>
		<?php if(empty($issuers) && !$mollieComponents) { ?>
			#payment-confirm-button {
				display: none;
			}
		<?php } ?>

		.mollie-text img {
		    position: relative;
		    top: -3px;
		    width: 58px;
		    left: -5px;
		}

		.mollie-text {
	      clear: both;
	      margin-left: 7px;
	    }

		</style>
	</form>
</div>
