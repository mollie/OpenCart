$(document).ready(function() {
	if (!window.ApplePaySession || !ApplePaySession.canMakePayments()) {
		// Apple Pay is not available
		$.ajax({
			type: "POST",
			url: 'index.php?route=payment/mollie/base/setApplePaySession',
			data: "apple_pay=0",
			success: function() {			
			}
		});
	}
});