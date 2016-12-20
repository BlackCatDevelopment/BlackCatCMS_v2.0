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

if(!class_exists('CAT_Helper_Indexer',false))
{
    class CAT_Helper_Indexer extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        private   static $instance = NULL;
        private   static $tables   = array();
        private   static $prep     = array();
        // tokens / words to ignore
        private   static $ignore   = array(
            'nbsp'
        );
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!! TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // Spaeter in die Sprachdateien verschieben
        // Evtl. konfigurierbar machen
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!! TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        private static $stopwords = array(
            'EN' => array("a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "aren't", "as", "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "can't", "cannot", "could", "couldn't", "did", "didn't", "do", "does", "doesn't", "doing", "don't", "down", "during", "each", "few", "for", "from", "further", "had", "hadn't", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "i", "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "isn't", "it", "it's", "its", "itself", "let's", "me", "more", "most", "mustn't", "my", "myself", "no", "nor", "not", "of", "off", "on", "once", "only", "or", "other", "ought", "our", "ours", "ourselves", "out", "over", "own", "same", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves", "then", "there", "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too", "under", "until", "up", "very", "was", "wasn't", "we", "we'd", "we'll", "we're", "we've", "were", "weren't", "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", "why's", "with", "won't", "would", "wouldn't", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves",),
            'DE' => array("aber", "als", "am", "an", "auch", "auf", "aus", "bei", "bin", "bis", "bist", "da", "dadurch", "daher", "darum", "das", "daß", "dass", "dein", "deine", "dem", "den", "der", "des", "dessen", "deshalb", "die", "dies", "dieser", "dieses", "doch", "dort", "du", "durch", "ein", "eine", "einem", "einen", "einer", "eines", "er", "es", "euer", "eure", "für", "hatte", "hatten", "hattest", "hattet", "hier", "hinter", "ich", "ihr", "ihre", "im", "in", "ist", "ja", "jede", "jedem", "jeden", "jeder", "jedes", "jener", "jenes", "jetzt", "kann", "kannst", "können", "könnt", "machen", "mein", "meine", "mit", "muß", "mußt", "musst", "müssen", "müßt", "nach", "nachdem", "nein", "nicht", "nun", "oder", "seid", "sein", "seine", "sich", "sie", "sind", "soll", "sollen", "sollst", "sollt", "sonst", "soweit", "sowie", "um", "und", "uns", "unser", "unsere", "unter", "vom", "von", "vor", "wann", "warum", "was", "weiter", "weitere", "wenn", "wer", "werde", "werden", "werdet", "weshalb", "wie", "wieder", "wieso", "wir", "wird", "wirst", "wo", "woher", "wohin", "zu", "zum", "zur", "über",),
            'NL' => array("aan", "af", "al", "als", "bij", "dan", "dat", "die", "dit", "een", "en", "er", "had", "heb", "hem", "het", "hij", "hoe", "hun", "ik", "in", "is", "je", "kan", "me", "men", "met", "mij", "nog", "nu", "of", "ons", "ook", "te", "tot", "uit", "van", "was", "wat", "we", "wel", "wij", "zal", "ze", "zei", "zij", "zo", "zou",),
        );

        public function __construct()
        {
            $db = CAT_Helper_DB::getInstance();
            self::$prep = array(
                'select_word' => $db->prepare("SELECT `word_id` FROM `:prefix:ri_words` WHERE `string` like :keyword LIMIT 1"),
                'add_word'    => $db->prepare("INSERT IGNORE INTO `:prefix:ri_words` (`string`) VALUES (?)"),
                'add_index'   => $db->prepare("INSERT IGNORE INTO `:prefix:ri_index` (`section_id`,`word_id`,`position`) VALUES (?,?,?)"),
                'cleanup'     => $db->prepare("DELETE `t1` FROM `:prefix:ri_words` AS `t1` LEFT JOIN `:prefix:ri_index` AS `t2` ON `t1`.`word_id`=`t2`.`word_id` WHERE `t2`.`word_id` IS NULL"),
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
         * removes words that no longer have a reference in the index
         *
         * @access public
         * @return void
         **/
        public static function cleanup()
        {
            self::$prep['cleanup']->execute();
        }   // end function cleanup()

        /**
         * retrieves the given columns of table $table and adds the words found
         * in the content(s) to the index
         *
         * @access public
         * @param  integer $section_id
         * @param  string  $table
         * @param  array   $fields
         * @return void
         **/
        public static function indextable($section_id, $table, $fields=array('*'))
        {
            if(!is_array($fields)) $fields = array($fields);

            // build and execute query
            $db   = CAT_Helper_DB::getInstance();
            $sql  = 'SELECT '.implode(',',$fields).' FROM `:prefix:'.$table.'` WHERE `section_id`=?';
            $sth  = $db->query($sql,array($section_id));
            $data = $sth->fetchAll();

            // get the language of the page
            $lang = CAT_Helper_Page::properties(
                CAT_Sections::getPageForSection($section_id),
                'language'
            );

            foreach($data as $i => $item)
            {
                foreach($item as $key => $value)
                {
                    $tokens     = self::tokenize($value);
                    $occurences = array_count_values($tokens);
                    foreach($tokens as $pos => $word)
                    {
                        if(in_array($word,self::$ignore)) continue;
                        if(in_array($word,self::$stopwords[$lang])) continue;
                        self::$prep['select_word']->bindParam(":keyword",$word);
                        self::$prep['select_word']->execute();
                        $res = self::$prep['select_word']->fetch(\PDO::FETCH_ASSOC);
                        if(!$res || !count($res) || !isset($res['word_id']))
                        {
                            self::$prep['add_word']->execute(array($word));
                            $word_id = $db->lastInsertId();
                        }
                        else
                        {
                            $word_id = $res['word_id'];
                        }
                        self::$prep['add_index']->execute(array($section_id,$word_id,$pos));
                    }
                }
            }
        }   // end function indextable()

        /**
         * break text into tokens (=words)
         *
         * @access  public
         * @param   string    $text
         * @return  array
         **/
        public static function tokenize($text)
        {
            $text = mb_strtolower($text);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_XHTML, 'utf-8');
            return self::utf8_str_word_count($text,1,'\\pL');
        }   // end function tokenize()

        /**
         * str_word_count replacement that handles UTF-8
         *
         * @access public
         * @param  string  $string
         * @param  integer $format
         * @param  string  $charlist
         * @return mixed
         **/
        public static function utf8_str_word_count($string,$format=0,$charlist='') {
            $array = preg_split("/[^'\-A-Za-z".$charlist."]+/u",$string,-1,PREG_SPLIT_NO_EMPTY);
            switch ($format) {
            case 0:
                return(count($array));
            case 1:
                return($array);
            case 2:
                $pos = 0;
                foreach ($array as $value) {
                    $pos = utf8_strpos($string,$value,$pos);
                    $posarray[$pos] = $value;
                    $pos += utf8_strlen($value);
                }
                return($posarray);
            }
        }   // end function utf8_str_word_count()

        /**
         *
         * @access public
         * @return
         **/
        public static function findPK()
        {
            if(!$pk)
            {
                $sth = CAT_Helper_DB::getInstance()->query(
                    "SHOW COLUMNS FROM `:prefix:$table` WHERE `Key`='PRI'"
                );
                $key = $sth->fetch();
                $pk  = isset($key['Field']) ? $key['Field'] : 'id';
            }

        }   // end function findPK()

        
    }
}