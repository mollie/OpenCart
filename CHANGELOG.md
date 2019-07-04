![Mollie](https://www.mollie.com/files/Mollie-Logo-Style-Small.png)

# Changelog #

#### Changes in release 9.0.6
  + Added Przelewy24 support.
  + Added additional check on order_id in webhook.
  + Update cleanup function to include cleanup for < 9.x modules.
  + Remove Bitcoin.
  + Fix double payment bug.
  + Fix voucher support.
  + Fix Amount invalid bug for all currencies.
  + Fix non-integer qty support.

#### Changes in release 9.0.5
  + Fixed notice error when shipping method is used that isn't linked to a tax class.
  + Fixed update functionality.

#### Changes in release 9.0.4
  + Added conditional checking for OC 1.5, so OCMod checks are not attempted on a version that doesn't support it.

#### Changes in release 9.0.3
  + Fixed settings randomly disappearing from admin.
  + Added support for VQMod/OCMod combinations.
  + Rounding fix.
  + Fixed missing translation button label.
  + Fixed cleanup function.
  + Send webhook URL on order creation.
  + Check payment status on success page.

#### Changes in release 9.0.2
  + Fixed a bug in the module update functionality.

#### Changes in release 9.0.1
  + Correctly calculate taxes.
  + Unselect payment method for store is possible again.
  + Fixed "Undefined index: iso_code_2" in one page checkouts.
  + Less warnings about adding an API key in the backend.
  + "Amount is lower than minimum" bugfix.
  + Fixed loading Belfius controller (thanks @KevinJDurant).
  + Fixed switched translations for EPS and Giropay (thanks @pvdptje).
  + Added support for 3rd party totals.
  + Minor improvements to documentation.
  + Removed possibility of sending html entities to Mollie API.
  + Made API key a mandatory field.
  + Added support for OC events in the versions that support it.
  + Added "shipping after complete status" functionality.

#### Changes in release 9.0.0
  + Added version support for OpenCart 1.5.x to 3.x in one module, no need to download a separate version anymore;
  + Added Klarna payment methods;
  + Added Mollie Order API and Shipment API;
  + Added update functionality right from the module itself, linked to new Github releases;
  + Added multi currency support;
  + Improved payment method display based on Mollie settings;
  + Bugfixes: fixed amount.value errors with totals over 1000 EU, errors when checking out with USD, setting price limits, entering API key.

#### Changes in release 8.4.2
  + Fixed error in total price that gave a amount.value error on totals over 1000 eu.

#### Changes in release 8.4.1
  + Fixed the error with rounded order totals in 2.2-and-lower version giving the following error: "Error executing API call (422: Unprocessable Entity): The amount contains an invalid value. Field: amount.value."
  + Fixed language file switch of EPS and Giropay payment methods.
  + Removed remaining Mistercash language files from the admin dir for the 2.3+ version.

#### Changes in release 8.4.0
  + Added support for Mollie API v2.
  + Added German (DE) and Spanish (ES) language support in the module.
  + Added EPS and Giropay payment methods.
  + Renamed MisterCash to Bancontact according to Mollie specifications.

#### Changes in release 8.3.2
  + Bugfix in the array with available Locale

#### Changes in release 8.3.1
  + Locale: use en_US if user Locale is not supported

#### Changes in release 8.3.0
  + ING Home'Pay added as payment method
  + Cleanup check for already installed modules that are updated

#### Changes in release 8.2.3
  + Improved logging for future debugging
  + Minor bug fixes.

#### Changes in release 8.2.2
  + Fixed a bug where quick checkouts would not validate properly
  + Added missing translation
  
#### Changes in release 8.2.1
  + Fixed a bug where Bancontact would be displayed as Mister Cash

#### Changes in release 8.2.0
  + New features are now available for OpenCart 1.5 and 2.x!
  + Fix a bug where a payment method would not always be displayed correctly in the order email when using payment images.
  + Minor issues solved.

#### Changes in release 8.1.0
  + Added 6 new payment methods (Gift cards)!
    + fashioncheque
    + Nationale EntertainmentCard
    + Podium Cadeaukaart
    + VVV Giftcard
    + YourGift
    + Webshop Giftcard

#### Changes in release 8.0.0
  + Convert module to work with OpenCart 3.x.x.x only (soon to be backported).
  + Addition of geo-zone selection per payment method.
  + Adjusted admin interface to declutter the view.

#### Changes in release 7.2.0
  + Add helpful method: `getPaymentID()`
  + Double check payment status in callback - uses new `getPaymentID()` method
  + Update mollie_return_2.tpl - some themes are using HTML 'id' attributes on container divs

#### Changes in release 7.1.1
  + Update bundled cacert.pem file. Follows Mozilla's recommendations on invalid certificates.

#### Changes in release 7.1.0
  + Added the payment method KBC Payment Button.

#### Changes in release 7.0.0
  + This is a backwards incompatible release that will only work for OpenCart 2.3 and up. To keep using our module with OpenCart 2.2 or lower, please checkout the `opencart-2.2` branch.

#### Changes in release 6.3.1
  + Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.5.1`

#### Changes in release 6.3.0
  + Added language paths for OC 2.2 and higher.

#### Changes in release 6.2.9
  + Fixed Confirm order click - open steps stay closed.

#### Changes in release 6.2.8
  + Fix for when the order id was not found in the redirectUrl

#### Changes in release 6.2.7
  + Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.3.3`

#### Changes in release 6.2.6
  + Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.3.1`
  + Third-party module compatibility: Quick checkout

#### Changes in release 6.2.5
  + Added multi-store support

#### Changes in release 6.2.4
  + Add support for SEPA Direct Debit  + Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.2.10`

#### Changes in release 6.2.3
  + Hide unavailable payment methods. Fixes problem when switching between test and live API key or when you change the activated payment methods in your Mollie Profile.
  + Update Mollie API client

#### Changes in release 6.2.2
  + Update Mollie API client. Fixes compatibility with PHP 5.2+

#### Changes in release 6.2.1
  + Add payment method Belfius Direct Net

#### Changes in release 6.2.0
  + Add some extra checks to the admin settings page to check if the module is installed correctly, your system is
  compatible and test the connection with with Mollie. Display possible fixes if the module can't connect with Mollie.

#### Changes in release 6.1.3
  + Clear cart when an order is marked as 'paid'.

#### Changes in release 6.1.2
  + Fix issue with white screen appearing when cancelling a payment on older PHP versions.

#### Changes in release 6.1.1
  + Fix order confirmation button.

#### Changes in release 6.1.0
  + Orders are no longer set to 'pending' when created (except for bank transfers). This should remove duplicate orders in the admin panel.
  + The 'cancelled' and 'expired' statuses can be disabled in the admin panel as well, if you do not wish to see these in your order overview.
  + For cancelled payments, you can now choose between showing a 'transaction failed' page or sending your customers directly back to the shopping cart.

#### Changes in release 6.0.1
  + Send users back to their shopping cart if a payment fails.

#### Changes in release 6.0.0
  + All payment methods have now been added as separate modules to improve support with other third party modules.

#### Changes in release 5.2.7
  + Prevent single page checkout modules from overwriting payment method selection.
  + Fixed issue with the latest vQmod release.

#### Changes in release 5.2.6
  + Fix support for Dreamvention's Quick Checkout module plugin.

#### Changes in release 5.2.5
  + Stop Google Analytics from listing payment provider as referrer.

#### Changes in release 5.2.4
  + Improved default templates for Opencart 2.
  + Fixed issue where vQmod broke Mollie's module code.

#### Changes in release 5.2.3
  + Support for OneCheckOut module.
  + Support for Dreamvention's Quick Checkout module.

#### Changes in release 5.2.2
  + Fixed order receipts not loading properly.
  + Fixed 'missing terms' warning on checkout page.

#### Changes in release 5.2.1
  + Support for ajax ('single page') checkout modules.

#### Changes in release 5.2.0
  + Support for Opencart 2.

#### Changes in release 5.1.7
  + Describe payment statuses more clear in admin settings.

#### Changes in release 5.1.6
  + Fixed an issue where creating orders in the backend would cause a JSON Parse error.

#### Changes in release 5.1.5
  + Resolved the problem that multiple payments could be created for one single order.
  + Fixed an issue where a successful payment can be changed to expired.

#### Changes in release 5.1.4
  + Places Mollie payment methods on the correct position (sorting order).
  + Now with support for Joomla-based OpenCart installations.
  + Fixed old PHP 5.2- regression bug.

#### Changes in release 5.1.3
  + Added support for custom catalog paths.
  + Improved order status updates before and after payments.
  + Fixed HTTP calls on HTTPS pages.
  + Added translations for payment methods. Now with French translations.

#### Changes in release 5.1.2
  + Support PHP version 5.2 and jQuery 1.7.

#### Changes in release 5.1.1
  + Webhook URL will now be configured automatically. No need to do it manually anymore.
  + If a customer's payment is still "Pending" when returning, the cart is now cleared and a pending message is printed.

#### Changes in release 5.1.0
  + Mollie payment methods are now shown as separate options.
  + iDEAL payment method immediately shows the bank selection dropdown.
  + Payment methods are displayed with their unique logos.
  + Correct order totals are calculated for non-EURO base currencies.

#### Wijzigigen in versie 5.0
  + De module gebruikt nu de nieuwe betalings-API van Mollie. Dit betekent dat de module naast [iDEAL](https://www.mollie.com/ideal/), nu
  ook [creditcard](https://www.mollie.com/creditcard/), [Mister Cash](https://www.mollie.com/bancontact/) en [paysafecard](https://www.mollie.com/paysafecard/)
  ondersteunt. Mocht een betaling om wat voor reden dan ook niet lukken, dat kan uw klant het gelijk nog een keer proberen. U hoeft hiervoor niets extra's
  te implementeren. In de toekomst zullen ook nog nieuwe betaalmethodes toegevoegd worden. Deze zijn dan direct beschikbaar in uw webshop.
  + Het instellingenscherm in de admin toont nu gelijk of de module correct kan communiceren met de Mollie API. Hierdoor kunnen we u beter helpen wanneer
  er problemen zijn met de module.
  + Verbeter foutafhandeling en communicatie met Mollie.

#### Upgraden vanaf versie 4.8 of eerder

Upgraden vanaf een eerdere versie is eenvoudig:

0. Download de module van [GitHub](https://github.com/mollie/OpenCart/releases).
0. Upload de bestanden in de map `admin` en `catalog` naar uw webserver. U moet hierbij de bestanden die er al zijn met dezelfde naam overschrijven. Er zijn ook enkele nieuwe bestanden.
0. Ga naar de instellingenpagina van de module in OpenCart. Kopieer hier uw _Test API key_ in het daarvoor bestemde veld. U vindt uw _Test API key_ in het Mollie Beheer onder "[Websiteprofielen](https://www.mollie.com/beheer/account/profielen/)".
0. Voer een testbetaling uit om te verifiëren of alles werkt.
0. Als alles goed werkt, vervang dan de _Test API key_ door de _Live API key_ in uw OpenCart omgeving.

#### Wijzigingen in versie 4.8
  + Na een succesvolle betaling, wordt de klant doorgestuurd naar de standaard "geslaagde betaling"-pagina van OpenCart.
  + Vanaf deze versie wordt de kwaliteit van de module bewaakt door de open source continuous integration server [Travis CI](https://travis-ci.org/mollie/OpenCart)

#### Wijzigingen in versie 4.7
  + Geef duidelijkere foutmeldingen indien er iets misgaat bij het opzetten van de betaling
  + Los een probleem op waardoor de sorteervolgorde niet werkte en iDEAL altijd als bovenste module verscheen

#### Wijzigingen in versie 4.6
  + Sommige vertalingen ontbraken / waren incorrect.

#### Wijzigingen in versie 4.5
  + Probleem opgelost waardoor het winkelmandje na een succesvolle betaling niet leeggemaakt werd.

#### Wijzigingen in versie 4.4
  + De module werkt nu op servers met gedateerde root certificaten.
  + Automatische tests werken nu ook op PHP 5.4.
  + Stuur geen email naar de klant wanneer de klant de betalingsomgeving bij de bank verlaat zonder te betalen.
  + Voorkom mixed-content waarschuwingen in de admin.

#### Wijzigingen in versie 4.3
  + De module verstuurt niet langer emails vóórdat de betaling is afgerond, klanten ontvangen een email wanneer de
betaling is ontvangen.
  + De module werkt nu direct op servers met een incorrect geconfigureerde OpenSSL installatie.
  + Minimumbedrag voor transacties aangepast in verband met de BTW verhoging van 1 oktober 2012.

#### Wijzigingen in versie 4.2
  + Het is niet langer nodig om handmatig tabellen in de database aan te maken, de module regelt dit zelf.
