/* PLUGINS */
;(function($) {

    //Ensures there will be no 'console is undefined' errors in IE
    window.console = window.console || (function(){
        var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile = c.clear = c.exception = c.trace = c.assert = function(){};
        return c;
    })();

    $.fn.hasAttr = function(name) {
       return this.attr(name) !== undefined;
    };

    /* Custom Easing */
    $.fn.extend($.easing,{
        def:"easeInOutExpo", easeInOutExpo:function(e,f,a,h,g){if(f===0){return a;}if(f===g){return a+h;}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a;}return h/2*(-Math.pow(2,-10*--f)+2)+a;}
    });

    $.fn.Gridder = function(options) {

        /* GET DEFAULT OPTIONS OR USE THE ONE PASSED IN THE FUNCTION  */
        var opts = $.extend( {}, $.fn.Gridder.defaults, options );

        /* */
        var gridderContent;

        if(opts.debug === true) {
            console.log('options: ', opts);
        }

        var processGridder = function(parent,gridder,data) {

            /* FORMAT OUTPUT */
            var htmlcontent = opts.outputTemplate;
            var nav         = "";

            /* NAVIGATION ICONS */
            if(opts.showNav) {

                /* CHECK IF PREV AND NEXT BUTTON HAVE ITEMS */
                var prevItem = $(".selectedItem").prevAll('.gridder-expander').not('.hidden').first();
                var nextItem = $(".selectedItem").nextAll('.gridder-expander').not('.hidden').first();

                nav = opts.showNavTemplate
                    .replace(/{{closeText}}/gi,opts.closeText)
                    .replace(/{{prevText}}/gi,opts.prevText)
                    .replace(/{{nextText}}/gi,opts.nextText)
                    ;

                nav = $(nav);

                if(!prevItem.length) {
                    $(nav).children(".gridder-nav.prev").attr('disabled','disabled').addClass('disabled');
                }
                if(!nextItem.length) {
                    $(nav).children(".gridder-nav.next").attr('disabled','disabled').addClass('disabled');
                }
            }

            htmlcontent = htmlcontent
                .replace(/{{shownav}}/gi, $(nav)[0].outerHTML)
                .replace(/{{thecontent}}/gi, data);

            gridder.html(htmlcontent).removeClass('gridder-loading');

            /* NEXT BUTTON */
            $(gridder).on("click", ".gridder-nav.next", function(e) {
                e.preventDefault();
                if($(this).parents(".gridder-show").nextAll().not('selectedItem').first().hasClass('gridder-expander')) {
                    $(this).parents(".gridder-show").nextAll().not('selectedItem').first().trigger("click");
                } else {
                    $(this).parents(".gridder-show").nextAll().first().children(opts.expander).trigger("click");
                }
            });

            /* PREVIOUS BUTTON */
            $(gridder).on("click", ".gridder-nav.prev", function(e) {
                e.preventDefault();
                if($(this).parents(".gridder-show").prevAll().not('.selectedItem').first().hasClass('gridder-expander')) {
                    // works for list items
                    $(this).parents(".gridder-show").prevAll().not('.selectedItem').first().trigger("click");
                } else {
                    // works for table
                    $(this).parents(".gridder-show").prevAll().not('.selectedItem').first().children(opts.expander).trigger("click");
                }
            });

            /* CLOSE BUTTON */
            $(gridder).on("click", ".gridder-close", function(e) {
                e.preventDefault();
                $(this).parents('.gridder-show').remove();
            });

            /* SCROLL TO CORRECT POSITION AFTER */
            if (opts.scroll) {
                var offset = gridder.offset().top - opts.scrollOffset;
                $("html, body").animate({
                    scrollTop: offset
                }, {
                    duration: opts.animationSpeed,
                    easing: opts.animationEasing
                });
            }
        };

        var updateGridder = function(item,parent) {

            /* SAME ELEMENT - DO NOTHING */
            if($(parent).hasClass('selectedItem')) {
                return false;
            }

            if(opts.debug === true) {
                console.log('expanding item: ',item);
                console.log('parent: ', parent);
            }

            /* ONSTART CALLBACK */
            opts.onStart();

            if(opts.debug === true) {
                console.log('resetting...');
            }

            /* RESET */
            if($('.gridder-show').length) {
                $('.selectedItem').removeClass('selectedItem');
                $('.gridder-show').remove();
            }

            /* MARK CURRENT ITEM */
            parent.addClass("selectedItem");

            /* ADD LOADER */
            var gridder = $(opts.gridderContent).insertAfter(parent);

            /* GET CONTENT */
            $.ajax({
                url : opts.rootUrl,
                type: "POST",
                data: {media_id: item}
            }).done(function(data) {
                opts.onShow(); // callback
                processGridder(parent,gridder,data);
                opts.onFinish(); // callback
            });
        };

		/* ATTACH ONCLICK EVENT; ALSO WORKS FOR DYNAMICALLY LOADED ELEMENTS */
		$(this).on("click", opts.expander, function(e) {
            e.preventDefault();
            // find item url
            if(!$(this).hasAttr("data-gridder-url")) {
                var _this = $(this).parentsUntil("[data-gridder-url=*]").first();
            } else {
                var _this = $(this);
            }
            var thisUrl = $(_this).data('gridder-url');
            updateGridder(thisUrl,$(_this));
		});

    };

    $.fn.Gridder.defaults = {
        debug: false,
        scroll: true,
        showNav: true,
        scrollOffset: 0,
        rootUrl: "/",
        animationSpeed: 'slow',
        animationEasing: "easeInOutExpo",
        expander: '.gridder-expander',
        gridderContent: '<li class="gridder-show"></li>',
        showNavTemplate: "<div class=\"gridder-navigation\">" +
                         "<a href=\"#\" class=\"gridder-close\">{{closeText}}</a>" +
                         "<a href=\"#\" class=\"gridder-nav prev\">{{prevText}}</a>" +
                         "<a href=\"#\" class=\"gridder-nav next\">{{nextText}}</a>" +
                         "</div>",
        nextText: "Next",
        prevText: "Previous",
        closeText: "Close",
        showTemplate: "<div class=\"gridder-show loading\"></div>",
        outputTemplate: "{{shownav}}<div class=\"gridder-padding\">" +
                        "<div class=\"gridder-expanded-content\">" +
                        "{{thecontent}}" +
                        "</div></div>",
        onStart: function(){},
        onShow: function(){},
        onClose: function(){},
        onFinish: function(){}
    };

})(jQuery);
