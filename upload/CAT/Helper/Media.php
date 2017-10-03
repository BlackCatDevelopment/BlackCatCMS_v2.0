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

if (!class_exists('CAT_Helper_Media'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Media extends CAT_Object
    {
        private static $instance;
        private static $tag_map = array(
            'basedata' => array(
                'mime_type',
                'filesize',
                'bits_per_sample',
                'resolution_x',
                'resolution_y',
                'encoding',
                'error',
                'warning',
            ),
            'EXIF' => array(
                'ExposureTime',
                'ISOSpeedRatings',
                'ShutterSpeedValue',
                'FocalLength',
                'ExifImageWidth',
                'ExifImageLength',
                'DateTimeOriginal',
            ),
            'IFD0' => array(
                'Make',
                'Model',
                'Orientation',
                'XResolution',
                'YResolution',
            ),
            'FILE' => array(
                'FileDateTime',
            ),
        );

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function getAttributes($id)
        {
            $attr = array();
            $sth = self::db()->query(
                  'SELECT * FROM `:prefix:media` AS `t1` '
                . 'JOIN `:prefix:media_data` AS `t2` '
                . 'ON `t1`.`media_id`=`t2`.`media_id` '
                . ' WHERE `t1`.`media_id`=?',
                array($id)
            );
            $data = $sth->fetchAll();

            if(is_array($data) && count($data))
            {
                foreach($data as $item)
                {
                    $attr[$item['attribute']] = $item['value'];
                }
                $attr['hfilesize'] = CAT_Helper_Directory::humanize($attr['filesize']);
                $attr['path']      = $data[0]['path'];
                $attr['filename']  = $data[0]['filename'];
                $attr['is_image']  = (substr($attr['mime_type'],0,6) == 'image/')
                                   ? true
                                   : false;
                $attr['url']       = CAT_Helper_Validate::path2uri($attr['path'].'/'.$attr['filename']);
            }
            return $attr;
        }   // end function getAttributes()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getMediaFromDir($dir,$filter=NULL)
        {
            $data     = array();
            $suffixes = array();

            if($filter)
            {
                $suffixes = CAT_Helper_Mime::getAllowedFileSuffixes($filter);
                if(!count($suffixes))
                    return false;
            }

            $files = CAT_Helper_Directory::findFiles($dir,array('extension'=>$suffixes));

            // load file data from database
            $sth = self::db()->query(
                  'SELECT * FROM `:prefix:media` AS `t1` '
                . 'WHERE `path`=?',
                array(CAT_Helper_Directory::getName(CAT_Helper_Directory::sanitizePath($dir)))
            );
            $dbdata  = $sth->fetchAll();
            $dbfiles = array();
            if(is_array($dbdata) && count($dbdata))
            {
                foreach($dbdata as $index => $item)
                {
                    $dbfiles[$item['filename']] = $item;
                }
            }

            foreach($files as $index => $filename)
            {
                $data[] = array();
                end($data);
                $index = key($data);

                // convert UTF8
                $decoded_filename = CAT_Helper_Directory::getName(pathinfo($filename,PATHINFO_BASENAME));

                // add info to db
                if(!is_dir($filename) && !isset($dbfiles[$decoded_filename]))
                {
                    self::db()->query(
                          'INSERT INTO `:prefix:media` ( `site_id`, `path`, `filename`, `checksum` ) '
                        . 'VALUES (?, ?, ?, ? )',
                        array(1, CAT_Helper_Directory::getName(CAT_Helper_Directory::sanitizePath($dir)), $decoded_filename, sha1_file($filename))
                    );
                    $data[$index]['media_id'] = self::db()->lastInsertId();
                    $data[$index]['url']      = CAT_Helper_Validate::path2uri($filename);
                    $data[$index] = self::analyzeFile(self::db()->lastInsertId(),$filename);
                } else {
                    $data[$index] = self::getAttributes($dbfiles[$decoded_filename]['media_id']);
                    $data[$index]['media_id'] = $dbfiles[$decoded_filename]['media_id'];
                }

                $data[$index]['filename'] = $decoded_filename;
            }

            return $data;
        }   // end function getMediaFromDir()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function analyzeFile($id,$filename)
        {
            $self = self::getInstance();

            $info = $self->fileinfo()->analyze($filename);
            $data = array();

            // base data
            foreach(array_values(self::$tag_map['basedata']) as $attr)
            {
                $data[$attr]
                    = isset($info[$attr]) ? $info[$attr] : NULL;

                if(!$data[$attr])
                {
                    foreach(array_values(array('video')) as $key)
                    {
                        if(isset($info[$key][$attr]))
                            $data[$attr] = $info[$key][$attr];
                    }
                }

                if($data[$attr])
                {
                    self::db()->query(
                          'INSERT INTO `:prefix:media_data` ( `media_id`, `attribute`, `value` ) '
                        . 'VALUES(?, ?, ?)',
                        array($id, $attr, $data[$attr])
                    );
                }
            }

            // file size
            if(isset($data['filesize']) && $data['filesize'] != 'n/a')
            {
                $data['hfilesize'] = CAT_Helper_Directory::humanize($data['filesize']);
                self::db()->query(
                      'INSERT INTO `:prefix:media_data` ( `media_id`, `attribute`, `value` ) '
                    . 'VALUES(?, ?, ?)',
                    array($id, 'filesize', $data['filesize'])
                );
            }

            // modification time
            $data['moddate'] = CAT_Helper_DateTime::getDateTime(CAT_Helper_Directory::getModdate($filename));
            self::db()->query(
                  'INSERT INTO `:prefix:media_data` ( `media_id`, `attribute`, `value` ) '
                . 'VALUES(?, ?, ?)',
                array($id, 'moddate', $data['moddate'])
            );

            if(isset($info['mime_type']))
            {
                $tmp = array();
                list($group,$type) = explode('/',$info['mime_type']);
                switch($group)
                {
                    case 'video':
                        $data['video']    = true;
                        break;
                    case 'image':
                        if($type == 'jpeg') $type = 'jpg';
                        $data['image']    = true;
                        $data['url']      = CAT_Helper_Validate::path2uri($filename);
                        break;
                }

                if(isset($info[$type]) && isset($info[$type]['exif']))
                {
                    foreach(self::$tag_map as $key => $attrs)
                    {
                        if(isset($info[$type]['exif'][$key]))
                        {
                            $arr = $info[$type]['exif'][$key];
                            foreach($attrs as $attr)
                            {
                                $tmp[$attr] = ( isset($arr[$attr]) ? $arr[$attr] : '?' );
                                self::db()->query(
                                      'INSERT INTO `:prefix:media_data` ( `media_id`, `attribute`, `value` ) '
                                    . 'VALUES(?, ?, ?)',
                                    array($id, $attr, $tmp[$attr])
                                );
                            }
                        }
                    }
                    $data['exif'] = $tmp;
                }

                if(
                       isset($info['tags'])
                    && isset($info['tags']['iptc'])
                    && isset($info['tags']['iptc']['IPTCApplication'])
                    && isset($info['tags']['iptc']['IPTCApplication']['CopyrightNotice'])
                ) {
                    $data['copyright']
                        = is_array($info['tags']['iptc']['IPTCApplication']['CopyrightNotice'])
                        ? $info['tags']['iptc']['IPTCApplication']['CopyrightNotice'][0]
                        : $info['tags']['iptc']['IPTCApplication']['CopyrightNotice'];
                    self::db()->query(
                          'INSERT INTO `:prefix:media_data` ( `media_id`, `attribute`, `value` ) '
                        . 'VALUES(?, ?, ?)',
                        array($id, 'copyright', $data['copyright'])
                    );
                }
            }

            return $data;
        }   // end function analyzeFile()
        
        

    }   // ----- class CAT_Helper_Media -----
}