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
   @package         ckeditor4

*/

class dashboard_widget_versioncheck extends \CAT\Addon\Widget
{
    /**
     *
     * @access public
     * @return
     **/
    public static function view($widget_id,$dashboard_id)
    {
        $widget_data  = \CAT\Helper\Widget::getWidget($widget_id);
        $error        = false;
        $newer        = false;
        $version      = (
              isset($widget_data['data']['last_version'])
            ? $widget_data['data']['last_version']
            : ''
        );
        $last         = (
              isset($widget_data['data']['last'])
            ? $widget_data['data']['last']
            : 0
        );

        // update upon request or after 30 days
        if(\CAT\Helper\Validate::sanitizeGet('widget_versioncheck_refresh') || $last < (time()-60*60*24*30))
        {
            $release_info = \CAT\Helper\GitHub::getRelease('BlackCatDevelopment','BlackCatCMS');
            if(!$release_info || !is_array($release_info) || !count($release_info))
            {
                $error = self::lang()->translate(
                    'Unable to get the latest version from GitHub!'
                ) . "<br />Status: " . \CAT\Helper\GitHub::getError();
                $version = 'unknown';
            }
            else
            {
                $version = isset($release_info['tag_name']) ? $release_info['tag_name'] : 'unknown';
                if($version && $version != 'unknown')
                {
                    if(\CAT\Helper\Addons::getInstance()->versionCompare($version,\CAT\Registry::get('CAT_VERSION'),'>'))
                        $newer = true;
                    $last = time();
                    \CAT\Helper\Widget::saveWidgetData(
                        $widget_id,
                        $dashboard_id,
                        array('last'=>$last,'last_version'=>$version)
                    );
                }
            }
        }

        $next = $last+60*60*24*30;

        global $parser;
        $parser->setPath(dirname(__FILE__).'/../templates/default');
        return $parser->get(
            'versioncheck.tpl',
            array(
                'error'               => $error,
                'version'             => $version,
                'newer'               => $newer,
                'last'                => \CAT\Helper\DateTime::getDate($last).' '.\CAT\Helper\DateTime::getTime($last),
                'next'                => \CAT\Helper\DateTime::getDate($next),
                'CAT_VERSION'         => \CAT\Registry::get('CAT_VERSION'),
                'uri'                 => CAT_URL.'/'.self::router()->getRoute()
            )
        );
    }
}