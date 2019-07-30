$(function() {
    var dashboard_id = $('div.dashboard').data('id'),
        columns      = $('div.dashboard').data('columns');

    // addable widgets
    $.ajax({
        type    : 'POST',
        url     : CAT_ADMIN_URL + '/dashboard/widgets/' + dashboard_id,
        data    : {},
        dataType: 'json',
        success : function(data, status) {
            // activate for debugging:
            // console.log('data',data);
            // if there are widgets that are not yet an the dashboard...
            if(data.data && data.data.length)
            {
                var select = $('div#bs_available_widgets select#dashboard_add_widget');
                var index  = 0;
                for(index in data.data) {
                    var obj = data.data[index];
                    var option = new Option(obj.widget_name,obj.widget_id);
                    select.append($(option));
                }
                $('button#bsAddWidget').show();
                $('p#bsAddWidgetInfo').show();
            }
            else
            {
                $('p#bsNoWidgets').show();
            }
        }
    });


// jQuery 1.4.3+
//$( elements ).delegate( selector, events, data, handler );
// jQuery 1.7+
//$( elements ).on( events, selector, data, handler );

    // delegate the click event to the detached button
    $('body').on('click','#bsAddWidget',function(e) {
        e.preventDefault();
        $('#modal_dialog .modal-body').html( $('div#bs_available_widgets').html() );
        $('#modal_dialog').modal('show');
        $('button.btn-primary').unbind('click').on('click',function(e) {
            var id = $('#modal_dialog select#dashboard_add_widget option:selected').val();
            $('#modal_dialog').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/dashboard/add/'+dashboard_id,
                data    : {
                    widget_id: id
                },
                dataType: 'json',
                success: function(data, status) {
                    // activate for debugging:
                    // console.log(data);
                    if(data.success) {
                        location.reload();
                    } else {
                        $('div.infopanel span#message').html(data.message);
                        $('div.infopanel').addClass('alert alert-danger').show();
                    }
                }
            });
            e.preventDefault();
        });
    });

    // toggle panel
    $(document).on('click', '.card-header span.toggle', function(e){
        var $this = $(this);
        if(!$this.hasClass('card-collapsed')) {
            $this.parents('.card').find('.card-body').slideUp();
            $this.addClass('card-collapsed');
            $this.removeClass('fa-eye').addClass('fa-eye-slash');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/dashboard/toggle',
                data    : {
                    id : $(this).parent().parent().parent().data('id'),
                    vis: 'N',
                    dashboard: dashboard_id
                },
                dataType: 'json'
            });
        } else {
            $this.parents('.card').find('.card-body').slideDown();
            $this.removeClass('card-collapsed');
            $this.removeClass('fa-eye-slash').addClass('fa-eye');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/dashboard/toggle',
                data    : {
                    id : $(this).parent().parent().parent().data('id'),
                    vis: 'Y',
                    dashboard: dashboard_id
                },
                dataType: 'json'
            });
        }
    });

    // remove panel
    $(document).on('click', '.card-header span.remove', function(e){
        var $this = $(this);
        $('#modal_remove').modal('show');
        $('#modal_remove button.btn-primary').unbind('click').on('click',function(e) {
            $('#modal_remove').modal('hide');
            var id = $this.data('id');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/dashboard/remove/'+ dashboard_id,
                data    : {
                    widget_id: id
                },
                dataType: 'json',
                success: function(data, status) {
                    // activate for debugging:
                    //console.log(data);
                    if(data.success) {
                        $this.parent().parent().parent().parent().remove();
                    } else {
                        $('div.infopanel span#message').html(data.message);
                        $('div.infopanel').addClass('alert alert-danger').show();
                    }
                }
            });
        });
    });

    // reset dashboard
    $('#dashboard_reset').unbind('click').on('click',function(e) {
        e.preventDefault();
        var $this = $(this);
        $('#modal_dialog .modal-body').html($.cattranslate('Do you really want to reset the Dashboard? All your customization settings will be lost!'));
        $('#modal_dialog .modal-title').text($.cattranslate('Reset Dashboard'));
        $('#modal_dialog').modal('show');
        $('button.btn-primary').unbind('click').on('click',function(e) {
            $('#modal_dialog').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/dashboard/reset/'+ dashboard_id,
                dataType: 'json',
                success: function(data, status) {
                    // activate for debugging:
                    if(data.success) {
                        location.reload();
                    } else {
                        $('div.infopanel span#message').html(data.message);
                        $('div.infopanel').addClass('alert alert-danger').show();
                    }
                }
            });
        });
    });
});