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

if(!class_exists('CAT_Addon_WYSIWYG',false))
{
    class CAT_Addon_WYSIWYG extends CAT_Addon_Module
    {

    	/**
    	 * @var void
    	 */
        public    static $editor = 'wysiwyg';
    	protected static $type   = 'wysiwyg';
        protected static $config = NULL;

        public static function initialize()
        {
            $e_name  = CAT_Registry::get('WYSIWYG_EDITOR');
            // get editor
            require_once CAT_ENGINE_PATH.'/modules/'.$e_name.'/inc/class.WYSIWYG.php';
            WYSIWYG::initialize();
        }


    	/**
    	 * @inheritDoc
    	 */
        public static function modify($section_id)
        {
            $e_name  = CAT_Registry::get('WYSIWYG_EDITOR');

            // get content
            $result = self::db()->query(
                "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=:section_id",
                array('section_id'=>$section_id)
            );
            $data    = $result->fetch();
            $content = htmlspecialchars($data['content']);

            // set template path
            self::tpl()->setPath(CAT_ENGINE_PATH.'/modules/'.$e_name.'/templates/default');

            // identifier
            $id      = $e_name.'_'.$section_id;
            $editor  = WYSIWYG::getInstance();
            $output  = self::tpl()->get(
                'modify',
                array(
                    'section_id' => $section_id,
                    'action'     => CAT_ADMIN_URL.'/section/save/'.$section_id,
                    'width'      => $editor->getWidth(),
                    'height'     => $editor->getHeight(),
                    'id'         => $id,
                    'content'    => $content
                )
            );

            $output .= $editor->loadJS($id);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Ich verstehe nicht warum der CKE nicht erscheint wenn ich nicht hier ein
// echo einbaue... :(
echo "<div style=\"display:none;\"></div>";
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            return $output;
        }   // end function modify()

    	/**
    	 * @inheritDoc
    	 */
    	public static function save($section_id)
    	{
            $field   = CAT_Helper_Validate::sanitizePost('content_id');
    		$content = CAT_Helper_Validate::sanitizePost($field);
            $olddata = self::view($section_id);

            if(!self::user()->is_root() && CAT_Helper_Addons::isModuleInstalled('lib_htmlpurifier'))
            {
                // check if if HTMLPurifier is enabled...
                $r = self::db()->get_one(
                    'SELECT * FROM `:prefix:mod_wysiwyg_settings` WHERE `option`="enable_htmlpurifier" AND `value`="true"'
                );
                if($r)
                {
                    require_once CAT_ENGINE_PATH.'/modules/lib_htmlpurifier/inc/class.Purifier.php';
                    $content = Purifier::purify($content,array('Core.CollectErrors'=>true));
                }
            }

            // check for changes
            if(sha1($content)!==sha1($olddata['content']))
            {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: per Checkbox steuern (wie beim Wiki)
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                self::db()->query(
                    'INSERT INTO `:prefix:mod_wysiwyg_revisions` VALUES (?,?,?,?,?);',
                    array($section_id,date('Y-m-d-H-i-s'),time(),$olddata['content'],$olddata['text'])
                );
                $query  = "REPLACE INTO `:prefix:mod_wysiwyg` VALUES (?,?,?);";
                self::db()->query($query,array($section_id, $content, strip_tags($content)));
                $result = self::db()->isError() ? false : true;
            }
            else
            {
                $result = true;
            }
            return $result;
    	}   // end function save()

        /**
    	 * @inheritDoc
    	 */
    	public static function upgrade()
    	{
    		// TODO: implement here
    	}

        /**
         *
         * @access public
         * @return
         **/
        public static function view($section_id)
        {
            $result = self::db()
                    ->query(
                        "SELECT `content`, `text` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=?",
                        array($section_id)
                      );
            if($result)
            {
                $fetch = $result->fetch(\PDO::FETCH_ASSOC);
                return $fetch;
            }
        }   // end function view()
        

    }
}

