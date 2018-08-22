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

namespace CAT\Helper;
use \CAT\Base as Base;
use \CAT\Helper\Page as HPage;
use \wblib\wbList\Tree as Tree;

if(!class_exists('\CAT\Helper\Menu',false))
{
	class Menu extends Base
	{
        protected static $loglevel  = \Monolog\Logger::EMERGENCY;

        /**
         * for object oriented use
         **/
        public function __call($method, $args)
            {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         *
         * @access public
         * @return
         **/
        public static function getLanguageMenu(array &$options = array())
        {
             $pid = NULL;
             self::checkPageId($pid);
             #self::checkOptions($options);
             $pages = HPage::getLinkedByLanguage($pid);
             $lb    = Base::lb();
             $lb->set('more_info','language');
             return $lb->buildList($pages,$options);
        }   // end function getLanguageMenu()

        /**
         *
         * @access public
         * @return
         **/
        public static function show(int $id, $attr)
        {
            $pid = NULL;
            self::checkPageId($pid);

            // get menu settings
            $stmt = self::db()->query(
                  'SELECT `type_name`, `attribute`, `value` FROM `:prefix:menus` AS `t1` '
                . 'LEFT JOIN `:prefix:menu_options` AS `t2` '
                . 'ON `t1`.`menu_id`=`t2`.`menu_id` '
                . 'JOIN `:prefix:menu_types` AS `t3` '
                . 'ON `t1`.`type_id`=`t3`.`type_id` '
                . 'WHERE `t1`.`menu_id`=?',
                array($id)
            );

            $data = $stmt->fetchAll();
            $settings = array();
            if(is_array($data) && count($data)>0) {
                $settings['type'] = ( isset($data[0]['type_name']) ? $data[0]['type_name'] : 'fullmenu' );
                foreach($data as $i => $item) {
                    $settings[$item['attribute']] = $item['value'];
                }
            }

            $formatter = '\wblib\wbList\Formatter\ListFormatter';
            switch($settings['type']) {
                case 'breadcrumb':
                    $formatter = '\wblib\wbList\Formatter\BreadcrumbFormatter';
                    break;
            }

            $renderer = new $formatter();
            $renderer->setMaxDepth(self::tree($settings['type'])->getDepth());
            $renderer->setOption('id_prefix','area','li');

            foreach(array_values(array('ul','li','a')) as $tag) {
                if(isset($settings[$tag.'_level_classes'])) {
                    $renderer->setLevelClasses($tag,$settings[$tag.'_level_classes']);
                }
                $knownClasses = $renderer->getKnownClasses($tag);
                if(is_array($knownClasses)) {
                    for($i=0;$i<count($knownClasses);$i++) {
                        if(isset($settings[$tag.'_'.$knownClasses[$i]])) {
                            $renderer->setClasses($tag,$knownClasses[$i],$settings[$tag.'_'.$knownClasses[$i]],true);
                        }
                    }
                }
            }

            return $renderer->render(self::tree($settings['type']));
        }   // end function show()

        /**
         * makes sure that we have a valid page id; the visibility does not
         * matter here
         *
         * @access protected
         * @param  integer   $id (reference!)
         * @return void
         **/
        protected static function checkPageId(&$pid=NULL)
        {
            if($pid===NULL) {
                if(self::router()->isBackend()) {
                    $pid = \CAT\Backend::getArea(1);
                } else {
                    $pid = \CAT\Page::getID();
                }
            }
            #if($pid===0)    $pid = \CAT\Helper\Page::getRootParent($page_id);
        }   // end function checkPageId()

        /**
         * 
         **/
        protected static function tree(string $type)
        {
            if(self::router()->isBackend()) {
                $menu = \CAT\Backend::getMainMenu();
                $rootid = 0;
                $pid = NULL;
                self::checkPageId($pid);

                // some areas (like page -> edit) do not have an entry in
                // the backend_areas table, so for the breadcrumb menu, we
                // have to add them here
                if($type=='breadcrumb') {
                    // current controller
                    $curr = self::router()->getController();
                    // if the controller is not in the menu...
                    $seen = false;
                    for($i=0;$i<count($menu);$i++) {
                        if(isset($menu[$i]['controller']) && $menu[$i]['controller']==$curr) {
                            $seen = true;
                            break;
                        }
                    }
                    if(!$seen) {
                        $temp = explode('\\',(self::router()->getController()));
                        end($temp);
                        $area = $temp[key($temp)];
                        $menu[] = array(
                            'id' => $area,
                            'name' => $area,
                            'position' => 1,
                            'parent' => 0,
                            'level' => 1,
                            'controller' => self::router()->getController(),
                            'title' => self::lang()->t(ucfirst($area)),
                            'href' => '#'
                        );
                        $menu[] = array(
                            'id' => self::router()->getFunction(),
                            'name' => self::router()->getFunction(),
                            'position' => 2,
                            'parent' => $area,
                            'level' => 2,
                            'controller' => self::router()->getController(),
                            'title' => self::lang()->t(ucfirst(self::router()->getFunction())),
                            'href' => '#',
                            'is_current' => true,
                        );
                        $rootid = $area;
                        $pid = self::router()->getFunction();
                    }
                }

                return new Tree(
                    $menu,
                    array('value'=>'title','linkKey'=>'href','root_id'=>$rootid,'current'=>$pid)
                );
            }

            return new Tree(
                HPage::getPages(),
                array('id'=>'page_id','value'=>'menu_title','linkKey'=>'href')
            );
        }


    }
}
