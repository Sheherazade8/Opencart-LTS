<?php
/**
 * @version		$Id: backup.php 6115 2021-03-14 10:15:06Z mic $
 * @package		Language Language Translation German Backend
 * @author		mic - https://osworx.net
 * @copyright	2021 OSWorX
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// Heading
$_['heading_title']		= 'Sichern &amp; Wiederherstellen';

// Text
$_['text_success']		= 'Datenbank erfolgreich importiert';

$_['text_backup']		= 'Sichere Tabelle %s Eintrag %s bis %s Eintrag';
$_['text_restore']		= 'Wiederherstellung %s von %s';
$_['text_option']		= 'Sicherungsoptionen';
$_['text_history']		= 'Sicherungsverlauf';
$_['text_progress']		= 'Fortschritt';
$_['text_import']		= 'Für große Sicherungsdateien ist es besser, die SQL-Datei per FTP in den Ordner <b>system/storage/backup/</b> zu kopieren';

// Column
$_['column_filename']	= 'Dateiname';
$_['column_size']		= 'Größe';
$_['column_date_added']	= 'Erstellt';
$_['column_action']		= 'Aktion';

// Entry
$_['entry_progress']	= 'Fortschritt<br><i style="color:coral">Hinweis: es können nur Daten, keine Strukturangaben wiederhergestellt werden!</i>';
$_['entry_export']		= 'Export<br><i style="color:coral">Achtung! Es werden nur Daten gesichert, keine Angaben zur Struktur!</i>';

// Error
$_['error_permission']	= 'Keine Rechte für diese Aktion';
$_['error_export']		= 'Es muss mindestens 1 Tabelle zur Sicherung ausgewählt werden';
$_['error_table']		= 'Tabelle %s ist nicht erlaubt';
$_['error_file']		= 'Datei konnte nicht gefunden werden';
$_['error_directory']	= 'Verzeichnis nicht gefunden';

// < 3.1.x
// Tab
$_['tab_backup']		= 'Sichern';
$_['tab_restore']		= 'Wiederherstellen';