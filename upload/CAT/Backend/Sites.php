<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

namespace CAT\Backend;
use \CAT\Base as Base;

if (!class_exists('\CAT\Backend\Sites'))
{
    class Sites extends Base
    {
        protected static $loglevel    = \Monolog\Logger::EMERGENCY;

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            if(!self::user()->hasPerm('site_admin'))
                self::printFatalError('You are not allowed for the requested action!');

            $users = array();

            $stmt = self::db()->query(
                'SELECT * FROM `:prefix:sites` AS `t1` '
                . 'JOIN `:prefix:rbac_users` AS `t2` '
                . 'ON `t1`.`site_owner`=`t2`.`user_id` '
            );
            $sites = $stmt->fetchAll();

            if(self::user()->hasPerm('users_list')) {
                $stmt = self::db()->query(
                    'SELECT `user_id`, `username`, `display_name` FROM `:prefix:rbac_users` WHERE `active`=1 AND `username`<>?',
                    array('guest')
                );
                $temp = $stmt->fetchAll();
                if(is_array($temp) && count($temp)>0) {
                    for($i=0;$i<count($temp);$i++) {
                        $users[$temp[$i]['user_id']] = $temp[$i]['username'].' ('.$temp[$i]['display_name'].')';
                    }
                }
            }

            $form = \CAT\Helper\FormBuilder::generateForm('be_site',array());
            $form->getElement('site_owner')->setData($users);

            \CAT\Backend::print_header();
            self::tpl()->output(
                'backend_sites',
                array(
                    'sites'         => $sites,
                    'users'         => $users,
                    'new_site_form' => $form->render(1),
                )
            );
            \CAT\Backend::print_footer();
        }   // end function index()
        

    }
}