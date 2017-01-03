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

class CAT_Addon_WYSIWYG extends CAT_Addon_Module
{

	/**
	 * @var void
	 */
    public    static $editor = 'wysiwyg';
	protected static $type   = 'wysiwyg';

    /**
     *
     * @access public
     * @return
     **/
    public static function initialize()
    {
        $config  = array('width'=>'100%','height'=>'250px');
        $details = CAT_Helper_Addons::getAddonDetails(self::$editor);
        $id      = $details['addon_id'];
    }   // end function initialize()

	/**
	 * @inheritDoc
	 */
	public static function modify($section_id)
	{
		// TODO: implement here
	}

	/**
	 * @inheritDoc
	 */
	public static function save()
	{
		// TODO: implement here
	}

    /**
	 * @inheritDoc
	 */
	public static function upgrade()
	{
		// TODO: implement here
	}
    
}
