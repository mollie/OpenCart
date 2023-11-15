$(document).ready(function() {
	if (!window.ApplePaySession || !ApplePaySession.canMakePayments()) {
		// Apple Pay is not available
		$.ajax({
			type: "POST",
			url: 'index.php?route=extension/mollie/payment/mollie_ideal|setApplePaySession',
			data: "apple_pay=0",
			success: function() {			
			}
		});
	}
});