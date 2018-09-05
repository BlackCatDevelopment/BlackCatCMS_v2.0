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
   @category        CAT_Modules
   @package         dashboard

*/

class dashboard_widget_logs extends \CAT\Addon\Widget
{
    /**
     *
     * @access public
     * @return
     **/
    public static function view($widget_id,$dashboard_id)
    {
        $temp_path   = CAT_ENGINE_PATH.'/temp';
        $widget_name = \CAT\Base::lang()->translate('Logfiles');
        $current     = strftime('%m-%d-%Y');
        $logs        = array();
        $list        = array();
        $files       = \CAT\Helper\Directory::findFiles(
                           $temp_path, array(
                               'recurse' => true,
                               'extension' => 'log',
                               'max_depth' => 2,
                           )
                       );

        if(count($files))
            foreach($files as $f)
                if(filesize($f)!==0)
                    $list[] = array('file'=>$f,'size'=>filesize($f));

        if(count($list))
        {
            foreach(array_values($list) as $f)
            {
                $file = str_ireplace(\CAT\Helper\Directory::sanitizePath($temp_path),'',\CAT\Helper\Directory::sanitizePath($f['file']));
                if(substr($file,0,1)=="/")
                    $file = substr_replace($file,'',0,1);
                if(preg_match('~'.$current.'\.log$~i',$file))
                    $removable = false;
                else
                    $removable = true;
                $logs[] = array(
                    'file'      => str_ireplace('logs/','',$file),
                    'size'      => \CAT\Helper\Directory::humanize($f['size']),
                    'removable' => $removable,
                    'date'      => \CAT\Helper\DateTime::getDateTime(filemtime($f['file'])),
                    //str_ireplace(array('log_','logs/','.txt'),'',$file)
                );
            }
        }
        else
        {
            return self::lang()->translate('No logfiles');
        }

        self::tpl()->setPath(dirname(__FILE__).'/../templates/default');
        return self::tpl()->get('logs.tpl',array('logs'=>$logs,'id'=>$widget_id));
    }   // end function view()

    /**
     *
     * @access public
     * @return
     **/
    public static function handleCall($data)
    {
        $temp_path = CAT_ENGINE_PATH.'/temp/logs';
        // ----- view -----
        if(isset($data['log']))
        {
            $file = pathinfo($data['log'],PATHINFO_FILENAME);
            $file = \CAT\Helper\Directory::sanitizePath($temp_path.'/'.$file.'.log');
            if(file_exists($file))
            {
                $lines  = file($file);
                $output = implode('<br />',$lines);
                $output = str_replace(
                    array(
                        'INFO',
                        'CAT.ERROR',
                        'CAT.EMERGENCY'
                    ),
                    array(
                        '<span style="color:#006600">INFO</span>',
                        '<span style="color:#990000">ERROR</span>',
                        '<span style="color:#990000;font-weight:900;">CRIT</span>',
                    ),
                    $output
                );
                $output = preg_replace('~\[CAT\.[^\.].+?\.DEBUG\]~i', '', $output);
                \CAT\Helper\Json::printData(array(
                    'success' => true,
                    'content' => $output
                ));
            }
            else
            {
                \CAT\Helper\Json::printError(
                      self::lang()->translate("File not found")
                    . ": ".str_ireplace( array(str_replace('\\','/',CAT_ENGINE_PATH),'\\'), array('/abs/path/to','/'), $file )
                );
            }
        }
        // ----- remove -----
        elseif(isset($data['remove']))
        {
            $file = pathinfo($data['remove'],PATHINFO_FILENAME);
            $file = \CAT\Helper\Directory::sanitizePath($temp_path.'/'.$file.'.log');
            if(file_exists($file))
            {
                unlink($file);
                \CAT\Helper\Json::printSuccess(\CAT\Base::lang()->translate('Logfile removed!'));
            }
            else
            {
                \CAT\Helper\Json::printError(
                      self::lang()->translate("File not found")
                    . ": ".str_ireplace(array(str_replace('\\','/',CAT_ENGINE_PATH),'\\'), array('/abs/path/to','/'), $file)
                );
            }
        }
        elseif(isset($data['dl']))
        {
            $file = pathinfo($data['dl'],PATHINFO_FILENAME);
            $file = \CAT\Helper\Directory::sanitizePath($temp_path.'/'.$file.'.log');
            if(file_exists($file))
            {
                try
                {
                    $zip = \CAT\Helper\Zip::getInstance(pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME).'.zip');
                    $zip->config('removePath',pathinfo($file,PATHINFO_DIRNAME))
                        ->create(array($file));
                    if(!$zip->errorCode() == 0)
                    {
                        \CAT\Helper\Json::printError(
                              self::lang()->translate("Unable to pack the file")
                            . ": ".str_ireplace(array(str_replace('\\','/',CAT_ENGINE_PATH),'\\'), array('/abs/path/to','/'), $file)
                        );
                    }
                    else
                    {
                        $filename = pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME).'.zip';
                        header("Pragma: public"); // required
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Cache-Control: private",false); // required for certain browsers
                        header("Content-Type: application/zip");
                        header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Length: ".filesize($filename));
                        readfile("$filename");
                    }
                }
                catch( Exception $e )
                {
                    \CAT\Helper\Json::printError($e->getMessage());
                }
            }
            else
            {
                \CAT\Helper\Json::printError(
                      self::lang()->translate("File not found")
                    . ": ".str_ireplace(array(str_replace('\\','/',CAT_ENGINE_PATH),'\\'), array('/abs/path/to','/'), $file)
                );
            }
        }
        exit;
    }   // end function handleCall()
    
    
}