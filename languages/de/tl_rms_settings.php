<?php

/**
 * Contao Open Source CMS
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2012 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de> 
 * @package    rms (Release Management System)
 * @license    LGPL 
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms_settings']['control_group'] = array('Gruppe mit Freigabeberechtigung', 'Geben Sie die Benutzergruppe an welche eine Freigabeberechtigung haben soll.');
$GLOBALS['TL_LANG']['tl_rms_settings']['fallback_master_member']         = array('Fallback-RMS-Redakteur', 'Falls in irgendeinem Bereich zwar rms aktiviert wurde, aber kein zugehöriger rms-Redakteur existiert, wird dieser hier benachrichtigt.');
$GLOBALS['TL_LANG']['tl_rms_settings']['extent_emailto']         = array('zusätzliche Empfänger-E-Mail-Adressen', 'Hier können Sie ein oder mehrere Email-Adressen durch ein Komma getrennt eingeben. Diese Email-Adressen werden zusätzlich bei allen Änderungen benachrichtigt. Das Feld kann leer bleiben.');
$GLOBALS['TL_LANG']['tl_rms_settings']['ignore_fields']         = array('zu ignorierende Felder in der Vergleichsansicht', 'Tragen Sie hier, durch ein Komma getrennt die Feldnamen ein die vom rms-Modul ignoriert werden sollen. Der In muss der aus der Datenbank sein (z.B. rms_first_save).');
$GLOBALS['TL_LANG']['tl_rms_settings']['ignore_content_types']         = array('zu ignorierende Inhaltselemente', 'Tragen Sie hier, durch ein Komma getrennt die Inhaltselemententypen ein die vom rms-Modul ignoriert werden sollen. Der Inhaltselemententyp muss der aus der Datenbank sein (z.B. colsetStart,colsetPart,colsetEnd).');

