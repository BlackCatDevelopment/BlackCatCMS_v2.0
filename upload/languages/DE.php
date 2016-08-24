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
    'Confirm'       => 'Bestätigen',
    'Create new'    => 'Neu erstellen',
    'Delete'        => 'Löschen',
    'Description'   => 'Beschreibung',
    'Please note'   => 'Hinweis',
    'Remove'        => 'Entfernen',
    'Save changes'  => 'Speichern',
    'Type'          => 'Typ',

    // --------------- Backend ---------------
    'Close all'     => 'Alle schließen',
    'Keep open'     => 'Offenhalten',
    'Logout'        => 'Abmelden',
    'Open all'      => 'Alle öffnen',
    'User Profile'  => 'Benutzerprofil',

    // --------------- Backend -> Menu ---------------
    'Addons'        => 'Erweiterungen',
    'Admintools'    => 'Admin Werkzeuge',
    'Datetime'      => 'Datum &amp; Zeit',
    'Groups'        => 'Gruppen',
    'Headers'       => 'Kopfdateien',
    'Media'         => 'Dateien',
    'Pages'         => 'Seiten',
    'Preferences'   => 'Profil',
    'Roles'         => 'Rollen',
    'Security'      => 'Sicherheit',
    'Settings'      => 'Einstellungen',

    // --------------- Backend -> Dashboard ---------------
    'Do you really want to remove this widget from your dashboard?' => 'Soll dieses Widget wirklich vom Dashboard entfernt werden?',
    'Remove widget' => 'Widget entfernen',

    // --------------- Backend -> Pages ---------------
    'Actions'       => 'Aktionen',
    'Collapse all'  => 'Alle einklappen',
    'Edit content'  => 'Inhalt bearbeiten',
    'Expand all'    => 'Alle aufklappen',
    'hidden'        => 'versteckt',
    'Preview'       => 'Vorschau',
    'private'       => 'privat',
    'public'        => 'öffentlich',
    'Visibility'    => 'Sichtbarkeit',
    'Icon explanation' => 'Symbolerklärung',
    'This action is not available' => 'Diese Aktion ist nicht verfügbar',

    // --------------- Backend -> Addons ---------------
    'Installed'     => 'Installiert',
    'Upgraded'      => 'Aktualisiert',

    // --------------- Backend -> Roles ---------------
    'Role ID'       => 'Rollen ID',
    'Title'         => 'Name',
    'Permissions'   => 'Rechte',
    'Users'         => 'Benutzer',
    'Manage role permissions' => 'Rechte bearbeiten',

    // --------------- Backend -> Users ---------------
    'Add users'     => 'Benutzer hinzufügen',
    'Delete user'   => 'Benutzer löschen',
    'Display name'  => 'Anzeigename',
    'Choose the users you wish to add and click [Save]' => 'Die gewünschten Benutzer auswählen und [Speichern] anklicken',
    'Do you really want to delete this user?' => 'Soll dieser Benutzer wirklich gelöscht werden?',
    'Edit group members' => 'Gruppenmitglieder bearbeiten',
    'Login name'    => 'Loginname',
    'Two-Step Authentication disabled' => 'Zwei-Faktor Authentifizierung deaktiviert',
    'Two-Step Authentication enabled' => 'Zwei-Faktor Authentifizierung aktiviert',
    'User ID'       => 'Benutzer ID',


    // --------------- Backend -> Groups ---------------
    'Add group members' => 'Gruppenmitglieder hinzufügen',
    'Click here to edit the group name' => 'Zum Ändern des Gruppennamens hier klicken',
    'Delete group'  => 'Gruppe löschen',
    'Do you really want to remove this group member?' => 'Soll dieses Gruppenmitglied wirklich aus der Gruppe entfernt werden?',
    'Group ID'      => 'Gruppen ID',
    'Group member successfully removed' => 'Gruppenmitglied erfolgreich entfernt',
    'Do you really want to delete this group?' => 'Soll diese Gruppe wirklich gelöscht werden?',
    'Manage group members' => 'Gruppenmitglieder verwalten',
    'No addable users found' => 'Keine passenden Benutzer gefunden',
    'Remove group member' => 'Gruppenmitglied entfernen',
    'Users of group "Administrators" and users that are already member of this group cannot be added.' => 'Benutzer der Gruppe "Administratoren" und Benutzer, die bereits Mitglied dieser Gruppe sind, können nicht hinzugefügt werden.',

    // --------------- Backend -> Permissions ---------------
    'Access to global dashboard' => 'Zugang zum globalen Dashboard',
    'Access to groups' => 'Zugang zur Gruppenverwaltung',
    'Access to media section' => 'Zugang zur Medienverwaltung',
    'Access to pages' => 'Zugang zur Seitenverwaltung',
    'Access to permissions' => 'Zugang zur Rechteverwaltung',
    'Access to roles' => 'Zugang zur Rollenverwaltung',
    'Access to tools' => 'Zugang zu den Admin-Tools',
    'Access to users' => 'Zugang zur Benutzerverwaltung',
    'Add new permissions' => 'Neue Rechte anlegen',
    'Backend access' => 'Backend-Zugang',
    'Create a new page' => 'Neue Seite anlegen',
    'Create a new role' => 'Neue Rolle anlegen',
    'Create a new user group' => 'Neue Gruppe anlegen',
    'Create new users' => 'Neue Benutzer anlegen',
    'Create root pages (level 0)' => 'Root-Seiten anlegen (Level 0)',
    'Delete groups' => 'Gruppen löschen',
    'Delete pages' => 'Seiten löschen',
    'Delete roles' => 'Rollen löschen',
    'Delete users' => 'Benutzer löschen',
    'Edit group membership' => 'Gruppenmitgliedschaften bearbeiten',
    'Edit intro page' => 'Einstiegsseite bearbeiten',
    'Edit page settings' => 'Seiteneinstellungen bearbeiten',
    'Edit user data' => 'Vorhandene Benutzer bearbeiten',
    'Manage group members' => 'Gruppenmitglieder verwalten',
    'Manage role permissions' => 'Rechte in Rollen bearbeiten',
    'Modify existing pages' => 'Vorhandene Seiten bearbeiten',
    'Permission' => 'Berechtigung',
    'See all users' => 'Vorhandene Benutzer auflisten',
    'See available admin tools' => 'Vorhandene Admin-Tools auflisten',
    'See available user groups' => 'Vorhandene Gruppen auflisten',
    'See defined permissions' => 'Vorhandene Rechte auflisten',
    'See defined roles' => 'Vorhandene Rollen auflisten',
    'See the page tree' => 'Seitenbaum sehen',
    'User can edit his profile' => 'Benutzer kann eigenes Profil bearbeiten',

    // --------------- Login page ---------------
    'Login'         => 'Anmelden',
    'If you have Two Step Authentication enabled, you will have to enter your one time password here. Leave this empty otherwise.' => 'Sofern Zwei-Faktor-Authentifizerung aktiviert ist, hier das Einmal-Kennwort eingeben. Andernfalls dieses Feld leer lassen.',
    'Please enter your code' => 'Bitte den Code eingeben',
    'Scan the following image with your app and enter the code below' => 'Bitte das Image mit einer entsprechenden App scannen und den Code unten eintragen',
    'Two-Step Authentication' => 'Zwei-Faktor-Authentifizierung',
    'Your username' => 'Benutzername',
    'Your OTP code (PIN)' => 'OTP Code (PIN)',
    'Your password' => 'Kennwort',
);

// include old lang files
if(defined('WB2COMPAT'))
{
    global $HEADING, $TEXT, $MESSAGE, $SETTINGS;
    require dirname(__FILE__).'/old/'.$language_code.'.php';
}