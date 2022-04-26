<?php
/**
 * @version		$Id: paypal.php 6115 2021-03-14 10:15:06Z mic $
 * @package		Language Translation German Backend
 * @author		mic - https://osworx.net
 * @copyright	2021 OSWorX
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// Text
$_['text_title']					= 'PayPal (Express, Karte)';
$_['text_paypal_express']			= 'PayPal Express';
$_['text_paypal_card']				= 'PayPal Karte';
$_['text_wait']						= 'Bitte warten ..';
$_['text_order_message']			= 'PayPal Käuferschutz - %s';

// Entry
$_['entry_card_number']				= 'Kartennummer';
$_['entry_expiration_date']			= 'Ablaufdatum';
$_['entry_cvv']						= 'CVV';

// Button
$_['button_pay']					= 'Bezahlen mit Karte';

// Error
$_['error_warning']					= 'Bitte das Formular auf Fehler überprüfen';
$_['error_3ds_error']				= 'Während der 3D-Secure-Genehmigung trat ein Fehler auf';
$_['error_3ds_skipped_by_buyer']	= '3D-Secure-Genehmigung wurde übersprungen';
$_['error_3ds_failure']				= 'Die Aufgabe wurde entweder nicht gelöst oder die Karte ist nicht genehmigt';
$_['error_3ds_undefined']			= 'Kartenaussteller benötigt kein 3D-Secure';
$_['error_3ds_bypassed']			= '3D-Secure wurde übersprungen';
$_['error_3ds_unavailable']			= 'Ausstellende Bank kann die Genehmigung nicht abschließen';
$_['error_3ds_attempted']			= 'Karte oder ausstellende Bank nimmt nicht am 3D-Secure Verfahren teil';
$_['error_3ds_card_ineligible']		= 'Karte kann nicht am 3D-Secure Verfahren teilnehmen';
$_['error_payment']					= 'Bitte entweder eine andere Zahlungsart auswählen oder <a href="%s" target="_blank">uns kontaktieren</a>.';
$_['error_timeout'] 	  			= 'Wir bedauern, aber PayPal ist aktuell überbelastet .. bitte später nochmal probieren. Vielen Dank.';