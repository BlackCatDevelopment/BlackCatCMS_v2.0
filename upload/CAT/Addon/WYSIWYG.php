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

namespace CAT\Addon;

use \CAT\Base as Base;
use \CAT\Registry as Registry;

if(!class_exists('\CAT\Addon\WYSIWYG',false))
{
    class WYSIWYG extends Module implements iAddon, iPage
    {
        private static $e_url  = null;
        private static $e_name = null;
        private static $e      = null;

        public static function initialize()
        {
            self::$e_name = Registry::get('wysiwyg_editor');     // name
            self::$e_url  = Registry::get('wysiwyg_editor_url'); // url
            self::$e      = new \CAT\Addon\WYSIWYG\CKEditor4();
        }

        public static function add() {}
        public static function getTemplate()
        {
            return "<form name=\"wysiwyg{\$section_id}\" action=\"{\$action}\" method=\"post\">
    <input type=\"hidden\" name=\"section_id\" value=\"{\$section_id}\" />
    <input type=\"hidden\" name=\"content_id\" value=\"{\$id}\" />
    <textarea class=\"wysiwyg\" id=\"{\$id}\" name=\"{\$id}\" style=\"width:{\$width};height:{\$height}\">{\$content}</textarea><br />
	<input type=\"submit\" value=\"{translate('Save')}\" />
</form>";
        }

        public static function remove() {}
        public static function view($section_id) {}
        public static function save($section_id) {}

        public static function getJS()
        {
            if(!is_object(self::$e)) self::initialize();
            return self::$e->getJS();
        }

		public static function modify($section_id)
        {
            // get content
            $result = self::db()->query(
                "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=:section_id",
                array('section_id'=>$section_id)
            );
            $data    = $result->fetch();
            $content = htmlspecialchars($data['content']);

            $tpl     = self::getTemplate();

            // identifier
            $id      = self::$e_name.'_'.$section_id;

            $am = \CAT\Helper\AssetFactory::getInstance('backend_page');
            $am->addCode(
                self::tpl()->get(
                    new \Dwoo\Template\Str(self::$e->getEditorJS()),
                    array(
                        'section_id' => $section_id,
                        'action'     => CAT_ADMIN_URL.'/section/save/'.$section_id,
                        'width'      => self::$e->getWidth(),
                        'height'     => self::$e->getHeight(),
                        'id'         => $id,
                        'content'    => $content
                    )
                ),
                'footer'
            );

            // render template
            $output  = self::tpl()->get(
                new \Dwoo\Template\Str($tpl),
                array(
                    'section_id' => $section_id,
                    'action'     => CAT_ADMIN_URL.'/section/save/'.$section_id,
                    'width'      => self::$e->getWidth(),
                    'height'     => self::$e->getHeight(),
                    'id'         => $id,
                    'content'    => $content
                )
            );
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Ich verstehe nicht warum der CKE nicht erscheint wenn ich nicht hier ein
// echo einbaue... :(
#echo "\n";
#echo "<div style=\"display:none;\"></div>";
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            return $output;
        }
    }
}