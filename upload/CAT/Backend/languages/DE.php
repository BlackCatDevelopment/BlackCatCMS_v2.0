<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2017 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

include CAT_ENGINE_PATH.'/languages/DE.php';

$LANG = array_merge($LANG,array(
    // --------------- Backend ---------------
    'Close all'         => 'Alle schließen',
    'Keep open'         => 'Offenhalten',
    'Logout'            => 'Abmelden',
    'Open all'          => 'Alle öffnen',
    'User Profile'      => 'Benutzerprofil',
    'Built-in objects must not be removed' => 'Standard-Objekte können nicht gelöscht werden',

    // --------------- Backend -> Session ---------------
    'Close Backend' => 'Backend schließen',
    'Close the Backend and open Homepage (Frontend)' => 'Das Backend schließen und die Hompage öffnen (Frontend)',
    'Do you wish to login again?' => 'Möchten Sie sich neu anmelden?',
    'Login with the given credentials and stay on current page' => 'Mit den angegebenen Daten anmelden und auf der aktuellen Seite bleiben',
    'Remaining session time' => 'Verbleibende Sessionzeit',
    'Session timed out!' => 'Die Session ist abgelaufen!',

    // --------------- Backend -> Menu ---------------
    'Addons'            => 'Erweiterungen',
    'Admintools'        => 'Admin Werkzeuge',
    'Datetime'          => 'Datum &amp; Zeit',
    'Groups'            => 'Gruppen',
    'Headers'           => 'Kopfdateien',
    'Media'             => 'Dateien',
    'Pages'             => 'Seiten',
    'Preferences'       => 'Profil',
    'Roles'             => 'Rollen',
    'Security'          => 'Sicherheit',
    'Settings'          => 'Einstellungen',

    // --------------- Backend -> Dashboard ---------------
    'Add widget'        => 'Widget hinzufügen',
    'Remove widget'     => 'Widget entfernen',
    'Reset Dashboard'   => 'Dashboard zurücksetzen',
    'Do you really want to remove this widget?' => 'Soll dieses Widget wirklich entfernt werden?',
    'Do you really want to reset the Dashboard? All your customization settings will be lost!' => 'Soll das Dashboard wirklich zurückgesetzt werden? Alle persönlichen Einstellungen gehen verloren!',
    'No addable widgets found.' => 'Es wurden keine hinzufügbaren Widgets gefunden.',
    'There are no widgets on your dashboard.' => 'Es befinden sich keine Widgets auf diesem Dashboard.',
    'Use widget setting' => 'Widget-Voreinstellung verwenden',
    'You can add widgets to your dashboard by clicking on the [Add widget] button' => 'Um diesem Dashboard Widgets hinzuzufügen, bitte die [Widget hinzufügen] Schaltfläche verwenden.',

    // --------------- Backend -> Pages ---------------
    'Add page'          => 'Neue Seite',
    'Add section'       => 'Sektion hinzufügen',
    'Actions'           => 'Aktionen',
    'Block number'      => 'Blocknr.',
    'Collapse all'      => 'Alle einklappen',
    'Date from'         => 'Datum von',
    'Date until'        => 'Datum bis',
    'deleted'           => 'gelöscht',
    'Delete section'    => 'Abschnitt löschen',
    'Edit content'      => 'Inhalt bearbeiten',
    'Every day'         => 'Täglich',
    'Expand all'        => 'Alle aufklappen',
    'Header files'      => 'Kopfdateien',
    'hidden'            => 'versteckt',
    'Linked page'       => 'Verknüpfte Seite',
    'Menu title'        => 'Menütitel',
    'Module'            => 'Erweiterung',
    'no name'           => 'kein Name',
    'No pages yet'      => 'Noch keine Seiten',
    'none'              => 'keine',
    'Page title'        => 'Seitentitel',
    'Parent page'       => 'Übergeordnete Seite',
    'Period of time'    => 'Zeitspanne',
    'Preview'           => 'Vorschau',
    'private'           => 'privat',
    'public'            => 'öffentlich',
    'registered'        => 'registriert',
    'Relations'         => 'Beziehungen',
    'Time of day'       => 'Uhrzeit',
    'Time from'         => 'Uhrzeit von',
    'Time until'        => 'Uhrzeit bis',
    'Visibility'        => 'Sichtbarkeit',
    'Add jQuery Plugin' => 'jQuery Plugin hinzufügen',
    'Add explicit Javascript file' => 'Ein bestimmtes Javascript hinzufügen',
    'Add explicit CSS file' => 'Eine bestimmte CSS Datei hinzufügen',
    'Change visibility' => 'Sichtbarkeit ändern',
    'Currently, no extra files are defined for this page.' => 'Zur Zeit sind keine zusätzlichen Dateien für diese Seite konfiguriert.',
    'Do you really want to delete this section?' => 'Soll dieser Abschnitt wirklich <strong>gelöscht</strong> werden?',
    'Do you really want to unlink the selected page?' => 'Soll diese Seitenbeziehung wirklich entfernt werden?',
    'Icon explanation' => 'Symbolerklärung',
    'If a section shall be visible between two dates, put the start and end date here.' => 'Wenn eine Sektion während einer gewissen Datumsspanne sichtbar sein soll, hier das Start- und Endedatum angeben.',
    "If a section shall be visible between X and Y o'clock every day, put the start and end times here." => 'Wenn eine Sektion nur zwischen X und Y Uhr jeden Tag sichbar sein soll, hier Start- und Ende-Uhrzeit angeben.',
    'If you set visibility to false, the section will <strong>not</strong> be shown. This means, all other settings - like periods of time - are ignored.' => 'Ist die Sichtbarkeit hier deaktiviert, wird diese Sektion <strong>nicht</strong> angezeigt. Alle anderen Einstellungen - z.B. eine Zeitspanne - werden ignoriert.',
    'Menu appearance' => 'Menüzugehörigkeit',
    'Move section to another page' => 'Sektion auf eine andere Seite verschieben',
    'No sections were found for this page' => 'Keine Sektionen für diese Seite gefunden',
    'Please enter max. 55 characters' => 'Bitte maximal 55 Zeichen',
    'Please note that there is a bunch of files that is loaded automatically, so there\'s no need to add them here.' => 'Bitte beachten, dass es eine Reihe von Dateien gibt, die automatisch geladen werden und daher hier nicht verwaltet werden können und müssen.',
    'public - visible for all visitors; registered - visible for configurable groups of visitors; ...' => 'öffentlich - für alle Besucher sichtbar; registriert - für eine einstellbare Gruppe von Besuchern sichtbar; ...',
    'Remove relation' => 'Beziehung entfernen',
    'See this page in the frontend; opens a new tab or browser window' => 'Diese Seite im Frontend ansehen; öffnet einen neuen Browser-Tab oder ein neues Fenster',
    'Select the menu the page belongs to. The menu select depends on the chosen template.' => 'Das Menü wählen, zu dem die Seite gehört. Die Auswahl ist abhängig vom eingestellten Template.',
    'Set publishing period' => 'Sichtbarkeits-Zeitraum bearbeiten',
    'System default' => 'Standardeinstellung',
    'Template variant' => 'Template-Variante',
    'The description should be a nice &quot;human readable&quot; text having 70 up to 156 characters.' => 'Die Beschreibung sollte ein &quot;menschenlesbarer&quot; Text mit mindestens 70 und bis zu 156 Zeichen sein.',
    'The (main) language of the page contents.' => 'Die (hauptsächliche) Sprache der Seiteninhalte.',
    'The (main) type (section) for the page contents.' => 'Haupttyp (Sektion) des Seiteninhalts',
    'The menu title is used for the navigation menu. Hint: Use short but descriptive titles.' => 'Der Menütitel wird für das Navigationsmenü verwendet. Tipp: Kurze aber aussagekräftige Titel verwenden.',
    'The page is accessible for all visitors and shows up in the navigation by default' => 'Die Seite ist für alle Besucher sichtbar und erscheint üblicherweise auch im Menü',
    'The page is accessible for visitors who know the exact address and can be found by the keyword search, but does not show up in the navigation by default' => 'Die Seite ist sichtbar, wenn man die Adresse kennt, und wird von der Suchfunktion gefunden, erscheint aber nicht im Menü',
    'The page is not accessible in the frontend at all, but can be edited in the backend' => 'Die Seite kann von Besuchern nicht aufgerufen, aber im Backend bearbeitet werden',
    'The page is only accessible to registered users and is not shown in the navigation for non-registered users' => 'Die Seite ist nur für berechtigte Benutzer sichtbar und erscheint nur im Menü, wenn der Benutzer angemeldet ist',
    'The page is only accessible to registered users; the page shows up in the navigation by default' => 'Die Seite ist nur für berechtigte Benutzer sichtbar; sie erscheint üblicherweise auch im Menü',
    'The page was deleted but can be recovered' => 'Die Seite ist gelöscht, kann aber wiederhergestellt werden',
    'The position of the page in the page tree' => 'Die Position der Seite im Seitenbaum',
    'The title should be a nice &quot;human readable&quot; text having 30 up to 55 characters.' => 'Der Seitentitel sollte ein &quot;menschenlesbarer&quot; Text mit mindestens 30 und höchstens 55 Zeichen sein.',
    'These settings are page based, to manage global settings, goto Settings -> Header files.' => 'Diese Einstellungen sind seitenbasiert, globale Einstellungen können unter Einstellungen -> Kopfdateien vorgenommen werden.',
    'This section is marked as deleted.' => 'Dieser Abschnitt ist als gelöscht markiert.',
    'Use {language_menu()} in your frontend template to show links to the pages listed below.' => 'Das Markup {language_menu()} im Frontend-Template erzeugt Links zu den untenstehenden Seiten.',
    'You can link any page to other pages in different languages that have the same content.' => 'Jede Seite kann mit Seiten in anderen Sprachen, die den gleichen Inhalt haben, verknüpft werden.',
    'You can manage Javascript- and CSS-Files resp. jQuery plugins to be loaded into the page header here.' => 'Hier können Javascript- und CSS-Dateien bzw. jQuery Plugins verwaltet werden, die zusätzlich in den Seitenkopf geladen werden sollen.',
    'You may override the system settings for the template here' => 'Systemweite Template-Einstellung für diese Seite ändern',
    'You may override the system settings for the template variant here' => 'Systemweite Template-Varianten-Einstellung für diese Seite ändern',
    'You may recover it by clicking on the recover icon.' => 'Durch Anklicken des Wiederherstellungs-Icons kann der Abschnitt wiederhergestellt werden.',

    // --------------- Backend -> Addons ---------------
    'Catalog'           => 'Katalog',
    'Installed'         => 'Installiert',
    'Languages'         => 'Sprachen',
    'Modules'           => 'Erweiterungen',
    'Upgraded'          => 'Aktualisiert',
    'Not (yet) installed' => '(Noch) nicht installiert',
    'Type to filter by text...' => 'Zum Filtern tippen...',


    // --------------- Backend -> Roles ---------------
    'Add role'          => 'Rolle hinzufügen',
    'Delete role'       => 'Rolle löschen',
    'Permissions'       => 'Rechte',
    'Role ID'           => 'Rollen ID',
    'Title'             => 'Name',
    'Users'             => 'Benutzer',
    'Brief description' => 'Kurze Beschreibung',
    'Do you really want to delete this role?' => 'Wollen Sie diese Rolle wirklich löschen?',
    'Manage role permissions' => 'Rechte bearbeiten',

    // --------------- Backend -> Users ---------------
    'Add users'         => 'Benutzer hinzufügen',
    'active'            => 'aktiv',
    'Built in'          => 'Standard (mitgeliefert)',
    'Contact'           => 'Kontaktdaten',
    'Delete user'       => 'Benutzer löschen',
    'Display name'      => 'Anzeigename',
    'Edit user'         => 'Benutzer ändern',
    'eMail address'     => 'eMail Adresse',
    'Home folder'       => 'Homeverzeichnis',
    'Login name'        => 'Loginname',
    'Tfa enabled'       => 'Zwei-Faktor-Authentifizierung aktiviert',
    'User ID'           => 'Benutzer ID',
    'Choose the users you wish to add and click [Save]' => 'Die gewünschten Benutzer auswählen und [Speichern] anklicken',
    'Do you really want to delete this user?' => 'Soll dieser Benutzer wirklich gelöscht werden?',
    'Edit group members' => 'Gruppenmitglieder bearbeiten',
    'Two-Step Authentication disabled' => 'Zwei-Faktor Authentifizierung deaktiviert',
    'Two-Step Authentication enabled' => 'Zwei-Faktor Authentifizierung aktiviert',

    // --------------- Backend -> Groups ---------------
    'Add group members' => 'Gruppenmitglieder hinzufügen',
    'Delete group'      => 'Gruppe löschen',
    'Group ID'          => 'Gruppen ID',
    'Click here to edit the group name' => 'Zum Ändern des Gruppennamens hier klicken',
    'Do you really want to remove this group member?' => 'Soll dieses Gruppenmitglied wirklich aus der Gruppe entfernt werden?',
    'Group member successfully removed' => 'Gruppenmitglied erfolgreich entfernt',
    'Do you really want to delete this group?' => 'Soll diese Gruppe wirklich gelöscht werden?',
    'Manage group members' => 'Gruppenmitglieder verwalten',
    'No addable users found' => 'Keine passenden Benutzer gefunden',
    'Remove group member' => 'Gruppenmitglied entfernen',
    'Users of group "Administrators" and users that are already member of this group cannot be added.' => 'Benutzer der Gruppe "Administratoren" und Benutzer, die bereits Mitglied dieser Gruppe sind, können nicht hinzugefügt werden.',

    // --------------- Backend -> Media ---------------
    'All types'         => 'Alle Dateitypen',
    'Bits per sample'   => 'Auflösung',
    'Date'              => 'Datum',
    'Filename'          => 'Dateiname',
    'Folders'           => 'Verzeichnisse',
    'Images'            => 'Bilder',
    'Resolution X'      => 'Breite in Pixel',
    'Resolution Y'      => 'Höhe in Pixel',
    'Size'              => 'Größe',
    'Unzip'             => 'Entpacken',

    // --------------- Backend -> Permissions ---------------
    'Access to groups'  => 'Zugang zur Gruppenverwaltung',
    'Access to pages'   => 'Zugang zur Seitenverwaltung',
    'Access to roles'   => 'Zugang zur Rollenverwaltung',
    'Access to tools'   => 'Zugang zu den Admin-Tools',
    'Access to users'   => 'Zugang zur Benutzerverwaltung',
    'Backend access'    => 'Backend-Zugang',
    'Create a new page' => 'Neue Seite anlegen',
    'Create a new role' => 'Neue Rolle anlegen',
    'Create new group'  => 'Neue Gruppe anlegen',
    'Create new users'  => 'Neue Benutzer anlegen',
    'Delete groups'     => 'Gruppen löschen',
    'Delete pages'      => 'Seiten löschen',
    'Delete roles'      => 'Rollen löschen',
    'Delete users'      => 'Benutzer löschen',
    'Edit intro page'   => 'Einstiegsseite bearbeiten',
    'Edit user data'    => 'Vorhandene Benutzer bearbeiten',
    'Permission'        => 'Berechtigung',
    'See all users'     => 'Vorhandene Benutzer auflisten',
    'See defined roles' => 'Vorhandene Rollen auflisten',
    'See the page tree' => 'Seitenbaum sehen',

    'Access to global dashboard' => 'Zugang zum globalen Dashboard',
    'Access to media section' => 'Zugang zur Medienverwaltung',
    'Access to permissions' => 'Zugang zur Rechteverwaltung',
    'Add new permissions' => 'Neue Rechte anlegen',
    'Create root pages (level 0)' => 'Root-Seiten anlegen (Level 0)',
    'Edit group membership' => 'Gruppenmitgliedschaften bearbeiten',
    'Edit page settings' => 'Seiteneinstellungen bearbeiten',
    'Manage group members' => 'Gruppenmitglieder verwalten',
    'Manage role permissions' => 'Rechte in Rollen bearbeiten',
    'Modify existing pages' => 'Vorhandene Seiten bearbeiten',
    'See available admin tools' => 'Vorhandene Admin-Tools auflisten',
    'See available user groups' => 'Vorhandene Gruppen auflisten',
    'See defined permissions' => 'Vorhandene Rechte auflisten',
    'User can edit his profile' => 'Benutzer kann eigenes Profil bearbeiten',

    // --------------- Backend -> Settings ----------
    'Common'            => 'Allgemein',
    'Default charset'   => 'Standard Encoding / Charset',
    'Default language'  => 'Standard-Sprache',
    'Default template'  => 'Standard-Template',
    'Default theme'     => 'Standard-Template',
    'Favicon tilecolor' => 'Hintergrundfarbe Kachel',
    'Manage Favicon'    => 'Favicons verwalten',
    'Media directory'   => 'Medien-Verzeichnis',
    'Network'           => 'Netzwerk',
    'Trash enabled'     => 'Seitenmülleimer eingeschaltet',
    'Website title'     => 'Seitentitel',
    'Wysiwyg editor'    => 'WYSIWYG Editor',
    'Default template variant' => 'Variante',
    'Default theme variant' => 'Variante',
    'If enabled, deleted pages and sections can be recovered.'
        => 'Eingeschaltet: Seiten und Sektionen können wiederhergestellt werden',
    "If your server is placed behind a proxy (i.e. if you're using BC for an Intranet), set the name here."
        => 'Wenn sich der Server hinter einem Proxy befindet (z.B. wenn BC für ein Intranet verwendet wird), hier den Namen eintragen.',

    // ---------- Backend -> Settings -> Favicons ----------
    'android'           => 'Android',
    'apple'             => 'Apple',
    'desktop'           => 'PCs',
    'webapp'            => 'Web Applikationen',
    'windows'           => 'Windows (ab Version 8.0)',
    'Below you can see which Favicon files BlackCat CMS is looking for to populate the page header. A checkmark shows if the file is available.'
        => 'BlackCat CMS sucht nach den unten aufgeführten Dateien, um damit den Seitenkopf zu befüllen. Gefundene Dateien sind anhgehakt.',
    'The CMS will also look for a &quot;browserconfig.xml&quot; file (for Internet Explorer >= 11) and manifest.json (for Web Apps).'
        => 'Das CMS schaut außerdem nach einer &quot;browserconfig.xml&quot; (für Internet Explorer >= 11) und manifest.json (für Web Apps).',
    'While there are several different sizes (for older devices in most cases), we only look for the files with the highest possible pixel rate, as these will still look good when sized down by the device.'
        => 'Obwohl eine Vielzahl unterschiedlicher Größen möglich ist (oft für ältere Geräte), sucht BlackCat CMS nur nach den Dateien mit der jeweils höchsten Auflösung, da diese auch dann gut aussehen, wenn sie vom Gerät herunterskaliert werden.',

));