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
    'be_page_settings' => array(
        // ----- general settings -----
        array(
            'type'     => 'legend',
            'label'    => 'General',
        ),
        array(
            'type'     => 'select',
            'name'     => 'parent',
            'label'    => 'Parent page',
            'after'    => 'The position of the page in the page tree',
            'options'  => array(),
        ),
        array(
            'type'     => 'select',
            'name'     => 'visibility',
            'label'    => 'Visibility',
            'after'    => 'public - visible for all visitors; registered - visible for configurable groups of visitors; ...',
            'options'  => array(),
        ),
        array(
            'type'     => 'select',
            'name'     => 'page_menu',
            'label'    => 'Menu appearance',
            'after'    => 'Select the menu the page belongs to. The menu select depends on the chosen template.',
            'options'  => array(),
        ),
        array(
            'type'     => 'select',
            'name'     => 'template',
            'label'    => 'Template',
            'after'    => 'You may override the system settings for the template here',
            'options'  => array(),
        ),
        array(
            'type'     => 'select',
            'name'     => 'template_variant',
            'label'    => 'Template variant',
            'after'    => 'You may override the system settings for the template variant here',
            'options'  => array(),
        ),

        // ----- meta and SEO settings -----
        array(
            'type'     => 'legend',
            'label'    => 'META / SEO',
        ),
        array(
            'name'     => 'page_title',
            'label'    => 'Page title',
            'title'    => 'Please enter max. 55 characters',
            'after'    => 'The title should be a nice &quot;human readable&quot; text having 30 up to 55 characters.',
            'pattern'  => '.{1,55}',
            'required' => true,
        ),
        array(
            'name'     => 'menu_title',
            'label'    => 'Menu title',
            'after'    => 'The menu title is used for the navigation menu. Hint: Use short but descriptive titles.',
        ),
        array(
            'name'     => 'description',
            'label'    => 'Description',
            'title'    => 'Please enter max. 156 characters',
            'pattern'  => '.{0,156}',
            'after'    => 'The description should be a nice &quot;human readable&quot; text having 70 up to 156 characters.',
        ),
        array(
            'type'     => 'select',
            'name'     => 'language',
            'label'    => 'Language',
            'after'    => 'The (main) language of the page contents.',
            'options'  => array(CAT_Registry::get('DEFAULT_LANGUAGE')),
        ),

        // ----- buttons -----
        array(
            'type'     => 'submit',
            'label'    => 'Submit changes',
        ),
    ),

    // ----- page based header files -----
    'be_page_headerfiles_plugin' => array(
        // plugin
        array(
            'type'     => 'hidden',
            'name'     => 'page_id',
            'id'       => 'page_id',
            'value'    => NULL,
        ),
        array(
            'type'     => 'select',
            'name'     => 'jquery_plugin',
            'label'    => 'Add jQuery Plugin',
            'options'  => array(),
        ),
        // ----- buttons -----
        array(
            'type'     => 'submit',
            'label'    => 'Submit changes',
        ),
    ),
    // specific js
    'be_page_headerfiles_js' => array(
        array(
            'type'     => 'hidden',
            'name'     => 'page_id',
            'id'       => 'page_id',
            'value'    => NULL,
        ),
        array(
            'type'     => 'select',
            'name'     => 'jquery_js',
            'label'    => 'Add explicit Javascript file',
            'options'  => array(),
        ),
        // ----- buttons -----
        array(
            'type'     => 'submit',
            'label'    => 'Submit changes',
        ),
    ),
    'be_page_headerfiles_css' => array(
        array(
            'type'     => 'hidden',
            'name'     => 'page_id',
            'id'       => 'page_id',
            'value'    => NULL,
        ),
        array(
            'type'     => 'select',
            'name'     => 'jquery_css',
            'label'    => 'Add explicit CSS file',
            'options'  => array(),
        ),
        // ----- buttons -----
        array(
            'type'     => 'submit',
            'label'    => 'Submit changes',
        ),
    ),

);

