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
                'filepath',
                'filename',
                'filenamepath',
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
        public static function getMediaFromDir($dir,$filter=NULL)
        {
            $self     = self::getInstance();
            $data     = array();
            $suffixes = array();

            if($filter)
            {
                $suffixes = CAT_Helper_Mime::getAllowedFileSuffixes($filter);
                if(!count($suffixes))
                    return false;
            }

            $files = CAT_Helper_Directory::getInstance()
                   ->setRecursion(false)
                   ->scanDirectory(
                        $dir,
                        true,              // with files
                        true,              // files only
                        NULL,              // prefix to remove
                        $suffixes,         // allowed suffixes
                        array(),           // no dirs to skip
                        array('index.php') // skip files
                    );
            CAT_Helper_Directory::reset();

            foreach($files as $index => $filename)
            {
                $data[] = array();
                end($data);
                $index = key($data);

                $data[$index]['filename'] = CAT_Helper_Directory::getName($filename);
                $data[$index]['url']      = CAT_Helper_Validate::path2uri($filename);

                $info = $self->fileinfo()->analyze($filename);

                // base data
                foreach(array_values(self::$tag_map['basedata']) as $attr)
                {
                    $data[$index][$attr]
                        = isset($info[$attr])
                        ? $info[$attr]
                        : NULL;
                    if(!$data[$index][$attr])
                    {
                        foreach(array_values(array('video')) as $key)
                        {
                            if(isset($info[$key][$attr]))
                                $data[$index][$attr] = $info[$key][$attr];
                        }
                    }
                }
                if(isset($data[$index]['filesize']) && $data[$index]['filesize'] != 'n/a')
                {
                    $data[$index]['hfilesize'] = CAT_Helper_Directory::byte_convert($data[$index]['filesize']);
                }
                $data[$index]['moddate'] = CAT_Helper_DateTime::getDateTime(CAT_Helper_Directory::getModdate($filename));
                        
                if(isset($info['mime_type']))
                {
                    $tmp = array();
                    list($group,$type) = explode('/',$info['mime_type']);
                    switch($group)
                    {
                        case 'video':
                            $data[$index]['video']    = true;
                            break;
                        case 'image':
                            if($type == 'jpeg') $type = 'jpg';
                            $data[$index]['image']    = true;
                            $data[$index]['preview']  = CAT_Helper_Validate::path2uri($filename);
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
                                }
                            }
                        }
                        $data[$index]['exif'] = $tmp;
                    }
                    if(
                           isset($info['tags'])
                        && isset($info['tags']['iptc'])
                        && isset($info['tags']['iptc']['IPTCApplication'])
                        && isset($info['tags']['iptc']['IPTCApplication']['CopyrightNotice'])
                    ) {
                        $data[$index]['copyright']
                            = is_array($info['tags']['iptc']['IPTCApplication']['CopyrightNotice'])
                            ? $info['tags']['iptc']['IPTCApplication']['CopyrightNotice'][0]
                            : $info['tags']['iptc']['IPTCApplication']['CopyrightNotice'];
                    }

                }
            }

            return $data;
        }   // end function getMediaFromDir()
        

    }   // ----- class CAT_Helper_Media -----
}