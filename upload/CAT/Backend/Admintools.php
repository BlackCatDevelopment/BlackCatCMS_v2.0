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

if (!class_exists('CAT_Backend_Admintools'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Admintools extends CAT_Object
    {
        // array to store config options
        protected static $instance = NULL;
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        //protected static $loglevel = \Monolog\Logger::DEBUG;

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
        public static function index()
        {
            $d = CAT_Helper_Dashboard::getDashboardConfig('backend/admintools');
            // no configuration yet
            if(!isset($d['widgets']) || !is_array($d['widgets']) || !count($d['widgets']))
            {
                $tools = CAT_Helper_Addons::getAddons('tool','name',false);
                $col          = 1; // init column
                $d['columns'] = ( isset($d['columns']) ? $d['columns'] : 2 ); // init col number
                if(count($tools))
                {
                    // order tools by name
                    $tools = CAT_Helper_Array::sort($tools,'name','asc',true);
                    $count = count($tools);
                    foreach($tools as $tool)
                    {
/*
[addon_id] => 1
[type] => module
[directory] => blackcat
[name] => BlackCat CMS Admin Tool and Widget
[description] => BlackCat CMS Admin Tool and Widget - allows to check for new versions (widget demo)
[function] => tool
[version] => 0.6
[guid] => CF217773-24C7-4DAB-954F-98D9F7118F7D
[platform] => 1.0
[author] => BlackCat  Development
[license] => GNU General Public License
[installed] => 1458312789
[upgraded] => 1458312789
[removable] => Y
[bundled] => Y
*/
                        // init widget
                        $d['widgets'][] = array(
                            'column'        => $col,
                            'widget_name '  => $tool['name'],
                            'content'       => $tool['description'],
                            'link'          => '<a href="'.CAT_ADMIN_URL.'/admintools/tool/'.$tool['directory'].'">'.$tool['name'].'</a>',
                            'position'      => 1,
                            'open'          => true,
                        );
                        $col++;
                        if($col > $d['columns']) $col = 1;
                    }
                    //CAT_Helper_Dashboard::saveDashboardConfig($d,'global','admintools');
                    //$d = CAT_Helper_Dashboard::getDashboard('backend/admintools');
                }
            }
            $self = self::getInstance();
            CAT_Backend::print_header();
            $self->tpl()->output('backend_dashboard',array('id'=>0,'dashboard'=>$d));
            CAT_Backend::print_footer();

        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: tool perm
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if(!self::user()->hasPerm('tools_list'))
                self::printFatalError('You are not allowed for the requested action!');
            $tool    = self::getTool();
            $name    = CAT_Helper_Addons::getDetails($tool,'name');
            $handler = NULL;
            foreach(array_values(array(str_replace(' ','',$name),$tool)) as $classname) {
                $filename = CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$tool.'/inc/class.'.$classname.'.php');
                if(file_exists($filename)) {
                     $handler = $filename;
                }
            }

            $tpl_data = array();
            if ($handler)
            {
                self::log()->addDebug(sprintf('found class file [%s]',$handler));
                CAT_Object::addLangFile(CAT_ENGINE_PATH.'/modules/'.$tool.'/languages/');
                self::setTemplatePaths($tool);
                include_once $handler;
                // init forms
                $init = CAT_Helper_Directory::sanitizePath(
                    CAT_ENGINE_PATH.'/modules/'.$tool.'/forms.init.php'
                );
                if(file_exists($init))
                {
                    CAT_Backend::initForm();
                    require $init;
                }
                #$classname::initialize();
                $tpl_data['content'] = $classname::tool();
            }

            if(self::asJSON())
            {
                echo json_encode($tpl_data,1);
                exit;
            }

            CAT_Backend::print_header();
            self::tpl()->output('backend_admintool', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function tool()
        
        /**
         * tries to retrieve 'tool' by checking (in this order):
         *
         *    - $_POST['tool']
         *    - $_GET['tool']
         *    - Route param
         *
         * also checks for numeric value
         *
         * @access private
         * @return integer
         **/
        public static function getTool()
        {
            $tool  = CAT_Helper_Validate::sanitizePost('tool','scalar',NULL);

            if(!$tool)
                $tool  = CAT_Helper_Validate::sanitizeGet('tool','scalar',NULL);

            if(!$tool)
                $tool = self::router()->getParam(-1);

            if(!$tool || !is_scalar($tool))
                CAT_Object::printFatalError('Invalid data');

            if(!CAT_Helper_Addons::exists($tool))
                self::printFatalError('No such tool');

            return $tool;
        }   // end function getTool()

    } // class CAT_Helper_Admintools

} // if class_exists()