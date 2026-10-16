<p align="center">
  <img src="https://info.mollie.com/hubfs/github/opencart/logo.png" width="128" height="128"/>
</p>
<h1 align="center">Mollie for OpenCart</h1>

# Installation

- Download the latest release on the [OpenCart Releases](https://github.com/mollie/OpenCart/releases)-page.
- Upload all files from the "upload" folder in the zip to the root of your opencart shop or use the update button in the admin of your shop if you have an earlier version installed.
- The OpenCart 3 (and earlier) zip is meant for uploading the files (FTP) and for the update button in the module. It can not be uploaded through _Extensions > Installer_ of OpenCart 3, because that installer only accepts a limited set of folders. The OpenCart 4 zip contains `mollie.ocmod.zip`, which is the file to upload through _Extensions > Installer_ of OpenCart 4.
- From version 10.0.0 onwards the module will support both the OCmod and vQmod. You are free to use either of them as per your requirements. However we will automatically install (on upgrade or on fresh install) the one which is best suited for you.
- OCmod is supported from OpenCart 2.0.2.0 onwards. OpenCart 1.5.x has no OCmod, and OpenCart 2.0.0.0 - 2.0.1.1 either do not load the OCmod file from the system folder or apply it incorrectly. On those versions the module always uses the vQmod file, so vQmod has to be installed.
- To switch from vQmod to OCmod just rename the file "root-dir/system/mollie.ocmod.xml\_" to "root-dir/system/mollie.ocmod.xml", disable the vQmod file by renaming or deleting it. Finally refresh the modification from admin panel under "Extensions".
- To switch from OCmod to vQmod just rename the file "root-dir/vqmod/xml/mollie.xml\_" to "root-dir/vqmod/xml/mollie.xml", disable the OCmod file by renaming or deleting it. Finally refresh the modification from admin panel under "Extensions". Please make sure you have vQmod installed on your system before switching from OCmod to vQmod.
- You can find the latest version of vQmod on the vQmod [Releases page](https://github.com/vqmod/vqmod/releases).
- After a correct installation, Mollie payment methods should be visible in the _Payments_ overview.
- Click on _Install (green button or text)_ and then _Edit (blue button or text)_.
- Fill out your _Mollie API key_ on the settings tab. You can find your API key on your Mollie dashboard [Websiteprofiles](https://www.mollie.com/beheer/account/profielen/).
- If you are using Content Security Policy, you should whitelist the _js.mollie.com_ domain. We recommend using a strict CSP on your checkout.
- Opencart version 4 is still unstable and may have bugs that are not due to our module.

# Updating

- Use the update button on the Mollie settings page, or upload the files again like a new installation. Open the Mollie settings page afterwards. OpenCart 4 registers new events and database indexes there, and OpenCart 3 and earlier asks to choose OCmod or vQmod when that was not chosen yet. After updating OpenCart 3 and earlier, refresh the modifications under _Extensions > Modifications_.
- The update button of version 14.1.0 and earlier on OpenCart 4 can not install version 14.1.2, because it does not unpack the `mollie.ocmod.zip` inside the release file. Update to 14.1.2 once manually: unzip the release file and the `mollie.ocmod.zip` inside it, upload the folders `admin`, `catalog`, `system` and the file `install.json` to `extension/mollie/` of your shop (overwrite) and the folder `image/mollie` to `image/mollie`. The OpenCart installer refuses an extension that is already installed, so use an FTP upload for this. From 14.1.2 onwards the update button works again.
- On OpenCart 3 and earlier the update button of version 14.1.0 and earlier can leave both the OCmod and vQmod file active. Opening the Mollie settings page of 14.1.2 keeps the one that was chosen before.

# Next level payments, for everyone

Mollie is dedicated to making payments better for everyone. No need to spend weeks on
paperwork or security compliance procedures. No more lost conversions because you don’t
support a shopper’s favorite payment method or because they don’t feel safe. We made our
products and API expansive, intuitive, and safe for merchants, customers and developers
alike.

You can quickly integrate all major payment methods, wherever you need them. Simply drop
them ready-made into your OpenCart webshop with this powerful plugin.

- All major payment methods with just a single contract.
- Added reliability through multiple acquiring banks.
- Free machine learning fraud protection and 3-D Secure.

# Payment methods

- Credit Cards (Visa / MasterCard &amp; American Express)
- PayPal
- SEPA Direct Debits
- SEPA Bank Transfer
- iDeal | Wero
- Bancontact
- KBC/CBC Payment Button
- Belfius Payment Button
- CartaSi
- Cartes Bancaires
- Dutch giftcards
- EPS
- Klarna Pay Later
- Klarna Pay Now
- Klarna Slice It
- Przelewy24
- Apple Pay
- Vouchers
- IN3
- MyBank
- Billie
- Twint
- Blik
- Bancomat Pay
- Trustly
- Alma
- Riverty
- Payconiq
- Satispay
- Wero

Please go to the signup page to create a new Mollie account and start receiving payments in
a couple of minutes. Contact info@mollie.com if you have any questions or comments about
this plugin.

# Features

- Support for all available Mollie payment methods
- Multiple translations: English, Dutch, French, Danish, German, Italian, Norwegian, Portuguese, Spanish and Swedish
- Event log for debugging purposes
- Multi-Store support
- Multi-Language support
- Supports OC 1.5 and higher

# License

[BSD (Berkeley Software Distribution) License](http://www.opensource.org/licenses/bsd-license.php).
Copyright (c) 2018, Mollie B.V.

# Support

Module developed by Quality Works: [www.qualityworks.eu](https://www.qualityworks.eu) — mollie.support@qualityworks.eu — +31 85-7430150 <br />
Contact Mollie: [www.mollie.com/nl/about](https://www.mollie.com/nl/about) — info@mollie.com — +31 20-612 88 55

- [More info on iDEAL via Mollie](https://www.mollie.com/en/payments/ideal/)
- [More info on credit card via Mollie](https://www.mollie.com/en/payments/creditcard/)
- [More info on Bancontact via Mollie](https://www.mollie.com/en/payments/bancontact/)
- [More info on SEPA Bank transfer via Mollie](https://www.mollie.com/en/payments/banktransfer/)
- [More info on SEPA Direct debit via Mollie](https://www.mollie.com/en/payments/directdebit/)
- [More info on PayPal via Mollie](https://www.mollie.com/en/payments/paypal/)
- [More info on Belfius Direct Net via Mollie](https://www.mollie.com/en/payments/belfiusdirectnet/)
- [More info on Giftcards via Mollie](https://www.mollie.com/en/payments/gift-cards/)
- [More info on EPS via Mollie](https://www.mollie.com/en/payments/eps/)
- [More info on Klarna Pay Later via Mollie](https://www.mollie.com/en/payments/klarna-pay-later/)
- [More info on Klarna Pay Now via Mollie](https://www.mollie.com/en/payments/klarna-pay-now/)
- [More info on Klarna Slite It via Mollie](https://www.mollie.com/en/payments/klarna-slice-it/)
- [More info on Przelewy24 It via Mollie](https://www.mollie.com/en/payments/p24/)
- [More info on Apple Pay via Mollie](https://www.mollie.com/en/payments/apple-pay/)
- [More info on Vouchers via Mollie](https://www.mollie.com/en/payments/meal-eco-gift-vouchers/)
- [More info on iDeal IN3 via Mollie](https://www.mollie.com/en/payments/ideal-in3/)
- [More info on MyBank via Mollie](https://www.mollie.com/en/payments/mybank/)
- [More info on Billie via Mollie](https://www.mollie.com/en/payments/billie/)
- [More info on Twint via Mollie](https://www.mollie.com/en/payments/twint/)
- [More info on Blik via Mollie](https://www.mollie.com/en/payments/blik/)
- [More info on Bancomat Pay via Mollie](https://www.mollie.com/en/payments/bancomatpay/)
- [More info on Trustly Pay via Mollie](https://www.mollie.com/en/payments/trustly/)
- [More info on Alma Pay via Mollie](https://www.mollie.com/en/payments/alma/)
- [More info on Riverty Pay via Mollie](https://www.mollie.com/en/payments/riverty/)
- [More info on Payconiq Pay via Mollie](https://www.mollie.com/en/payments/payconiq/)
- [More info on Satispay Pay via Mollie](https://www.mollie.com/en/payments/satispay/)
- [More info on Multibanco via Mollie](https://www.mollie.com/en/payments/multibanco/)
- [More info on Bizum via Mollie](https://www.mollie.com/en/payments/bizum/)
- [More info on MB Way via Mollie](https://www.mollie.com/en/payments/mb-way/)
- [More info on Pay By Bank via Mollie](https://www.mollie.com/en/payments/pay-by-bank/)
- [More info on Swish via Mollie](https://www.mollie.com/en/payments/swish/)
- [More info on Wero via Mollie](https://www.mollie.com/en/payments/wero/)
