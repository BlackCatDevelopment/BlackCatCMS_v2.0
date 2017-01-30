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

if (!class_exists('CAT_Backend_Groups'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Groups extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function addmember()
        {
print_r($_REQUEST);
        }   // end function addmember()

        public static function create()
        {
            if(!CAT_Object::user()->hasPerm('groups_add'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $name  = $val->sanitizePost('group_name');
            $desc  = $val->sanitizePost('group_description');
            if(CAT_Groups::getInstance()->exists($name))
                CAT_Helper_JSON::printError('A group with the same name already exists!');
            CAT_Groups::getInstance()->addGroup($name,$desc);
        }

        /**
         * delete a group; requires the group id as route param
         *    example: /groups/delete/99
         * prints JSON result (success or error) to STDOUT
         *
         * @access public
         * @return void
         **/
        public static function delete()
        {
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('groups_delete'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $id    = $val->sanitizePost('id');
            if(!CAT_Groups::getInstance()->exists($id))
                CAT_Helper_JSON::printError('No such group!');
            $group = CAT_Groups::getInstance()->getGroup($id);
            if($group['builtin']=='Y')
                CAT_Helper_JSON::printError('Built-in elements cannot be removed!');
            $res   = CAT_Groups::getInstance()->removeGroup($id);
            CAT_Object::json_result($res,($res?'':'Failed!'),($res?true:false));
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function deleteuser()
        {
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('groups_users'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $id   = $self->router()->getParam();
            $user = CAT_User::getInstance($id);
            if($user->hasGroup($id))
            {
            }
        }   // end function deleteuser()
        

        /**
         * edit group attribute set by param 'name'
         *
         * @access public
         * @return void
         **/
        public static function edit()
        {
            if(!CAT_Object::user()->hasPerm('groups_edit'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $val = CAT_Helper_Validate::getInstance();
            $field = $val->sanitizePost('name');
            $id    = $val->sanitizePost('pk');
            $value = $val->sanitizePost('value');
            CAT_Groups::getInstance()->set($field,$value,$id);
        }   // end function edit()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function index($id=NULL)
        {
            $self = self::getInstance();
            $params = $self->router()->getParams();
            if(count($params))
            {
                switch($params[0])
                {
                    case 'deleteuser':
                        $user = new CAT_User($params[1]);
                        if($user->hasGroup($id))
                        {
                         #   $self->db()->query(
                         #       'DELETE FROM `:prefix:rbac_usergroups` WHERE `user_id`=? AND `group_id`=?',
                         #       array($params[1],$id)
                         #   );
                        }
                        break;
                }
                if(self::asJSON())
                {
                    echo header('Content-Type: application/json');
                    echo CAT_Helper_JSON::printSuccess('Success');
                    return;
                }
            }

            $tpl_data = array(
                'groups' => CAT_Groups::getInstance()->getGroups(),
            );
            foreach($tpl_data['groups'] as $i => $g)
            {
                $members = CAT_Groups::getInstance()->getMembers($g['group_id']);
                $roles   = CAT_Roles::getInstance()->getRoles(array('for'=>'group','id'=>$g['group_id']));
                $tpl_data['groups'][$i]['member_count'] = count($members);
                $tpl_data['groups'][$i]['role_count']   = count($roles);
            }
            CAT_Backend::print_header();
            $self->tpl()->output('backend_groups', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function users()
        {
            if(!CAT_Object::user()->hasPerm('groups_users'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $self  = self::getInstance();
            $id    = $self->router()->getParam();
            $users = CAT_Groups::getInstance()->getMembers($id);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($users,true);
                return;
            }

            $tpl_data = array(
                'members' => $users
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_groups_members', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function users()

    } // class CAT_Helper_Groups

} // if class_exists()