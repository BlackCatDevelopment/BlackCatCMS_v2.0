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

if (!class_exists('CAT_Helper_FormBuilder'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_FormBuilder extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;

        public static function generate($name,$items,$legend_key,$data)
        {
            $form = CAT_Backend::initForm();
            $form->createForm($name);

            $lastlegend = '';
            foreach($items as $item)
            {
                if($item[$legend_key] != $lastlegend)
                {
                    $form->addElement(array(
                        'type'  => 'legend',
                        'label' => self::lang()->translate(self::humanize($item[$legend_key])),
                        'class' => '',
                    ));
                    $lastlegend = $item[$legend_key];
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
                        : self::lang()->translate(self::humanize($item['name']))
                    ),
                    'default' => (
                          strlen($item['default_value'])
                        ? $item['default_value']
                        : ''
                    ),
                    'after'   => (
                          strlen($item['helptext'])
                        ? $item['helptext']
                        : ''
                    ),
                    'required' => (
                          (isset($item['is_required']) && $item['is_required'] == 'Y')
                        ? true
                        : false
                    ),
                );

                switch($element['type'])
                {
                    case 'labeledbutton':
                        $element['text'] = self::lang()->translate('Go');
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: convert handler name to route
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        // check for route
                        if(strlen($item['fieldhandler']) && substr($item['fieldhandler'],0,1)=='/') {
                            $element['onclick'] = "javascript:parent.location='".$item['fieldhandler']."';return false;";
                        }
                        break;
                    case 'radiogroup':
                        $element['options'] = array('y'=>'yes','n'=>'no');
                        break;
                    case 'checkbox':
                        if(strlen($item['default_value']))
                        {
                            $element['value'] = $item['default_value'];
                        }
                        break;
                }

                $elem = $form->addElement($element);
                if(strlen($item['fieldhandler']) && $element['type'] != 'labeledbutton')
                {
                    $handler = $item['fieldhandler'];
                    if(strlen($item['params']))
                    {
                        $params = $item['params'];
                        if(substr_count($params,','))
                            $params = explode(',',$params);
                        else
                            $params = array($params);
                        $data = call_user_func_array($handler,$params);
                    }
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

            // add buttons
            $form->addElement(array(
                'type' => 'submit',
                'label' => 'Save changes',
            ));
            $form->addElement(array(
                'type'  => 'button',
                'label' => 'Cancel',
                'value' => 'cancel',
            ));

            // set current data
            $form->setData($data);

            return $form;

        }

    } // class CAT_Helper_FormBuilder
} // if class_exists()