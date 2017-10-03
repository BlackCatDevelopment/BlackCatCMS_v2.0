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

if (!class_exists('CAT_Backend_Page'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Page extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;
        protected static $debug    = false;

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
        public static function add()
        {
            // check permissions
            if(!self::user()->hasPerm('pages_add'))
                self::printFatalError('You are not allowed for the requested action!');
            // we need at least a page title
            if(!CAT_Helper_Validate::sanitizePost('page_title','string'))
                self::printFatalError('Missing page title!');

            // use query builder for easier handling
            $query  = self::db()->qb();
            $query->insert(self::db()->prefix().'pages');

            $i      = 0;
            $parent = 0;

            // expected data
            foreach(array_values(array('page_title','language','parent')) as $key)
            {
                if(($val = CAT_Helper_Validate::sanitizePost($key)) != '')
                {
                    $query->setValue($key,$query->createNamedParameter($val));
                    if($key=='parent' && $val>0)
                        $parent = $val;
                    if($key=='page_title')
                        $title  = $val;
                }
            }

            // set menu title = page title for now
            $query->setValue('menu_title',$query->createNamedParameter(CAT_Helper_Validate::sanitizePost('page_title')));
            $query->setValue('modified_when',$query->createNamedParameter(time()));
            $query->setValue('modified_by',$query->createNamedParameter(self::user()->getID()));

            if($parent>0)
            {
                // get details for parent page
                $parent_page = CAT_Helper_Page::properties($parent);

                // set root parent
                $query->setValue('root_parent',$query->createNamedParameter($parent_page['page_id']));

                // set level
                $query->setValue('level',$query->createNamedParameter($parent_page['level']+1));

                // set trail
                $trail = (substr_count($parent_page['page_trail'],',')>0 ? explode(',',$parent_page['page_trail']) : array());
                array_push($trail,$parent_page['page_id']);
                $query->setValue('page_trail',$query->createNamedParameter(implode(',',$trail)));

                // set link
                $query->setValue('link',$query->createNamedParameter($parent_page['link'].'/'.$title));
            }
            else
            {
                // set root parent
                $query->setValue('root_parent',$query->createNamedParameter(0));
                // set link
                $query->setValue('link',$query->createNamedParameter('/'.$title));
            }

            // save page
            $sth   = $query->execute();

            // get the ID of the newly created page
            $pageID = self::db()->lastInsertId();

            if(!$pageID) {
                self::printFatalError('Unable to create the page: {{error}}', self::db()->getError());
            } else {
                if(($module = CAT_Helper_Validate::sanitizePost('type','numeric')) != '0')
                {
                    // create section
                    CAT_Sections::addSection($pageID,$module);
                }
            }

            if(self::asJSON())
            {
                echo json_encode(array(
                    'success' => true,
                    'page_id' => $pageID,
                    'message' => self::lang()->t('The page was created successfully')
                ),1);
                exit;
            }
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            CAT_Backend::print_header();
            self::tpl()->output('', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function add()

        /**
         * returns the pages list, sorted by parent -> children -> position
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            // note: the access rights for index() are checked by the Backend
            // class, so there's no need to do it here
            $pages = self::list(true);
            // sort pages by children
            $tpl_data = array(
                'pages' => self::lb()->sort($pages,0),
            );
            if(self::asJSON())
            {
                echo json_encode($tpl_data,1);
                exit;
            }
            CAT_Backend::print_header();
            self::tpl()->output('backend_pages', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            $pageID  = self::getPageID();

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
                self::printFatalError('You are not allowed for the requested action!');

            // get sections; format: $sections[array_of_blocks[array_of_sections]]
            $sections = CAT_Sections::getSections($pageID,NULL,false);

            // addable addons
            $addable  = CAT_Helper_Addons::getAddons('page','name',false);

            $tpl_data = array(
                'page'    => CAT_Helper_Page::properties($pageID),
                'linked'  => CAT_Helper_Page::getLinkedByLanguage($pageID),
                'blocks'  => NULL,
                'addable' => $addable,
            );

            if(count($sections))
            {
                // for hybrid modules
                global $page_id;
                $page_id = $pageID;

                foreach($sections as $block => $items)
                {
                    foreach($items as $section)
                    {
                        $content    = null;

                        // spare some typing
                        $section_id = $section['section_id'];
                        $module     = $section['module'];
                        $class      = 'CAT_Addon_Page_'.ucfirst($module);

                        // special case
                        if($module=='wysiwyg')
                        {
                            CAT_Addon_WYSIWYG::initialize();
                            $content = CAT_Addon_WYSIWYG::modify($section_id);
                        }
                        else
                        {
                            // get the module class
                            $name    = CAT_Helper_Addons::getDetails($module,'directory');
                            $handler = NULL;
                            foreach(array_values(array(str_replace(' ','',$name),$module)) as $classname) {
                                $filename = CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$module.'/inc/class.'.$classname.'.php');
                                if(file_exists($filename)) {
                                     $handler = $filename;
                                }
                            }

                            if ($handler)
                            {
                                self::log()->addDebug(sprintf('found class file [%s]',$handler));
                                CAT_Object::addLangFile(CAT_ENGINE_PATH.'/modules/'.$module.'/languages/');
                                self::setTemplatePaths($module);
                                include_once $handler;
                                $classname::initialize($section_id);
                                $content = $classname::modify($section_id);
                            }

                        }
                        $tpl_data['blocks'][] = array_merge(
                            $section,
                            array('content' => $content)
                        );
                    }
                }
            }

            if(self::asJSON())
            {
                echo json_encode($tpl_data,1);
                exit;
            }

            CAT_Helper_Page::setTitle(sprintf(
                'BlackCat CMS Backend / %s / %s',
                self::lang()->translate('Page'),
                self::lang()->translate('Edit')
            ));
            CAT_Backend::print_header();
            self::tpl()->output('backend_page_modify', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function edit()

        /**
         * tries to retrieve 'page_id' by checking (in this order):
         *
         *    - $_POST['page_id']
         *    - $_GET['page_id']
         *    - Route param['page_id']
         *
         * also checks for numeric value
         *
         * @access private
         * @return integer
         **/
        public static function getPageID()
        {
            $pageID  = CAT_Helper_Validate::sanitizePost('page_id','numeric',NULL);

            if(!$pageID)
                $pageID  = CAT_Helper_Validate::sanitizeGet('page_id','numeric',NULL);

            if(!$pageID)
                $pageID = self::router()->getParam(-1);

            if(!$pageID || !is_numeric($pageID) || !CAT_Helper_Page::exists($pageID))
                $pageID = NULL;

            return $pageID;
        }   // end function getPageID()


        /**
         * get header files
         *
         * @access public
         * @return
         **/
        public static function headerfiles()
        {
            $pageID  = self::getPageID();

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
                CAT_Object::printFatalError('You are not allowed for the requested action!');

            $headerfiles = CAT_Helper_Page::getExtraHeaderFiles($pageID);
            $headerfiles_by_plugin = array();

            // link headerfiles to jQuery plugins
            // !!!!!!!!!!!!!!! NOT USED AT THE MOMENT !!!!!!!!!!!!!!!
            if(is_array($headerfiles) && count($headerfiles))
            {
                foreach(array_values($headerfiles) as $item)
                {
                    foreach(array_values(array('css','js')) as $key)
                    {
                        if(isset($item[$key]) && is_array($item[$key]) && count($item[$key]))
                        {
                            foreach(array_values($item[$key]) as $file)
                            {
                                $file = str_ireplace(
                                    array('/modules/lib_jquery/plugins/'),
                                    '',
                                    $file
                                );
                                $plugin = substr($file,0,strpos($file,'/'));
                                if(!isset($headerfiles_by_plugin[$plugin]))
                                    $headerfiles_by_plugin[$plugin] = array();
                                $headerfiles_by_plugin[$plugin][] = preg_replace('~^'.$plugin.'~i','',$file);
                            }
                        }
                    }
                }
            }

            // check params
            if(($remove_plugin = CAT_Helper_Validate::sanitizePost('remove_plugin')) !== NULL)
            {
                // find plugin in $headerfiles_by_plugin
                if(array_key_exists($remove_plugin,$headerfiles_by_plugin))
                {
                    foreach($headerfiles_by_plugin[$remove_plugin] as $item)
                    {
                        // find type
                        $type = pathinfo($item,PATHINFO_EXTENSION);
                        if(!in_array($type,array('css','js')))
                        {

                        }
                        self::delHeaderComponent($type,$item,$pageID);
                    }
/*
    [css] => Array
                (
                    [0] => /modules/lib_jquery/plugins/jquery.fileupload/css/style.css
                )
*/

                }
            }

            // check params
            if(($remove_file = CAT_Helper_Validate::sanitizePost('remove_file')) !== NULL)
            {
echo "remove file $remove_file\n<br />";
            }

            // available jQuery Plugins
            $jq     = self::getJQueryFiles();
            $jq_js  = self::getJQueryFiles('js');
            $jq_css = self::getJQueryFiles('css');

            array_unshift($jq,self::lang()->t('[Please select]'));
            array_unshift($jq_js,self::lang()->t('[Please select]'));
            array_unshift($jq_css,self::lang()->t('[Please select]'));

            $forms = array();

            // now, let's load the form(s)
            $form = CAT_Backend::initForm();
            $form->loadFile('pages.forms.php',__dir__.'/forms');
            foreach(array_values(array(
                'be_page_headerfiles_plugin',
                'be_page_headerfiles_js',
                'be_page_headerfiles_css'
            )) as $name) {
                $form->setForm($name);
                $form->setAttr('action',CAT_ADMIN_URL.'/page/headerfiles');
                $form->getElement('page_id')->setValue($pageID);
                if($form->hasElement('jquery_plugin'))
                    $form->getElement('jquery_plugin')->setAttr('options',$jq);
                if($form->hasElement('jquery_js'))
                    $form->getElement('jquery_js')->setAttr('options',$jq_js);
                if($form->hasElement('jquery_css'))
                    $form->getElement('jquery_css')->setAttr('options',$jq_css);

                if($form->isSent() && $form->isValid())
                {
                    $data = $form->getData();
                    foreach(array_values(array('jquery_plugin','jquery_js','jquery_css')) as $key)
                    {
                        if(isset($data[$key]))
                        {
                            $value = $data[$key];
                            $type  = preg_replace('~^jquery_~i','',$key);
                            if($type == 'plugin')
                            {
                                // find JS files
                                $js  = self::getJQueryFiles('js',$value);
                                // find CSS files
                                $css = self::getJQueryFiles('css',$value);
                                foreach($js as $file)
                                    self::addHeaderComponent('js',$value.'/'.$file,$pageID);
                                foreach($css as $file)
                                    self::addHeaderComponent('css',$value.'/'.$file,$pageID);
                            }
                        }
                    }
                }
                $forms[$name] = $form->getForm($name);
            }

            if(self::asJSON())
            {
                echo json_encode(array(
                    'byplugin' => $headerfiles_by_plugin,
                    'forms'    => $forms,
                ));
            }
        }   // end function headerfiles()

        /**
         *
         * @access public
         * @return
         **/
        public static function header()
        {
            $pageID  = CAT_Helper_Validate::sanitizePost('page_id');

            if(($plugin = CAT_Helper_Validate::sanitizePost('jquery_plugin')) !== false)
            {
                $success = true;
                // find JS files
                $js  = self::getJQueryFiles('js',$plugin);
                // find CSS files
                $css = self::getJQueryFiles('css',$plugin);
                foreach($js as $file)
                {
                    if(($result=self::addHeaderComponent('js',$plugin.'/'.$file,$pageID)) !== true)
                    {
                        echo CAT_Helper_JSON::printError($result);
                        exit;
                    }
                }
                foreach($css as $file)
                {
                    if(($result=self::addHeaderComponent('css',$plugin.'/'.$file,$pageID)) !== true)
                    {
                        CAT_Helper_JSON::printError($result);
                    }
                }
                $ajax    = array(
                    'message'    => $success ? 'ok' : 'error',
                    'success'    => $success
                );
                print json_encode( $ajax );
                exit();
            }
        }   // end function header()

        /**
         *
         *
         *
         *
         **/
        public static function list($as_array=false)
        {
            if(!self::user()->hasPerm('pages_list'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');

            $pages = CAT_Helper_Page::getPages(true);

            $lang  = self::router()->getRoutePart(-1);
            if($lang && !in_array($lang,array('page','index','list')))
            {
                $addon = CAT_Helper_Addons::getDetails($lang);
                if(!$addon || !is_array($addon) || !isset($addon['type']) || !$addon['type'] == 'language')
                {
                    self::printFatalError('Invalid data! (CAT_Backend_Page::list())');
                }
                $pages = CAT_Helper_Page::getPagesForLanguage($lang);
            }

            if(!$as_array && self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($pages,true);
                return;
            }

            return $pages;
        }   // end function list()

        /**
         *
         * @access public
         * @return
         **/
        public static function save()
        {
            $pageID  = self::getPageID();

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
                self::printFatalError('You are not allowed for the requested action!');

        }   // end function save()

        /**
         *
         * @access public
         * @return
         **/
        public static function settings()
        {
            $pageID  = self::getPageID();

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_settings') || !self::user()->hasPagePerm($pageID,'pages_settings'))
                CAT_Object::printFatalError('You are not allowed for the requested action!');

            // now, let's load the form(s)
            $form = CAT_Backend::initForm();
            $form->loadFile('pages.forms.php',__dir__.'/forms');
            $form->setForm('be_page_settings');

            $curr_tpl   = CAT_Helper_Page::getPageTemplate($pageID);
            $page       = CAT_Helper_Page::properties($pageID);
            $languages  = array();
            $templates  = array();
            $pages      = self::lb()->sort(CAT_Helper_Page::getPages(1),0);

            // to fill the several page select fields (f.e. "parent")
            $pages_select = array();
            foreach($pages as $p)
                $pages_select[$p['page_id']] = $p['menu_title'];

            // language select
            $langs        = self::getLanguages();
            if(is_array($langs) && count($langs))
            {
                foreach(array_values($langs) as $lang)
                {
                    $data        = CAT_Helper_Addons::getDetails($lang);
                    $languages[] = $data;
                }
            }

            // template select
            if(is_array(($tpls=CAT_Helper_Addons::getAddons('template'))))
            {
                foreach(array_values($tpls) as $dir => $name)
                {
                    $templates[$dir] = $name;
                }
            }

            // page parent
            $form->getElement('parent')
                 ->setAttr('options',array_merge(
                     array('0'=>'['.self::lang()->t('none').']'),
                     $pages_select
                   ))
                 ->setValue($page['parent'])
                 ;
            // template
            $form->getElement('template')
                 ->setAttr('options',array_merge(
                     array(''=>'System default'),
                     $templates
                   ))
                 ->setValue($curr_tpl)
                 ;

            // remove variant select if no variants are available
            $variants = CAT_Helper_Template::getVariants($curr_tpl);
            if(!$variants) $form->removeElement('template_variant');
            else           $form->getElement('template_variant')->setAttr('options',$variants);

            // remove menu select if there's only one menu block
            $menus    = CAT_Helper_Template::get_template_menus($curr_tpl);
            if(!$menus)    $form->removeElement('page_menu');
            else           $form->getElement('page_menu')->setAttr('options',$menus);

            // visibility
            $vis_list = CAT_Helper_Page::getVisibilities();
            $form->getElement('visibility')->setAttr('options',$vis_list)->setValue($page['vis_id']);

            // set current data
            $form->setData($page);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Die aktuellen Einstellungen als JSON zurueckliefern, nicht nur als
// fertiges HTML-Formular
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if(self::asJSON())
            {
                CAT_Helper_JSON::printSuccess($form->getForm());
            } else {
                CAT_Backend::print_header();
                self::tpl()->output('backend_page_settings', array(
                    'form' => $form->getForm(),
                    'page' => CAT_Helper_Page::properties($pageID),
                ));
                CAT_Backend::print_footer();
            }
        }   // end function settings()

        /**
         *
         * @access public
         * @return
         **/
        public static function sections()
        {
            $pageID  = self::getPageID();
            if(self::asJSON())
            {
                CAT_Helper_JSON::printSuccess($form->getForm());
            } else {
                CAT_Backend::print_header();
                self::tpl()->output('backend_page_sections', array(
                    'page'     => CAT_Helper_Page::properties($pageID),
                    'sections' => CAT_Sections::getSections($pageID,NULL,false),
                    'blocks'   => CAT_Helper_Template::getBlocks(),
                    'addable'  => CAT_Helper_Addons::getAddons('page','name',false),
                ));
                CAT_Backend::print_footer();
            }
        }   // end function sections()

        /**
         *
         * @access public
         * @return
         **/
        public static function tree()
        {
            if(!self::user()->hasPerm('pages_list'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');

            $pages = CAT_Helper_Page::getPages(true);
            $pages = self::lb()->buildRecursion($pages);

            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($pages,true);
                return;
            }

            return $pages;
        }   // end function tree()

        /**
         * remove a page relation
         *
         * note: if the relation does not exist, there will be no error!
         *
         * @access public
         * @return
         **/
        public static function unlink()
        {
            $pageID   = self::getPageID();
            $unlinkID = CAT_Helper_Validate::sanitizePost('unlink');

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
                CAT_Object::printFatalError('You are not allowed for the requested action!');

            // check data
            if(!CAT_Helper_Page::exists($pageID) || !CAT_Helper_Page::exists($unlinkID))
                CAT_Object::printFatalError('Invalid data!');

            self::db()->query(
                'DELETE FROM `:prefix:pages_langs` WHERE `page_id`=? AND `link_page_id`=?',
                array($pageID,$unlinkID)
            );

            if(self::asJSON())
            {
                echo CAT_Object::json_result(
                    ( self::db()->isError() ? false : true ),
                    ''
                );
                return;
            }

            self::edit();
        }   // end function unlink()

        /**
         *
         * @access public
         * @return
         **/
        public static function visibility()
        {
            if(!self::user()->hasPerm('pages_edit'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $params  = self::router()->getParams();
            $page_id = $params[0];
            $newval  = $params[1];
            if(!is_numeric($page_id)) {
                CAT_Helper_JSON::printError('Invalid value');
            }
            if(!in_array($newval,array('public','private','hidden','none','deleted','registered')))
            {
                CAT_Helper_JSON::printError('Invalid value');
            }
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// MUSS ANGEPASST WERDEN! Neue Spalte vis_id (FK)
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            self::db()->query(
                'UPDATE `:prefix:pages` SET `visibility`=? WHERE `page_id`=?',
                array($newval,$page_id)
            );
            echo CAT_Object::json_result(
                self::db()->isError(),
                '',
                true
            );
        }   // end function visibility()
        

        /**
         * add header file to the database; returns an array with keys
         *     'success' (boolean)
         *         and
         *     'message' (some error text or 'ok')
         *
         * @access public
         * @param  string  $type
         * @param  string  $file
         * @param  integer $page_id
         * @return array
         **/
        protected static function addHeaderComponent($type,$file,$page_id=NULL)
        {
            $headerfiles = CAT_Helper_Page::getExtraHeaderFiles($page_id);

            if(!is_array($headerfiles) || !count($headerfiles))
            {
                $headerfiles = array(array());
            }

            foreach(array_values($headerfiles) as $data)
            {
                if(isset($data[$type]) && is_array($data[$type]) && count($data[$type]) && in_array($file,$data[$type]))
                {
                    return CAT_Object::lang()->translate('The file is already listed');
                }
                else
                {
                    $paths = array(
                        CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/lib_jquery/plugins/')
                    );

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Dateien des WYSIWYG-Editors, evtl. des Templates?
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                    $db = self::db(); // spare some typing...

                    foreach($paths as $path)
                    {
                        $filename = CAT_Helper_Directory::sanitizePath($path.'/'.$file);
                        if(file_exists($filename))
                        {
                            $new    = ( isset($data[$type]) && is_array($data[$type]) && count($data[$type]) )
                                    ? $data[$type]
                                    : array();
                            array_push($new,CAT_Helper_Validate::path2uri($filename));
                            $new = array_unique($new);
                            $params = array(
                                'field'   => 'page_'.$type.'_files',
                                'value'   => serialize($new),
                                'page_id' => $page_id,
                            );

                            if(count($data))
                            {
                                $q = 'UPDATE `:prefix:pages_headers` SET :field:=:value WHERE `page_id`=:page_id';
                            }
                            else
                            {
                                $q = 'INSERT INTO `:prefix:pages_headers` ( `page_id`, :field: ) VALUES ( :page_id, :value )';
                            }
                            $db->query($q,$params);
                            if($db->isError())
                                return $db->getError();
                        }
                    }
                }
            }
            return true;
        }   // end function addHeaderComponent()

        /**
         * remove header file from the database
         **/
        protected static function delHeaderComponent($type,$file,$page_id=NULL)
        {
            $headerfiles = CAT_Helper_Page::getExtraHeaderFiles($page_id);

echo "remove file $file\n";
            if(is_array($headerfiles) && count($headerfiles))
            {
                foreach(array_values($headerfiles) as $item)
                {
print_r($item[$type]);
                    if(!(is_array($item[$type]) && count($item[$type]) && in_array($file,$item[$type])))
                        return true; // silently fail
                }
            }

/*
            if(($key = array_search($file, $data[$type])) !== false) {
                unset($data[$type][$key]);
            }
            $q = count($data)
               ? sprintf(
                     'UPDATE `:prefix:pages_headers` SET `page_%s_files`=\'%s\' WHERE `page_id`="%d"',
                     $type, serialize($data[$type]), $page_id
                 )
               : sprintf(
                     'REPLACE INTO `:prefix:pages_headers` ( `page_id`, `page_%s_files` ) VALUES ( "%d", \'%s\' )',
                     $type, $page_id, serialize($data[$type])
                 )
               ;
            self::getInstance(1)->db()->query($q);
            return array(
                'success' => ( self::getInstance(1)->isError() ? false                            : true ),
                'message' => ( self::getInstance(1)->isError() ? self::getInstance(1)->getError() : 'ok' )
            );
*/
        }   // end function delHeaderComponent()

    } // class CAT_Backend_Page

} // if class_exists()