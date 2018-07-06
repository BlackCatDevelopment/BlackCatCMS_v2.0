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

namespace CAT\Addon;

use \CAT\Base as Base;
use \CAT\Registry as Registry;

if(!class_exists('\CAT\Addon\WYSIWYG',false))
{
    class WYSIWYG extends Page implements iAddon, iPage
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
        public static function remove() {}

        /**
         * @inheritdoc
         **/
        public static function view($section)
        {
            parent::view($section);

            // get the contents, ordered by 'order' column; returns an array
            $contents = self::getContent($section['section_id']);

            // render template
            $output  = self::tpl()->get(
                'view.tpl',
                array(
                    'section_id' => $section['section_id'],
                    'columns'    => $contents,
                    'options'    => $section['options'],
                )
            );

            return $output;
        }   // end function view()

        /**
         *
         **/
        public static function save(int $section_id)
        {
            // ex: ./backend/section/save/48?variant=pricing2&section_id=48&currency=EUR
        }

        public static function getJS()
        {
            if(!is_object(self::$e)) self::initialize();
            return self::$e->getJS();
        }

        /**
         *
         * @access  public
         * @param   integer  $section_id
         * @return  string
         **/
		public static function modify(array $section)
        {
            parent::modify($section); // sets template path(s)

            // get the contents, ordered by 'order' column; returns an array
            $contents = self::getContent($section['section_id']);

/*
Array
(
    [0] => <h2>Enjoy the difference!</h2>

<p>BlackCat CMS bietet das perfekte System für fast jeden Einsatzbereich. Modern, intuitiv, leicht erweiterbar und dabei kinderleicht zu installieren. Überzeugen Sie sich selbst von den Vorteilen und lassen Sie sich begeistern!</p>

    [1] => <p>Lorem ipsum dolor sit amet consectetuer sapien laoreet elit ipsum porttitor. Odio sed Curabitur semper odio tincidunt felis ut lobortis Morbi eu. Pellentesque sit mollis justo sem Vestibulum rutrum pellentesque Ut ut id. Et tincidunt adipiscing netus nunc augue lorem tempus interdum mollis orci. Consequat tellus condimentum eu pede ut.</p>
<p>Id eget laoreet sed augue natoque sollicitudin lobortis ut Lorem Integer. Et vel eget a Quisque platea ac malesuada lobortis et tristique. Nulla at libero laoreet congue leo nisl vitae quis iaculis justo. Ut auctor quis augue tincidunt enim quis In interdum dui mus. Pellentesque pellentesque leo et at Phasellus diam morbi semper rhoncus tempus. </p>
<p>Semper felis risus semper urna justo nunc laoreet malesuada convallis leo. Orci ut Praesent Nullam Vestibulum laoreet Aenean laoreet pede In et. Malesuada consectetuer Phasellus Curabitur Vivamus velit et sit nunc elit et. Metus Nam ipsum vitae pellentesque id wisi vel sem sed sem. Consectetuer sed adipiscing Quisque massa id Phasellus tempus commodo et dui. Convallis parturient Maecenas condimentum eros nulla.</p>
<p>Ante auctor nunc lacinia libero nulla velit ipsum vitae sollicitudin elit. Elit vestibulum sapien leo felis congue Aenean Lorem auctor nibh Donec. Rutrum wisi rutrum enim ut id tortor eros gravida consequat dolor. Felis lacus elit Pellentesque tortor congue ut metus enim nibh amet. Non pellentesque ante semper Vivamus ipsum Vestibulum leo Vestibulum metus orci. Orci tempor mi sodales.</p>

)
*/

            $am = \CAT\Helper\AssetFactory::getInstance('backend_page');
            $am->addJS(
                self::$e->getJS(),
                'footer'
            );

            // editor has some init code
            $editor_js = self::$e->getEditorJS();
            if(!empty($editor_js))
            {
                $am->addCode(
                    self::tpl()->get(
                        new \Dwoo\Template\Str(),
                        array(
                            'section_id' => $section['section_id'],
                            'action'     => CAT_ADMIN_URL.'/section/save/'.$section['section_id'],
                            'width'      => self::$e->getWidth(),
                            'height'     => self::$e->getHeight(),
                            'id'         => $id,
                            'content'    => $content
                        )
                    ),
                    'footer'
                );
            }

            // render template
            $output  = self::tpl()->get(
                'modify.tpl',
                array(
                    'section_id' => $section['section_id'],
                    'action'     => CAT_ADMIN_URL.'/section/save/'.$section['section_id'],
                    'width'      => self::$e->getWidth(),
                    'height'     => self::$e->getHeight(),
                    'columns'    => $contents,
                    'options'    => $section['options'],
                    'editor'     => self::tpl()->get(
                        new \Dwoo\Template\Str(self::$e->showEditor()),
                        array(
                            'section_id' => $section['section_id'],
                            'action'     => CAT_ADMIN_URL.'/section/save/'.$section['section_id'],
                            'width'      => self::$e->getWidth(),
                            'height'     => self::$e->getHeight(),
                            'id'         => $id,
                            'content'    => $content
                        )
                    ),
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

        /**
         *
         * @access protected
         * @return
         **/
        protected static function getContent(int $section_id, bool $escape=false)
        {
            $content = array();
            // get content
            $result  = self::db()->query(
                "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=:section_id ORDER BY `order`",
                array('section_id'=>$section_id)
            );
            $data    = $result->fetchAll();
            if($data && is_array($data) && count($data)>0)
            {
                foreach($data as $i => $c)
                {
                    $content[$i] = (
                          $escape
                        ? htmlspecialchars($c['content'])
                        : $c['content']
                    );
                }
                return $content;
            }
        }   // end function getContent()
        
    }
}