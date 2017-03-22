<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2016 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

if (!class_exists('CAT_Backend_Settings'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Settings extends CAT_Object
    {
        // log level
        protected static $loglevel       = \Monolog\Logger::EMERGENCY;
        #protected static $loglevel  = \Monolog\Logger::DEBUG;
        protected static $instance       = NULL;
        protected static $perm_prefix    = 'settings_';
        private   static $regions        = NULL;
        private   static $avail_settings = NULL;

        public static function __callstatic($name,$arguments)
        {
            call_user_func([__CLASS__, 'index'] ,$name);
        }   // end function __callstatic()

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
            {
                self::$instance = new self();
                self::addLangFile(__dir__.'/languages');
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * get available settings
         **/
        public static function getSettings()
        {
            if(!self::$avail_settings)
            {
                $self = self::getInstance();
                $data = $self->db()->query(
                    'SELECT * FROM `:prefix:settings` AS `t1` '
                    . 'JOIN `:prefix:forms_fieldtypes` AS `t2` '
                    . 'ON `t1`.`fieldtype`=`t2`.`id` '
                    . 'WHERE `is_editable`=?',
                    array('Y')
                );
                if($data)
                {
                    self::$avail_settings = $data->fetchAll();
                }
            }
            return self::$avail_settings;
        }   // end function getSettings()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $settings = self::getSettings();
            $self     = self::getInstance();
            $region   = $self->router()->getParam();

            if(!$region) $region = $self->router()->getFunction();

            $form = CAT_Backend::initForm();

            $form->createForm('settings');
            $form->setAttr('class','');

            if(!self::$regions)
            {
                self::$regions = array('index');
                $regions = CAT_Backend::getMainMenu(4);
                foreach(array_values($regions) as $r)
                    array_push(self::$regions,$r['name']);
            }

            if(!in_array($region,self::$regions)) // invalid call!
                $region = 'index';

            // filter settings by region
            //$settings = CAT_Helper_Array::ArrayFilterByKey($settings, 'region', $region);
            $settings = CAT_Helper_Array::filter($settings,'region',$region);

            if(is_array($settings) && count($settings))
            {
                $lastlegend = '';
                foreach($settings as $item)
                {
                    if($item['fieldset'] != $lastlegend)
                    {
                        $form->addElement(array(
                            'type'  => 'legend',
                            'label' => $self->lang()->translate(self::humanize($item['fieldset'])),
                            'class' => '',
                        ));
                        $lastlegend = $item['fieldset'];
                    }
                    $element = array(
                        'type'  => (
                              strlen($item['fieldtype'])
                            ? $item['fieldtype']
                            : 'text'
                        ),
                        'name'  => $item['name'],
                        'label' => (
                              strlen($item['fieldlabel'])
                            ? $item['fieldlabel']
                            : $self->lang()->translate(self::humanize($item['name']))
                        ),
                        'default' => (
                              strlen($item['default_value'])
                            ? $item['default_value']
                            : ''
                        ),
                    );

                    switch($element['type'])
                    {
                        case 'radiogroup':
                            $element['options'] = array('y'=>'yes','n'=>'no');
                            break;
                        case 'checkbox':
                            break;
                    }

                    $elem = $form->addElement($element);
                    if(strlen($item['fieldhandler']))
                    {
                        $handler = $item['fieldhandler'];
//******************************************************************************
// Funktioniert derzeit nur mit einem Parameter und loest keine Variablen auf!
//******************************************************************************
                        if(strlen($item['params']))
                            $data = call_user_func_array($handler,array($item['params']));
//******************************************************************************
                        else
                            $data = $handler();

                        if($elem instanceof wblib\wbFormsElementSelect)
                        {
                            $elem->setAttr('options',$data);
                        }
                        else
                        {
                            $elem->setAttr('value',$data);
                        }
                    }

                }
            }
            else
            {
                $form->setError('no settings or invalid region!');
            }

            // set current data
            $form->setData(CAT_Object::loadSettings());

            CAT_Backend::print_header();
            $self->tpl()->output(
                'backend_settings',
                array(
                    'region' => $self->lang()->t(ucfirst($region)),
                    'form'   => $form->getForm(),
                )
            );
            CAT_Backend::print_footer();
        }   // end function mail()
        

    } // class CAT_Helper_Settings

} // if class_exists()