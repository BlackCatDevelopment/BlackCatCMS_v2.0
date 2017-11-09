/**
 * Please note: This plugin is for use with BlackCat CMS v2.x only! It will not
 * work without it!
 *
 * Version 1.0.0
 **/
;(function($) {
    $.cattranslate = function(string,attributes,module) {
        var url = CAT_URL + '/modules/lib_javascript/plugins/jquery.cattranslate/cattranslate.php';
        var translated;
        translated = '';
        $.ajax({
          type   : 'post',
          url    : url,
          data: {
            msg  : string,
            attr : attributes,
            mod  : module
          },
          cache  : false,
          async  : false,
          success: function( data ) {
            if ( typeof elem != 'undefined' && typeof elem != '' && elem != '' && elem != null ) {
              jQuery(elem).text(jQuery(data).text());
            }
            else
            {
              translated = jQuery(data).text();
            }
          }
        });
        if(translated=='') translated = string;
        return translated;
    }
})(jQuery);
