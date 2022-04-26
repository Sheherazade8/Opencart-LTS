<?php
/**
 * @version		$Id: tax_rate.php 6115 2021-03-14 10:15:06Z mic $
 * @package		Language Translation German Backend
 * @author		mic - https://osworx.net
 * @copyright	2021 OSWorX
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// has to be here!
$help_common			= '<br><ul style="font-size: 0.8em; font-weight: normal; margin-top: 15px; list-style: disclosure-closed;"><li>Name: Begriff wie er im Warenkorb, Rechnung usw. angezeigt wird</li><li>Betrag: abhängig von der Berechnungsart hier Wert angeben - nur Zahlen &amp; Punkt</li><li>Berechnungsart: Fixbetrag oder Prozent - jeweils gerechnet vom Nettowarenwert</li><li>Kundengruppe: für welche Kundengruppe(n) soll dieser Steuersatz gelten</li><li>Geozone: für welche Länder gilt dieser Steuersatz<br>(wenn noch nicht vorhanden, dann <i>Menü > System > Lokale Einst. >> Geozonen</i> und neu anlegen)</li></ul>';

// Heading
$_['heading_title']			= 'Steuersatz';

// Text
$_['text_success']			= 'Datensatz erfolgreich bearbeitet';
$_['text_list']				= 'Übersicht';
$_['text_add']				= 'Neu' . $help_common;
$_['text_edit']				= 'Bearbeiten' . $help_common;
$_['text_percent']			= 'Prozent';
$_['text_amount']			= 'Fixbetrag';

// Column
$_['column_name']			= 'Name';
$_['column_rate']			= 'Betrag';
$_['column_type']			= 'Berechnungsart';
$_['column_geo_zone']		= 'Geozone';
$_['column_date_added']		= 'Erstellt';
$_['column_date_modified']	= 'Geändert';
$_['column_action']			= 'Aktion';

// Entry
$_['entry_name']			= 'Name';
$_['entry_rate']			= 'Betrag';
$_['entry_type']			= 'Berechnungsart';
$_['entry_customer_group']	= 'Kundengruppe';
$_['entry_geo_zone']		= 'Geozone';

// Error
$_['error_permission']		= 'Keine Rechte für diese Aktion';
$_['error_tax_rule']		= 'Steuersatz kann nicht gelöscht werden da er aktuell %s Steuerklasse(n) zugeordnet ist';
$_['error_name']			= 'Name muss zwischen 3 und 32 Zeichen lang sein';
$_['error_rate']			= 'Betrag erforderlich';