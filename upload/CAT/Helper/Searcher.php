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

if(!class_exists('CAT_Helper_Searcher',false))
{
    class CAT_Helper_Searcher extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        private   static $instance = NULL;
        private   static $prep     = array();

        public function __construct()
        {
            self::$prep = array(
                'find_like' => $this->db()->prepare(
'SELECT count(`t3`.`section_id`) as `occurences`, `t3`.`section_id`, `t3`.`modified_when`, `t3`.`page_id`, `t4`.`menu_title`, `t5`.`display_name`
FROM `:prefix:ri_words` AS `t1`
JOIN `:prefix:ri_index` AS `t2`
ON `t1`.`word_id`=`t2`.`word_id`
JOIN `:prefix:sections` AS `t3`
ON `t2`.`section_id`=`t3`.`section_id`
JOIN `:prefix:pages` AS `t4`
ON `t3`.`page_id`=`t4`.`page_id`
LEFT OUTER JOIN `:prefix:users` AS `t5`
ON `t3`.`modified_by`=`t5`.`user_id`
WHERE `t1`.`string` LIKE ?
GROUP BY `t2`.`section_id`'),
            );
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!self::$instance) self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function like($word)
        {
            try {
                self::$prep['find_like']->execute(array($word));
            } catch ( Exception $e ) {
            }
            $items = self::$prep['find_like']->fetchAll(\PDO::FETCH_ASSOC);
            if(is_array($items) && count($items))
            {
                // sort items by occurences
                usort($items,array('self','rank_by_occurence'));
                return $items;
            }
            return NULL;
        }   // end function like()

        /**
         *
         * @access public
         * @return
         **/
        public static function rank_by_occurence($a,$b)
        {
            if ($a == $b) { return 0; }
            return ($a < $b) ? 1 : -1;
        }   // end function rank_by_occurence()
    }
}