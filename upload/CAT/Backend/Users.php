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

if (!class_exists('CAT_Backend_Users'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Users extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;
        protected static $avail_settings = NULL;

        /**
         * Singleton
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * get the list of users that are members of the given group
         *
         * @access public
         * @return
         **/
        public static function bygroup()
        {
            if(!self::user()->hasPerm('users_membership'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $id   = self::router()->getParam();
            $data = CAT_Groups::getInstance()->getMembers($id);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
        }   // end function bygroup()

        /**
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            if(!self::user()->hasPerm('user_delete'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $userID = self::getUserID();
            $form   = self::renderForm(CAT_Helper_Users::getDetails($userID));
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode(array(
                    'form' => $form->getForm(),
                ),true);
                return;
            }
        }   // end function edit()

        /**
         *
         * @access public
         * @return
         **/
        public static function delete()
        {
            if(!self::user()->hasPerm('user_delete'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $id   = self::router()->getParam();
            if(CAT_Helper_Users::deleteUser($id)!==true)
            {
                if(self::asJSON())
                {
                    echo CAT_Helper_JSON::printError('Unable to delete the user');
                } else {
                    self::printFatalError('Unable to delete the user');
                }
            }
            else
            {
                if(self::asJSON())
                {
                    echo CAT_Helper_JSON::printSuccess('User successfully deleted');
                } else {
                    self::printMsg('User successfully deleted');
                }
            }
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $data  = CAT_Helper_Users::getUsers();
            if(count($data))
            {
                foreach($data as $i => $user)
                {
                    $data[$i]['groups'] = CAT_Helper_Users::getUserGroups($user['user_id']);
                }
            }
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
            $tpl_data = array(
                'users' => $data
            );
            CAT_Backend::print_header();
            self::tpl()->output('backend_users', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function notingroup()
        {
            if(!self::user()->hasPerm('users_membership'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $id    = self::router()->getParam();
            $users = CAT_Helper_Users::getUsers(array('group_id'=>$id,'not_in_group'=>true));
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($users,true);
                return;
            }
        }   // end function notingroup()

        /**
         *
         * @access public
         * @return
         **/
        public static function tfa()
        {
            if(!self::user()->hasPerm('users_edit'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $id   = self::router()->getParam();
            $user = new CAT_User($id);
            $tfa  = $user->get('tfa_enabled');
            $new  = ( $tfa == 'Y' ? 'N' : 'Y' );
            self::db()->query(
                'UPDATE `:prefix:rbac_users` SET `tfa_enabled`=? WHERE `user_id`=?',
                array($new,$id)
            );
            if(self::db()->isError())
            {
                echo CAT_Helper_JSON::printError('Unable to save');
            }
            else
            {
                echo CAT_Helper_JSON::printSuccess('Success');
            }
        }   // end function tfa()

        /**
         * get available settings
         **/
        protected static function getSettings()
        {
            if(!self::$avail_settings)
            {
                $data = self::db()->query(
                    'SELECT * FROM `:prefix:rbac_user_settings` AS `t1` '
                    . 'JOIN `:prefix:forms_fieldtypes` AS `t2` '
                    . 'ON `t1`.`fieldtype`=`t2`.`id` '
                    . 'WHERE `is_editable`=? '
                    . 'ORDER BY `fieldset` ASC, `position` ASC',
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
         * tries to retrieve 'user_id' by checking (in this order):
         *
         *    - $_POST['user_id']
         *    - $_GET['user_id']
         *    - Route param['user_id']
         *
         * also checks for numeric value
         *
         * @access private
         * @return integer
         **/
        protected static function getUserID()
        {
            $userID  = CAT_Helper_Validate::sanitizePost('user_id','numeric',NULL);

            if(!$userID)
                $userID  = CAT_Helper_Validate::sanitizeGet('user_id','numeric',NULL);

            if(!$userID)
                $userID = self::router()->getParam(-1);

            if(!$userID || !is_numeric($userID) || !CAT_Helper_Page::exists($userID))
                CAT_Object::printFatalError('Invalid data');

            return $userID;
        }   // end function getUserID()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function renderForm($data)
        {
            return CAT_Helper_FormBuilder::generate(
                'edit_user',
                self::getSettings(),
                'fieldset',
                $data
            );

/*
            $form = CAT_Backend::initForm();
            $form->loadFile('users.forms.php',__dir__.'/forms');
            $form->setForm('edit_user');
            $form->setAttr('class','tabbed');
            if(isset($data['extended']) && is_array($data['extended']))
            {
                $form->addElement(array(
                    'type' => 'legend',
                    'label' => 'Additional options',
                ));
                foreach($data['extended'] as $opt => $val)
                {
                    $form->addElement(array(
                        'type' => 'text',
                        'name' => $opt,
                        'label' => $opt,
                        'value' => $val
                    ));
                }
            }
            $form->setData($data);
            return $form->getForm();
*/
        }   // end function renderForm()
        

    } // class CAT_Helper_Users

} // if class_exists()