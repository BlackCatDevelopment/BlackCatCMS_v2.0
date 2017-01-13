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

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Sections', false ) ) {

	class CAT_Sections extends CAT_Object
	{
        /**
         * log level
         **/
        public    static $loglevel   = \Monolog\Logger::EMERGENCY;
        /**
         * list of all sections
         **/
        private   static $sections   = array();
        /**
         * list of active sections
         **/
        private   static $active     = array();
        /**
         * instance
         **/
        private   static $instance   = NULL;

	    /**
         * constructor
	     *
         * @access private
         * @return void
         **/
        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * allow to use methods in OO context
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
        public static function addSection($page_id,$module,$add_to_block)
        {
            $self = self::getInstance();
            //`position`=:pos,
        	$self->db()->query(
                'INSERT INTO `:prefix:sections` SET `page_id`=:id, `module`=:module, `block`=:block',
                array('id'=>$page_id, 'module'=>$module, 'block'=>$add_to_block)
            );
        	if ( !$self->db()->isError() )
        		return $self->db()->lastInsertId(); // Get the section id
            else
                return false;
        }   // end function addSection()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function updateSection($section_id, $options)
        {
            $sql    = 'UPDATE `:prefix:sections` SET ';
            $params = array('id'=>$section_id);
            foreach($options as $key => $value)
            {
                $sql .= $key.' = :'.$key.', ';
                $params[$key] = $value;
            }
            $sql  = preg_replace('~,\s*$~','',$sql);
            $sql .= ' WHERE section_id = :id LIMIT 1';

		    self::getInstance()->db()->query(
                $sql,
                $params
            );

            return self::getInstance()->db()->is_error()
                ? false
                : true;
        }   // end function updateSection()

        /**
         *
         * @access public
         * @return
         **/
        public static function deleteSection($section_id)
        {
            // delete section
            if(self::exists($section_id))
            {
                if(self::getSetting('trash_enabled')===true)
                {
                    self::db()->query(
                        'UPDATE `:prefix:sections` SET `state_id`=? WHERE `section_id`=?',
                        array(self::getStateID('deleted'), $section_id)
                    );
                } else {
                	self::db()->query(
                        'DELETE FROM `:prefix:sections` WHERE `section_id`=:id',
                        array('id'=>$section_id)
                    );
                }
            	return ( self::db()->isError() ? false : true );
                // note: we do not clean the position (order) here as it's no
                // problem to have a gap
            } else {
                return false;
            }
        }   // end function deleteSection()

        /**
         * check if a section exists
         *
         * @access public
         * @return
         **/
        public static function exists($section_id)
        {
            $data = self::getSection($section_id);
            if(!$data || !is_array($data) || !count($data)) return false;
            return true;
        }   // end function exists()
	    
	    /**
         *
         * @access public
         * @return
         **/
        public static function getSection($section_id)
        {
            $self = self::getInstance();
        	$q = $self->db()->query(
                'SELECT * FROM `:prefix:sections` WHERE `section_id` = :id',
                array('id'=>$section_id)
            );
        	if($q->rowCount() == 0)
                return false;
        	return $q->fetch();
        }   // end function getSection()

        /**
         * This method retrieves _all_ sections and saves them into a class
         * variable.
         *
         * If a $block_id is passed, the $page_id is mandatory. The method
         * will return only sections for that block on that page.
         *
         * $active_only defaults to true; it enables the checks for
         * publ_start and publ_end and "visible" column in pages_sections table.
         *
         * @access public
         * @param  integer  $page_id     Sections for that page only
         * @param  integer  $block_id    Sections for that block only
         * @param  boolean  $active_only Only active sections
         * @return array
         **/
        public static function getSections($page_id=NULL,$block_id=NULL,$active_only=true)
        {
            // cache data
	        if (!self::$sections)
	        {
                $q = 'SELECT '
                   . '    `t1`.`section_id`, `t1`.`module`, `t1`.`modified_when`, `t1`.`modified_by`, '
                   . '    `t2`.`page_id`, `t2`.`position`, `t2`.`block`, '
                   . '    `t2`.`publ_start`, `t2`.`publ_end`, '
                   . '    `t2`.`publ_by_time_start`, `t2`.`publ_by_time_end`, '
                   . '    `t2`.`name`, `t4`.`state_id` '
                   . 'FROM `:prefix:sections` AS `t1` '
                   . 'JOIN `:prefix:pages_sections` AS `t2` '
                   . 'ON `t1`.`section_id`=`t2`.`section_id` '
                   . 'JOIN `:prefix:pages` AS `t3` '
                   . 'ON `t2`.`page_id`=`t3`.`page_id` '
                   . 'JOIN `:prefix:item_states` AS `t4` '
                   . 'ON `t1`.`state_id`=`t4`.`state_id` '
                   . 'WHERE `t1`.`site_id`=? '
                   . 'ORDER BY `block`, `t2`.`position`';

                $sec = self::db()->query($q,array(CAT_SITE_ID));
	            if($sec->rowCount() == 0) return NULL;

                $data = $sec->fetchAll();
                foreach($data as $i => $section)
                {
                    self::$sections[$section['page_id']][$section['block']][] = $section;
                }
            }

            // only active sections
            if($active_only)
            {
                if(!self::$active) // cache
                {
                    foreach(self::$sections as $pageID => $items)
                    {
                        self::$active[$pageID] = array();
                        foreach($items as $blockID => $sections)
                        {
                            foreach($sections as $section)
                            {
                                // skip this section if it is out of publication-date
                                if($section['publ_start']!='' || $section['publ_end']!='')
                                {
                	                $now = time();
                	                if (!(
                                           ($now <= $section['publ_end'] || $section['publ_end'] == 0)
                                        && ($now >= $section['publ_start'] || $section['publ_start'] == 0)
                                    )) {
                                        self::log()->addDebug(sprintf(
                                            'skipping section [%d] by publication date',
                                            $section['section_id']
                                        ));
                	                    continue;
                                    }
                	                self::$active[$section['page_id']][$section['block']][] = $section;
                                }
                                if($section['publ_by_time_start']!='' || $section['publ_by_time_end']!='')
                                {
                                    $now   = new DateTime();
                                    $start = new DateTime();
                                    $end   = new DateTime();

                                    $start->setTime(date("H",$section['publ_by_time_start']),date("i",$section['publ_by_time_start']));
                                    $end->setTime(date("H",$section['publ_by_time_end']),date("i",$section['publ_by_time_end']));

                                    if($now >= $start && $now <= $end)
                                    {
                                        self::$active[$section['page_id']][$section['block']][] = $section;
                                    }
                                }
                            }
                        }
                    }
                }
                if($page_id)
                    if(isset(self::$active[$page_id]))
                        return self::$active[$page_id];
                    else
                        return NULL;
            }

            if($page_id)
                if(isset(self::$sections[$page_id]))
                    return self::$sections[$page_id];
                else
                    return NULL;

            return self::$sections;
        }   // end function getSections()
        
	    /**
         * gets all sections of given type; if a page_id is given, for that
         * page only, all sections of this type otherwise
          *
          * @access public
         * @param  integer  $page_id (default NULL = all)
         * @param  string   $type    (default 'wysiwyg')
         * @param  integer  $limit   (default 1)
         * @param  boolean  $all     (default false) skip sections out of pub time
         * @return array
          **/
        public static function getSectionsByType($page_id=NULL,$type='wysiwyg',$limit=1,$all=false)
        {
            $limit  = ( isset($limit) && $limit && is_int($limit) )
                    ? $limit
                    : 1;
            $pub_sql = NULL;
            $result  = NULL;
            $params  = array();
            if(!$all)
            {
                $now     = time();
                $pub_sql = '(( :time1 BETWEEN `publ_start` AND `publ_end`) OR '
                         . '( :time2 > `publ_start` AND `publ_end`=0)) '
                         ;
                $params  = array('time1'=>$now,'time2'=>$now);
            }
            $self   = self::getInstance();
            $SQL    = "SELECT `section_id`, `page_id` FROM `:prefix:sections` "
                    . "WHERE "
                    . ( $page_id ? "`page_id` = :page_id  AND " : '' )
                    . "`module` = :module AND `section_id`>0 "
                    . ( $pub_sql ? ' AND '.$pub_sql : '' )
                    . "ORDER BY `position` ASC LIMIT " . $limit;
            $params['module'] = $type;
            if($page_id) $params['page_id'] = $page_id;
            $result = $self->db()->query($SQL,$params);
            return $result->rowCount()
                ?  $result->fetchAll()
                :  false;
         }   // end function getSectionsByType()

        /**
         * gets the first section for given $page_id that has a module of type $type
         *
         * @access public
         * @param  integer  $page_id
         * @param  string   $type
         * @return mixed    result array containing the section_id on success,
         *                  false otherwise (no such section)
         **/
        public static function getSectionForPage($page_id,$type=NULL)
        {
            $opt = array('page_id'=>$page_id, 'module'=>$type);
            $sql = 'SELECT `section_id` FROM `:prefix:sections` WHERE `page_id`=:page_id AND `module`=:module';
            $sec = self::getInstance()->db()->query($sql,$opt);
            if($sec->rowCount())
                return $sec->fetch();
            return false;
        }   // end function getSectionForPage()

        /**
         * gets the page_id for a given section
         *
         * @access public
         * @param  integer $section_id
         * @return integer
         **/
        public static function getPageForSection($section_id)
        {
            $sec = self::getInstance()->db()->query(
                'SELECT `page_id` FROM `:prefix:sections` WHERE `section_id`=:id',
                array('id'=>$section_id)
            );
            if($sec->rowCount())
            {
                $result = $sec->fetch();
                return $result['page_id'];
            }
        }   // end function getPageForSection()

	    /**
	     * checks if a page has active sections
	     *
	     * @access public
	     * @param  integer $page_id
	     * @return boolean
	     *
	     **/
	    public static function hasActiveSections($page_id)
	        {
	        if (!isset(self::$active[$page_id]) )
	            self::getActiveSections($page_id);
	        return ( count(self::$active[$page_id]) ? true : false );
	    }   // end function hasActiveSections()

        /**
         *
         * @access public
         * @return
         **/
        public static function recoverSection($section_id)
        {
            if(self::exists($section_id))
            {
                self::db()->query(
                    'UPDATE `:prefix:sections` SET `state_id`=? WHERE `section_id`=?',
                    array(self::getStateID('default'), $section_id)
                );
                return ( self::db()->isError() ? false : true );
            } else {
                self::log()->addError('attempt to recover non-existing section');
                return false; // silently fail
            }
        }   // end function recoverSection()
        
        /**
         * checks if given section is active
         *
         * @access public
         * @param  int    $section_id
         * @return boolean
         **/
        public static function section_is_active($section_id)
        {
            global $database;
            $now = time();
            $sql = 'SELECT COUNT(*) FROM `:prefix:sections` ';
            $sql .= 'WHERE (' . $now . ' BETWEEN `publ_start` AND `publ_end`) OR ';
            $sql .= '(' . $now . ' > `publ_start` AND `publ_end`=0) ';
            $sql .= 'AND `section_id`=' . $section_id;
            return($database->query($sql)->fetchColumn() != false);
	    }

        /**
         * checks if given section has given type
         *
         * @access public
         * @param  integer  $section_id
         * @param  string   $type (module)
         * @return boolean
         **/
        public static function hasType($section_id,$type)
        {
            $opt = array('id'=>$section_id, 'mod'=>$type );
            $sql = 'SELECT * FROM `:prefix:sections` WHERE `section_id`=:id AND `module`=:mod';
            $sec = self::getInstance()->db()->query($sql,$opt);
            if($sec->rowCount())
                return true;
            return false;
        }   // end function hasType()

        /**
         * checks if given page is of type menu_link
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isMenuLink($page_id)
        {
            if(!self::$instance)
                self::getInstance();
            $res = self::$instance->db()->query(
                  'SELECT `module` FROM `:prefix:sections` '
                . 'WHERE `page_id` = :id AND `module` = "menu_link"',
                array('id'=>$page_id)
            );
            if($res && $res->rowCount())
                return true;
            return false;
        }   // end function isMenuLink()

	}
}

?>