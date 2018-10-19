<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2018 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Module
   @package         catMenuAdmin

*/

namespace CAT\Addon;

use \CAT\Base as Base;
use \CAT\Helper\Validate as Validate;

if(!class_exists('\CAT\Addon\catMenuAdmin',false))
{
    final class coreMenuAdmin extends Tool
    {
        protected static $type        = 'tool';
        protected static $directory   = 'coreMenuAdmin';
        protected static $name        = 'Menu Manager';
        protected static $version     = '0.1';
        protected static $description = "Manage your menus here";
        protected static $author      = "BlackCat Development";
        protected static $guid        = "";
        protected static $license     = "GNU General Public License";

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {
            $id = self::getID();
            if(is_integer($id) && $id<>0) {
                return self::edit($id);
            }
            $stmt = self::db()->query(
                'SELECT `t1`.*, `t2`.`type_name` FROM `:prefix:menus` AS `t1` '
                . 'JOIN `:prefix:menu_types` AS `t2` '
                . 'ON `t1`.`type_id`=`t2`.`type_id` '
                . 'LEFT JOIN `:prefix:menu_on_site` AS `t3` '
                . 'ON `t1`.`menu_id`=`t3`.`menu_id` '
                . 'WHERE `t1`.`core`=? OR `t3`.`site_id`=? '
                . 'ORDER BY `core`,`t1`.`menu_id`',
                array('Y',CAT_SITE_ID)
            );

            $data = $stmt->fetchAll();
            // render
            return self::tpl()->get('tool',array('menus'=>$data));
        }   // end function tool()

        /**
         *
         * @access public
         * @return
         **/
        public static function analyze()
        {
            if(Validate::isSet('cancel')) {
                Validate::cleanup();
                return self::router()->reroute(CAT_BACKEND_PATH.'/'.str_ireplace('/'.__function__,'',self::router()->getRoute()));
            }

            $tpldata   = array('html'=>null,'startnode'=>'');
            $html      = Validate::sanitizePost('html');
            $startnode = '';

            if(strlen($html)) {
                $dom = new \DOMDocument;
                libxml_use_internal_errors(true);
                $dom->loadHTML($html);

                // startnode defaults to "nav", but can be overridden by param
                $tagName   = 'nav';
                $startnode = Validate::sanitizePost('startnode');
                if(strlen($startnode)) {
                    $elem = new \DOMDocument;
                    $elem->loadHTML($startnode);
                    // virtual body element
                    $body = $elem->documentElement->childNodes->item(0);
                    $node = $body->childNodes->item(0);
                    $tagName = $node->tagName;
                }

                $parent = $dom->getElementsByTagName($tagName)->item(0);
                $result = array();
                foreach(array_values(array('ul','li','a')) as $tag) {
                    $temp = self::analyzeTag($parent,$tag,$tagName);
                    $result = array_merge($result,$temp);
                }
                $tpldata = array(
                    'html'       => $html,
                    'result'     => $result,
                    'startnode'  => htmlentities($startnode),
                );

                $form = \CAT\Helper\FormBuilder::generateForm('menu_edit',$result);
                $form->addElement(
                    new \wblib\wbForms\Element\Select(
                        'menu_id',
                        array(
                            'label' => self::lang()->translate('Save to menu'),
                            'options' => array(
                                '' => self::lang()->translate('Create new'),
                            )
                        )
                    )
                );
                $tpldata['form'] = $form->render(true);

            }
            // render
            self::tpl()->setPath(CAT_ENGINE_PATH.'/modules/coreMenuAdmin/templates/default','backend');
            return self::tpl()->get('analyze',$tpldata);
        }   // end function analyze()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function analyzeTag($parent,$tag,$tagName)
        {
            $depth        = 1;
            $levelclasses = array();
            $result       = array();
            $elems        = $parent->getElementsByTagName($tag);

            foreach($elems as $i => $node) {
                $css = $node->getAttribute('class');
                if(strlen($css)) {
                    $nodedepth = self::getDepth($node,$tagName);
                    if($nodedepth>$depth) { $depth++; }
                    // check for known keys
                    $temp = explode(' ',$css);
                    for($i=count($temp)-1;$i>=0;$i--) {
                        if(in_array($temp[$i],array('dropdown','dropdown-menu','dropdown-toggle'))) {
                            $result[$tag.'_child'] = $temp[$i];
                            unset($temp[$i]);
                        }
                        elseif(in_array($temp[$i],array('active','current'))) {
                            $result[$tag.'_current'] = $temp[$i];
                            unset($temp[$i]);
                        }
                    }
                    if(is_array($temp) && count($temp)>0) {
                        $css = implode(' ',$temp);
                        $levelclasses[$depth] = $css;
                    }
                }
            }
            if(is_array($levelclasses) && count($levelclasses)>0) {
                $lastval = null;
                foreach($levelclasses as $index => $value) {
                    // look forward until end
                    if($value == $lastval) {
                        $diffs = false;
                        for($i=$index;$i<count($levelclasses);$i++) {
                            if($levelclasses[$i] != $value) {
                                $diffs = true;
                            }
                        }
                        if(!$diffs) {
                            end($result[$tag.'_level_classes']);
                            $lastindex = key($result[$tag.'_level_classes']);
                            list($level,$css) = explode(':',$result[$tag.'_level_classes'][$lastindex],2);
                            $result[$tag.'_level_classes'][$lastindex] = '>='.$level.':'.$css;
                            break;
                        }
                    }
                    $result[$tag.'_level_classes'][] = $index.':'.$value;
                    $lastval = $value;
                }
            }

            if(isset($result[$tag.'_level_classes']) && count($result[$tag.'_level_classes'])>0) {
                $result[$tag.'_level_classes'] = implode("\n",$result[$tag.'_level_classes']);
            }

            return $result;
        }   // end function analyzeTag()
        

        public static function getDepth($node,$until)
        {
            $depth = -1;
            // Increase depth until we reach the root (root has depth 0)
            while ($node != null && !in_array($node->tagName,array('html','body',$until)))
            {
                $depth++;
                // Move to parent node
                $node = $node->parentNode;
            }
            return $depth;
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function edit(int $id)
        {
            $stmt = self::db()->query(
                'SELECT `attribute`, `value` FROM `:prefix:menus` AS `t1` '
                . 'JOIN `:prefix:menu_options` AS `t2` '
                . 'ON `t1`.`menu_id`=`t2`.`menu_id` '
                . 'WHERE `t1`.`menu_id`=?',
                array($id)
            );
            $data = $stmt->fetchAll();
            $formdata = array();
            if(is_array($data) && count($data)>0) {
                foreach($data as $i => $item) {
                    $formdata[$item['attribute']] = $item['value'];
                }
            }
            if(isset($formdata['ul_level_classes'])) {
                $formdata['ul_level_classes'] = implode("\n",explode('|',$formdata['ul_level_classes']));
            }
            if(isset($formdata['li_level_classes'])) {
                $formdata['li_level_classes'] = implode("\n",explode('|',$formdata['li_level_classes']));
            }

            $form = \CAT\Helper\FormBuilder::generateForm('menu_edit',$formdata);
            // render
            self::tpl()->setPath(CAT_ENGINE_PATH.'/modules/coreMenuAdmin/templates/default','backend');
            return self::tpl()->get('edit',array('form'=>$form->render(true)));
        }   // end function edit()
        
        protected static function getID()
        {
            $menuID  = Validate::sanitizePost('menu_id','numeric');

            if(!$menuID)
                $menuID  = Validate::sanitizeGet('menu_id','numeric');

            if(!$menuID)
                $menuID = self::router()->getParam(-1);

            if(!$menuID)
                $menuID = self::router()->getRoutePart(-1);

            if(!$menuID || !is_numeric($menuID))
                $menuID = NULL;

            return intval($menuID);
        }   // end function getID()

    }
}