if(!class_exists('wysiwyg_editor_base',false))
{
    abstract class wysiwyg_editor_base
    {
        private static $instances       = array();

        protected      $default_skin    = NULL;
        protected      $default_toolbar = NULL;
        protected      $default_height  = '250px';
        protected      $default_width   = '100%';
        protected      $config          = array();

        // derived classes must provide these functions
        abstract public function getFilemanagerPath();
        abstract public function getSkinPath();
        abstract public function getPluginsPath();
        abstract public function getToolbars();
        abstract public function getAdditionalSettings();
        abstract public function getAdditionalPlugins();
        abstract public function getFrontendCSS();
        abstract public function loadConfig();

        /**
         * get value from $config array
         **/
        protected function get($name)
        {
            if(isset($this->config[$name]))
            {
                return $this->config[$name];
            }
            return NULL;
        }   // end function get()

        public static function getInstance()
        {
            $editor = CAT_Registry::get('WYSIWYG_EDITOR');
            if(!isset(self::$instances[$editor]))
            {
                $class  = get_called_class();
                $i      = new $class();
                $config = array('width'=>$i->default_width,'height'=>$i->default_height);
                $id     = CAT_Helper_Addons::getDetails($editor,'addon_id');
                $result = CAT_Object::db()->query(
                    "SELECT * from `:prefix:mod_wysiwyg_settings` where `editor_id`=:name",
                    array('name'=>$id)
                );
                if($result->rowCount())
                {
                    $rows = $result->fetchAll();
                    foreach(array_values($rows) as $row)
                    {
                        $i->config[$row['option']] = $row['value'];
                    }
                }
                $i->loadConfig();
                self::$instances[$editor] = $i;
                CAT_Helper_Page::addInc(CAT_ENGINE_PATH.'/modules/'.$editor.'/inc/headers.inc.php');
            }
            return self::$instances[$editor];
        }   // end function getInstance()

        /**
         * get available filemanager plugins; requires an info.php file in
         * the filemanager path
         **/
        public function getFilemanager()
        {
            $fm_path = $this->getFilemanagerPath();
            $d       = CAT_Helper_Directory::getInstance(1);
            $fm      = $d->maxRecursionDepth(1)->findFiles('info.php',$fm_path,$fm_path.'/');
            $r       = array();
            $d->maxRecursionDepth(); // reset
            if(is_array($fm) && count($fm))
            {
                foreach($fm as $file)
                {
                    $filemanager_name = $filemanager_dirname = $filemanager_version = $filemanager_sourceurl = $filemanager_registerfiles = $filemanager_include = NULL;
                    @include $fm_path.$file;
                    $r[$filemanager_dirname] = array(
                        'name'    => $filemanager_name,
                        'version' => $filemanager_version,
                        'url'     => $filemanager_sourceurl,
                        'inc'     => $filemanager_include,
                        'dir'     => $filemanager_dirname,
                    );
                }
            }
            return $r;
        }   // end function getFilemanager()

        /**
         * get the editor height
         **/
        public function getHeight()
        {
            $val = $this->get('height');
            return ( $val != '' ) ? $val : $this->default_height;
        }   // end function getHeight()

        /**
         * get the editor width
         **/
        public function getWidth()
        {
            $val = $this->get('width');
            return ( $val != '' ) ? $val : $this->default_width;
        }   // end function getWidth()

        /**
         * get the editor skin
         **/
        public function getSkin()
        {
            $val = $this->get('skin');
            return ( $val != '' ) ? $val : $this->default_skin;
        }   // end function getSkin()

        /**
         * get the toolbar
         **/
        public function getToolbar()
        {
            $val = $this->get('toolbar');
            return ( $val != '' ) ? $val : $this->default_toolbar;
        }   // end function getToolbar()

        /**
         * get available skins
         **/
        public function getSkins($skin_path)
        {
            $d = CAT_Helper_Directory::getInstance();
            $d->setRecursion(false);
            $skins = $d->getDirectories($skin_path,$skin_path.'/');
            $d->setRecursion(true);
            return $skins;
        }   // end function getSkins()

        /**
         * load javascript
         **/
        public function loadJS($id)
        {
            return CAT_Object::tpl()->get(
                'wysiwyg',
                array_merge($this->config,
                array(
                    'CAT_URL' => CAT_URL,
                    'id'      => $id,
                    'width'   => $this->getWidth(),
                    'height'  => $this->getHeight(),
                    'toolbar' => $this->getToolbar(),
                ))
            );
        }

    }
}