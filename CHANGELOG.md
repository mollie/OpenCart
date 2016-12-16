![Mollie](https://www.mollie.com/files/Mollie-Logo-Style-Small.png)

# Changelog #

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
  ook [creditcard](https://www.mollie.com/creditcard/), [Mister Cash](https://www.mollie.com/mistercash/) en [paysafecard](https://www.mollie.com/paysafecard/)
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
