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

    // really disable disabled links
    $('a.disabled').on('click',function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

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
            dataType: 'json',
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

    // page tree panel
	$('#slide-panel-toggle').on('click', function() {
		$('#slide-panel').toggleClass('open');
        $('#slide-panel-toggle').toggleClass('open');
        $('#slide-panel-toggle span').toggleClass('fa-angle-left');
        $('#slide-panel-toggle span').toggleClass('fa-angle-right');
		return false;
	});

    // add tooltip
    $('[data-toggle="tooltip"]').tooltip();

});
