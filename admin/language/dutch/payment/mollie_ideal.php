<?php

/**
 * Copyright (c) 2012, Mollie B.V.
 * All rights reserved. 
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met: 
 * 
 * - Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright 
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH 
 * DAMAGE. 
 *
 * @category    Mollie
 * @package     Mollie_Ideal
 * @author      Mollie B.V. (info@mollie.nl)
 * @version     v4.4
 * @copyright   Copyright (c) 2012 Mollie B.V. (http://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 * 
 **/

// Heading
$_['heading_title']         = 'iDEAL via Mollie';

// Text 
$_['text_payment']          = "Betaling";
$_['text_success']          = "Gelukt: Je hebt de iDEAL instellingen aangepast!";
$_['text_mollie_ideal']     = '<a onclick="window.open(\'https://www.mollie.nl\');"><img src="http://www.mollie.nl/images/badge-ideal-small.png" alt="iDEAL via Mollie" title="iDEAL via Mollie" style="border:0px" /></a>';

// Entry
$_['entry_status']          = "Status: <br/><span class='help'>De betaalmodule activeren</span>";
$_['entry_testmode']        = "Testmode: <br/><span class='help'>Gebruik de testmode om betalingen te testen zonder een echte betaling te doen</span>";
$_['entry_partnerid']       = "Mollie partner ID: <br/><span class='help'>Uw Mollie partner ID. Op dit account wordt de betaling toegevoegd. U kunt uw Mollie partner ID [<a target='new' href='https://www.mollie.nl/beheer/account/'>hier</a>] vinden</span>";
$_['entry_profilekey']      = "Profilekey: <br/><span class='help'>Vul het websiteprofiel in die u wilt gebruiken<br/>[ <a href='https://www.mollie.nl/beheer/account/profielen/' target='_blank'>Bekijk uw profielen</a> ]</span>";
$_['entry_description']     = "Beschrijving: <br/><span class='help'>Hiermee kunt u in maximaal 29 karakters een beschrijving meegegeven aan de betaling die op het dagafschrift van uw klant wordt weergeven. Zorg dus dat hier iets staat dat de lading dekt, zoals een ordernummer. TIP: Gebruik '%' voor het tonen van het ordernummer (Denk er wel aan dat % meerdere karakters kan bevatten, naargelang de lengte van het ordernummer in uw systeem)</span>";
$_['entry_total']           = "Minimaal bestelbedrag: <br/><span class='help'>Minimale bedrag voor iDEAL wordt weergeven bij een bestelling (MEEGEGEVEN IN CENTEN!)</span>";
$_['entry_sort_order']      = "Sorteervolgorde:";

// Info
$_['entry_module']          = "Module:";
$_['entry_status']          = "Module Status:";
$_['entry_version']         = "<a href='https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/' target='_blank'>iDEAL</a> versie 4.4";
$_['entry_support']         = "Support:";

// Error
$_['error_permission']      = "Waarschuwing: u hebt geen rechten om de betalingsmodule iDEAL via Mollie te wijzigen!";
$_['error_partnerid']       = "Het Mollie partner ID is vereist";
$_['error_profilekey']      = "Een Profilekey is vereist";
$_['error_description']     = "Een omschrijving is vereist";
$_['error_total']           = "Een minimaal bestelbedrag is verplicht. TIP: Vul hier 118 in, standaard werkt iDEAL voor u niet onder dit bedrag.";

// Status
$_['entry_failed_status']    = 'Mislukte Status:';
$_['entry_canceled_status']  = 'Geannuleerde Status:';
$_['entry_expired_status']   = 'Verlopen Status:';
$_['entry_pending_status']   = 'Afwachting Status:';
$_['entry_processing_status']= 'Verwerking Status:';
$_['entry_processed_status'] = 'Verwerkt Status:';
