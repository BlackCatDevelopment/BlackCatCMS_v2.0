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

$FORMS = array(
    'edit_user' => array(
        // ----- general settings -----
        array(
            'type'     => 'legend',
            'label'    => 'General',
        ),
        array(
            'type'     => 'text',
            'name'     => 'username',
            'label'    => 'Name',
        ),
        array(
            'type'     => 'text',
            'name'     => 'display_name',
            'label'    => 'Display name',
        ),
        array(
            'type'     => 'checkbox',
            'name'     => 'active',
            'value'    => '1',
            'label'    => 'active',
        ),
        array(
            'type'     => 'checkbox',
            'name'     => 'tfa_enabled',
            'value'    => '1',
            'label'    => 'Two factor authentication enabled',
        ),
        array(
            'type'     => 'text',
            'name'     => 'home_folder',
            'label'    => 'Home folder',
        ),
        array(
            'type'     => 'legend',
            'label'    => 'Contact data',
        ),
        array(
            'type'     => 'text',
            'name'     => 'email',
            'label'    => 'eMail address',
            'allowed'  => 'email'
        ),
        array(
            'type'     => 'text',
            'name'     => 'language',
            'label'    => 'Language',
        ),
    ),
);