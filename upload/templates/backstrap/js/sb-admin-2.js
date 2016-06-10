$(function() {
    $('#side-menu').metisMenu();
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function() {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});

$(function() {
    // add X-Editable to elements with 'editable' class
    $('.editable').editable();

    // handle AJAX links
    $('.ajax').on('click', function(event) {
        if($(this).is('form')) {
            var $form = $(this)
                $url  = $form.attr('action'),
                $type = $form.attr('method'),
                $data = $form.serialize()
                ;
        } else {
            var $url  = $(this).data('url'),
                $type = 'POST',
                $data =  $(this).data()
                ;
        }
        $.ajax({
            type   : $type,
            url    : $url,
            data   : $data,
            success: function(data, status) {
                // activate for debugging:
                //console.log(data);
                if(data.success) {
                    location.reload();
                } else {
                    $('div.infopanel span#message').html(data.message);
                    $('div.infopanel').addClass('alert alert-danger').show();
                }
            }
        });
        event.preventDefault();
    });

	$('#slide-panel-toggle').on('click', function() {
		$('#slide-panel').toggleClass('open');
        $('#slide-panel-toggle').toggleClass('open');
        $('#slide-panel-toggle span').toggleClass('fa-angle-left');
        $('#slide-panel-toggle span').toggleClass('fa-angle-right');
		return false;
	});
});
