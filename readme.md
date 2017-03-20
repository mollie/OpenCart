![Mollie](https://www.mollie.com/files/Mollie-Logo-Style-Small.png)

**Let op:** Deze versie van de module is alleen geschikt voor OpenCart 2.3 en hoger. Zie [deze branch](https://github.com/mollie/OpenCart/tree/opencart-2.2) voor de versie die geschikt is voor OpenCart modules 2.2 en lager.

# Installatie [![Build Status](https://travis-ci.org/mollie/OpenCart.png)](https://travis-ci.org/mollie/OpenCart) #
**Let op:** voor de installatie van deze module is FTP-toegang tot je webserver benodigd. Heb je hier geen ervaring mee? Laat de installatie van deze module dan over aan je websitebouwer of serverbeheerder.

+ Download op de [OpenCart Releases](https://github.com/mollie/OpenCart/releases)-pagina de nieuwste release.
+ Kopieër de gedownloade mappen `admin` en `catalog` naar de bestaande OpenCart-installatie op je server.
+ Ga naar uw OpenCart AdminPanel (Beheerpagina).
+ Ga in het menu naar _Extentions_ en selecteer _Payments_.
+ Na een correcte afhandeling zou onze Betaalmethode zichtbaar moeten zijn in het _Payments_ overzicht.
+ Klik vervolgens op _Install_ en daarna op _Edit_.
+ Vul je _Mollie API key_ in en bewaar de instellingen. Je vindt de API key in uw Mollie Beheer onder [Websiteprofielen](https://www.mollie.com/beheer/account/profielen/).

# Over Mollie #
Via [Mollie](https://www.mollie.com/) is gemakkelijk wereldwijd online betaalmethodes aan te sluiten zonder de gebruikelijke technische en administratieve rompslomp. Mollie geeft op ieder moment toegang tot je transactieoverzichten en andere statistieken. [Mollie](https://www.mollie.com/) is gestart door developers en verwerkt voor meer dan 20.000 websites de online betalingen.

# Ondersteunde betaalmethodes #
### iDEAL ###
Met [iDEAL](https://www.mollie.com/ideal/) kunt u vertrouwd, veilig en gemakkelijk uw online aankopen afrekenen. iDEAL is het systeem dat u direct koppelt aan uw internetbankierprogramma bij een online aankoop.
Via [Mollie](https://www.mollie.com/) is iDEAL gemakkelijk aan te sluiten zonder de gebruikelijke technische en administratieve rompslomp. Mollie geeft u op ieder moment toegang tot uw transactieoverzichten en andere statistieken. Tevens is het mogelijk per e-mail of SMS een notificatie te ontvangen bij elke gelukte betaling. [Mollie](https://www.mollie.nl/) is hierdoor dus een perfecte partner op het gebied van iDEAL en is het dan ook niet verbazingwekkend dat [Mollie](https://www.mollie.nl/) ondertussen op meer dan 20.000 websites iDEAL-betalingen mag verzorgen.

### Creditcard ###
[Creditcard](https://www.mollie.com/creditcard/) is vrijwel de bekendste methode voor het ontvangen van betalingen met wereldwijde dekking. Doordat we onder andere de bekende merken Mastercard en Visa ondersteunen, zorgt dit direct voor veel potentiële kopers.

### Bancontact/Mister Cash ###
[Bancontact/Mister Cash](https://www.mollie.com/mistercash/) maakt gebruik van een fysieke kaart die gekoppeld is aan tegoed op een Belgische bankrekening. Betalingen via Bancontact/Mister Cash zijn gegarandeerd en lijken daarmee sterk op iDEAL in Nederland. Daarom is het uitermate geschikt voor uw webwinkel.

### SOFORT Banking ###
[SOFORT Banking](https://www.mollie.com/sofort/) is een in Duitsland zeer populaire betaalmethode. Betalingen zijn direct en niet storneerbaar, waarmee het sterk op het Nederlandse iDEAL lijkt. Daarom is het uitermate geschikt voor uw webwinkel.

### Overboekingen ###
[Overboekingen](https://www.mollie.com/banktransfer/) binnen de SEPA zone ontvangen via Mollie. Hiermee kun je betalingen ontvangen van zowel particulieren als zakelijke klanten in meer dan 35 Europese landen.

### Eenmalige incasso ###
[Eenmalige incasso](https://www.mollie.com/directdebit/) binnen de SEPA zone ontvangen via Mollie. Een eenvoudige betaalmethode waarbij het bedrag achteraf van de rekening van de consument wordt afgeschreven.

### Bitcoin ###
[Bitcoin](https://www.mollie.com/bitcoin/) is een vorm van elektronisch geld. De bitcoin-euro wisselkoers wordt vastgesteld op het moment van de transactie waardoor het bedrag en de uitbetaling zijn gegarandeerd.

### PayPal ###
[PayPal](https://www.mollie.com/paypal/) is wereldwijd een zeer populaire betaalmethode. In enkele klikken kunt u betalingen ontvangen via een bankoverschrijving, creditcard of het PayPal-saldo.

### Belfius Direct Net ###
[Belfius Direct Net](https://www.mollie.com/belfiusdirectnet/) is een eigen betaaloplossing voor klanten van Belfius, één van de grootste banken van België.

### paysafecard ###
[paysafecard](https://www.mollie.com/paysafecard/) is de populairste prepaidcard voor online betalingen die veel door ouders voor hun kinderen wordt gekocht.

# Veelgestelde vragen #

## Ik kan Mollie niet kiezen bij het afrekenen! ##

Als je de _Live API key_ gebruikt, en iDEAL is nog niet voor je account geactiveerd, kan de module geen betaalmethode vinden om de bestelling mee af te ronden. De module is dan niet zichtbaar. Je kunt de _test API key_ gebruiken totdat iDEAL voor je account actief is.

Het is ook mogelijk dat het bedrag van de bestelling to hoog of te laag is voor de beschikbare betaalmethodes. Het is bijvoorbeeld niet mogelijk om betalingen hoger dan € 50.000 af te rekenen met iDEAL.

Als iDEAL geactiveerd is voor uw account en het bedrag klopt ook, controleer dan of de relevante betaalmethodes ingeschakeld staan bij het websiteprofiel in uw Mollie Beheer.

## Moet ik ook een redirect URL of webhook instellen? ##

Het is niet nodig een redirect URL of webhook in te stellen. Dat doet de module zelf automatisch bij elke order.

## Ik krijg een witte pagina tijdens het afrekenen. ##

Controleert of er fouten in het Fouten Logboek staan, je vindt dit in de OpenCart admin onder "Configuratie" en dan "Fouten Logboek". Sommige fouten, zoals het verkeerd instellen van een mailserver, stoppen het afrekenproces.

## Ik krijg de melding "The redirect location is invalid" tijdens het afrekenen. ##

Als e meerdere webwinkels hebt ingesteld op één OpenCart installatie, controleert dan of de instellingen "Winkel URL" en "Gebruik SSL" goed zijn ingesteld onder het tabblad "Algemeen" in de OpenCart admin. Als de instelling "Gebruik SSL" leeg laat, controleer dan ook of de instelling "Gebruik SSL" onder het tabblad "Server" uit staat.

## Ik krijg een popup met de melding "Error" tijdens het afrekenen. ##

De module is op dit moment niet compatible met de instelling "Gzip compressie-niveau". Schakel deze instelling  uit door de instelling op "0" te zetten onder het tabblad "Server" in de Opencart admin. De module zal het dan weer doen.

# Wil je meewerken aan deze module? #

Wil je helpen om onze plugin voor OpenCart nog beter te maken? Wij staan open voor [pull requests](https://github.com/mollie/OpenCart/pulls?utf8=%E2%9C%93&q=is%3Apr) voor onze module. 

Maar wat denk je er over om bij een [technology driven organisatie](https://www.mollie.com/nl/blog/post/werken-bij-mollie-sfeer-kansen-en-mogelijkheden/) aan de slag te gaan? Mollie is altijd op zoek naar developers en system engineers. [Check onze vacatures](https://www.mollie.com/nl/jobs) of [neem contact met ons op](mailto:personeel@mollie.com).

# Licentie #
[BSD (Berkeley Software Distribution) License](http://www.opensource.org/licenses/bsd-license.php).
Copyright (c) 2013, Mollie B.V.

# Ondersteuning #
Contact: [www.mollie.com/nl/about](https://www.mollie.com/nl/about) — info@mollie.com — +31 20-612 88 55

+ [Meer informatie over iDEAL via Mollie](https://www.mollie.com/ideal/)
+ [Meer informatie over credit card via Mollie](https://www.mollie.com/creditcard/)
+ [Meer informatie over Bancontact/Mister Cash via Mollie](https://www.mollie.com/mistercash/)
+ [Meer informatie over SOFORT Banking via Mollie](https://www.mollie.com/sofort/)
+ [Meer informatie over SEPA Bank transfer via Mollie](https://www.mollie.com/banktransfer/)
+ [Meer informatie over SEPA Direct debit via Mollie](https://www.mollie.com/directdebit/)
+ [Meer informatie over Bitcoin via Mollie](https://www.mollie.com/bitcoin/)
+ [Meer informatie over PayPal via Mollie](https://www.mollie.com/paypal/)
+ [Meer informatie over Belfius Direct Net via Mollie](https://www.mollie.com/belfiusdirectnet/)
+ [Meer informatie over paysafecard via Mollie](https://www.mollie.com/paysafecard/)

![Powered By Mollie](https://www.mollie.com/images/badge-betaling-medium.png)
