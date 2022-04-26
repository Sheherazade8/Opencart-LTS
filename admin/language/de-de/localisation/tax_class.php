<?php
/**
 * @version		$Id: tax_class.php 6115 2021-03-14 10:15:06Z mic $
 * @package		Language Translation German Backend
 * @author		mic - https://osworx.net
 * @copyright	2021 OSWorX
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// has to be here!
$help_common			= '<br><ul style="font-size: 0.8em; font-weight: normal; margin-top: 15px; list-style: disclosure-closed;"><li>Name: Begriff wie er in den Auswahllisten in der Verwaltung angezeigt wird</li><li>Beschreibung: interne Anmerkung</li><li>Steuersatz: sollte der gewünschte noch nicht vorhanden sein, ist er zuerst über das <i>Menü System > Lokale Einst. > Steuern >> Steuersätze anzulegen</i></li><li>Basiert auf: auf welcher Basis soll die Steuern berechnet werden<br><b>Achtung</b>: siehe dazu auch die passenden Einstellungen in der allgemeinen <b>Shopverwaltung</b> <i>Menü>System >> Einstellungen : Reiter Optionen :: Bereich Steuern</i> (wichtig, da ansonsten die Steuerberechnung nicht stimmt bzw. nicht angewendet wird!).</li><li>Priorität: Reihenfolge der Anwendung der Steuern</li></ul>';

// Heading
$_['heading_title']		= 'Steuerklasse';

// Text
$_['text_success']      = 'Datensatz erfolgreich bearbeitet';
$_['text_list']			= 'Übersicht';
$_['text_add']			= 'Neu' . $help_common;
$_['text_edit']			= 'Bearbeiten' . $help_common;
$_['text_tax_class']	= 'Steuerklasse';
$_['text_tax_rate']		= 'Steuern';
$_['text_shipping']		= 'Versandadresse';
$_['text_payment']		= 'Rechnungsadresse';
$_['text_store']		= 'Geschäftsadresse';

// Column
$_['column_title']      = 'Name';
$_['column_action']     = 'Aktion';

// Entry
$_['entry_title']       = 'Name';
$_['entry_description'] = 'Beschreibung';
$_['entry_rate']        = 'Steuersatz';
$_['entry_based']		= 'Basiert auf';
$_['entry_geo_zone']    = 'Geozone';
$_['entry_priority']    = 'Priorität';

// Error
$_['error_permission']  = 'Keine Rechte für diese Aktion';
$_['error_title']       = 'Name der Steuerklasse muss zwischen 3 und 32 Zeichen lang sein';
$_['error_description'] = 'Beschreibung muss zwischen 3 und 255 Zeichen lang sein';
$_['error_product']     = 'Steuerklasse kann nicht gelöscht werden da sie noch %s Produkt(en) zugeordnet ist';