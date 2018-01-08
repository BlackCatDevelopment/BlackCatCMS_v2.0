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

class dashboard_widget_forennews extends \CAT\Addon\Widget
{
    /**
     *
     * @access public
     * @return
     **/
    public static function view($widget_id,$dashboard_id)
    {
        global $widget_dashboard_forennews_url, $widget_dashboard_forennews_maxitems;
        $widget_dashboard_forennews_url = 'https://forum.blackcat-cms.org/feed.php?f=2';
        $widget_dashboard_forennews_maxitems = 5;

        $ch       = \CAT\Helper\GitHub::init_curl($widget_dashboard_forennews_url);
        $data     = curl_exec($ch);
        $tpl_data = array();

        if($data && strlen($data))
        {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($data);

            $items = $dom->getElementsByTagName('entry');
            $cnt   = 0;

            foreach($items as $item)
            {
                if($item->childNodes->length)
                {
                    if(substr(str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),0,3) == 'Re:') continue;
                    $pub_date = new DateTime($item->getElementsByTagName('published')->item(0)->textContent);
                    $content  = trim(substr(strip_tags($item->getElementsByTagName('content')->item(0)->textContent),0,81));

                    $tpl_data[] = array(
                        'published' => $pub_date->format('Y-m-d H:i:s'),
                        'link'      => $item->getElementsByTagName('link')->item(0)->getAttribute('href'),
                        'title'     => str_replace('News • ','',$item->getElementsByTagName('title')->item(0)->textContent),
                        'content'   => $content.((strlen($content)>=80) ? '...' : ''),
                    );
                    $cnt++;
                    if($cnt == $widget_dashboard_forennews_maxitems) break;
                }
            }
        }
        self::tpl()->setPath(dirname(__FILE__).'/../templates/default');
        return self::tpl()->get(
            'forennews.tpl',
            array('news'=>$tpl_data)
        );
    }   // end function view()
}