<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 **/

if(!class_exists('CAT_Helper_QRCode',false))
{
    class CAT_Helper_QRCode extends CAT_Object
    {
        // log level
        private   static $loglevel   = \Monolog\Logger::EMERGENCY;

        /**
         *
         * @access public
         * @return
         **/
        public static function getImage($data,$base64=true)
        {
            $outputOptions = new \chillerlan\QRCode\Output\QRImageOptions;
            $outputOptions->base64 = $base64;
            $qrcode = new \chillerlan\QRCode\QRCode($data, new \chillerlan\QRCode\Output\QRImage($outputOptions));
            return $qrcode->output();
        }   // end function getImage()

    }
}

if(!class_exists('CAT_Helper_QRCodeProvider',false))
{
    class CAT_Helper_QRCodeProvider implements \RobThree\Auth\Providers\Qr\IQRCodeProvider
    {
        public function getMimeType() {
            return 'image/png';     // This provider only returns PNG's
        }

        public function getQRCodeImage($data, $size) {
            $outputOptions = new \chillerlan\QRCode\Output\QRImageOptions;
            $outputOptions->base64 = false;
            $qrcode = new \chillerlan\QRCode\QRCode($data, new \chillerlan\QRCode\Output\QRImage($outputOptions));
            return $qrcode->output();
        }
    }
}