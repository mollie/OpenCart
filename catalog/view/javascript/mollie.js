$(document).ready(function() {
  if (!window.ApplePaySession || !ApplePaySession.canMakePayments()) {
    // Apple Pay is not available
    if(document.cookie.indexOf('applePay=') == -1) {
    	document.cookie="applePay=0";
    }
  }
});