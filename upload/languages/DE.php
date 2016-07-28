<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

// Set the language information
$language_code        = 'DE';
$language_name        = 'Deutsch';
$language_version     = '2.0';
$language_platform    = '2.x';
$language_author      = 'Black Cat Development';
$language_license     = 'GNU General Public License';
$language_guid        = 'f49419c8-eb27-4a69-bffb-af61fce6b0c9';
$language_date_long   = '%A,|%d.|%B|%Y';
$language_date_short  = '%d.%m.%Y';
$language_time        = '%H:%I:%S';
$language_time_string = 'Uhr';

$LANG = array(
    // --------------- error messages ---------------
    'A group with the same name already exists!' => 'Es existiert bereits eine Gruppe mit diesem Namen!',
    'A role with the same name already exists!' => 'Es existiert bereits eine Rolle mit diesem Namen!',
    'Access denied' => 'Zugriff verweigert',
    "An internal error occured. We're sorry for inconvenience." => 'Es ist ein interner Fehler aufgetreten. Wir bitten um Entschuldigung.',
    'Authentication failed!' => 'Autorisierung fehlgeschlagen!',
    'Click to dismiss' => 'Zum Schließen anklicken',
    'No such user, user not active, or invalid password!' => 'Benutzer nicht vorhanden, nicht aktiv, oder Kennwort falsch!',
    'Sorry, there was an error' => 'Entschuldigung, ein Fehler ist aufgetreten',
    'Two step authentication failed!' => 'Zwei-Faktor Authentifizierung fehlgeschlagen!',
    'You are not allowed for the requested action' => 'Sie sind nicht berechtigt, die Aktion auszuführen',

    // --------------- Globals ---------------
    'Cancel'        => 'Abbrechen',
    'Create new'    => 'Neu erstellen',
    'Delete'        => 'Löschen',
    'Description'   => 'Beschreibung',
    'Remove'        => 'Entfernen',
    'Save changes'  => 'Speichern',
    'Type'          => 'Typ',

    // --------------- Backend ---------------
    'Logout'        => 'Abmelden',
    'User Profile'  => 'Benutzerprofil',

    // --------------- Backend -> Menu ---------------
    'Addons'        => 'Erweiterungen',
    'Admintools'    => 'Admin Werkzeuge',
    'Groups'        => 'Gruppen',
    'Media'         => 'Dateien',
    'Pages'         => 'Seiten',
    'Roles'         => 'Rollen',
    'Settings'      => 'Einstellungen',

    // --------------- Backend -> Dashboard ---------------
    'Do you really want to remove this widget from your dashboard?' => 'Soll dieses Widget wirklich vom Dashboard entfernt werden?',
    'Remove widget' => 'Widget entfernen',


    // --------------- Backend -> Addons ---------------
    'Installed'     => 'Installiert',
    'Upgraded'      => 'Aktualisiert',

    // --------------- Backend -> Roles ---------------
    'Role ID'       => 'Rollen ID',
    'Title'         => 'Name',
    'Permissions'   => 'Rechte',
    'Users'         => 'Benutzer',

    // --------------- Backend -> Users ---------------
    'Display name'  => 'Anzeigename',
    'Edit group members' => 'Gruppenmitglieder bearbeiten',
    'Login name'    => 'Loginname',
    'Two-Step Authentication disabled' => 'Zwei-Faktor Authentifizierung deaktiviert',
    'Two-Step Authentication enabled' => 'Zwei-Faktor Authentifizierung aktiviert',
    'User ID'       => 'Benutzer ID',

    // --------------- Backend -> Groups ---------------
    'Group ID'      => 'Gruppen ID',

    // --------------- Login page ---------------
    'Login'         => 'Anmelden',
    'Please enter your code' => 'Bitte den Code eingeben',
    'Scan the following image with your app and enter the code below' => 'Bitte das Image mit einer entsprechenden App scannen und den Code unten eintragen',
    'Two-Step Authentication' => 'Zwei-Faktor-Authentifizierung',
    'Your username' => 'Benutzername',
    'Your password' => 'Kennwort',
);

// include old lang files
if(defined('WB2COMPAT'))
{
    global $HEADING, $TEXT, $MESSAGE, $SETTINGS;
    require dirname(__FILE__).'/old/'.$language_code.'.php';
}