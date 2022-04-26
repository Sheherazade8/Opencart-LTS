<?php
/**
 * @version		$Id: klarna_invoice.php 6115 2021-03-14 10:15:06Z mic $
 * @package		Translation - Frontend
 * @author		mic - https://osworx.net
 * @copyright	2021 OSWorX
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// Text
$_['text_title']		= 'Klarna - Kauf auf Rechnung';
$_['text_terms_fee']	= '<span id="klarna_invoice_toc"></span> (+%s)<script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s\', charge: %s});</script>';
$_['text_terms_no_fee']	= '<span id="klarna_invoice_toc"></span><script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s\'});</script>';
$_['text_additional']	= 'Klarna benötigt weitere Infos um mit der Zahlung fortfahren zu können.';
$_['text_male']			= 'Mann';
$_['text_female']		= 'Frau';
$_['text_year']			= 'Jahr';
$_['text_month']		= 'Monat';
$_['text_day']			= 'Tag';
$_['text_comment']		= "Klarna Rechnungsnr.: %s\n%s/%s: %.4f";
$_['text_trems_description']    = 'Mit der Übermittlung der für die Abwicklung des Rechnungskaufes und einer Identitäts - und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine <a href="https://online.klarna.com/consent_de.yaws" target="_blank">Einwilligung</a> kann ich jederzeit mit Wirkung für die Zukunft widerrufen.';

// Entry
$_['entry_gender']		= 'Geschlecht';
$_['entry_pno']			= 'Sozialversicherungsnr.';
$_['entry_dob']			= 'Geburtsdatum';
$_['entry_phone_no']	= 'Telefonnr.';
$_['entry_street']		= 'Straße';
$_['entry_house_no']	= 'Hausnummer';
$_['entry_house_ext']	= 'Türnr.';
$_['entry_company']		= 'Firmennummer';

// Help
$_['help_pno']			= 'Bitte hier die Sozialversicherungsnummer angeben';
$_['help_phone_no']		= 'Hier eine erreichbare Telefonnummer angeben';
$_['help_street']		= 'Lieferung kann nur an eine gültige Adresse erfolgen';
$_['help_house_no']		= 'Bitte genaue Hausnummer angeben';
$_['help_house_ext']	= 'Bitte wenn vorhanden eine Türnummer angeben';
$_['help_company']		= 'Hier Firmenbuchnnummer angeben';

// Error
$_['error_deu_terms']		= 'Den Datenschutzbestimmungen von Klarna muss zugestimmt werden';
$_['error_address_match']	= 'Rechnungs- &amp; Zustelladresse müssen übereinstimmen';
$_['error_network']			= 'Fehler: Server momentan nicht erreichbar - bitte nochmals probieren';