$(function() {
    // reset infopanel
    function infoPanelReset() {
        $('div.infopanel span#message').html('');
        $('div.infopanel').hide();
    }

    var dtable = $('table.dtable').DataTable({
        mark: true,
        stateSave: true,
        orderClasses: false,
        language: {
            url: CAT_ASSET_URL
        }
    });
    // delete a group member
    $(document).on('click', 'a.deleteuser', function(event) {
        event.preventDefault();
        infoPanelReset();
        var id = $(this).data('id');
        $('#bsmodal_groupLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> {translate("Remove group member")}: '+$(this).data('username'));
        $('#modal_group').modal('show');
        $('div.modal-body').html('{translate("Do you really want to remove this group member?")}');
        $('#modal_users').modal('show');
        var _this = $(this);
        $('button.btn-primary').on('click',function(e) {
            $('#modal_users').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : _this.data('url')+"/"+id,
                dataType: 'json',
                success : function(data, status) {
                    _this.parent().parent().remove();
                    $('div.infopanel span#message').html("{translate('Group member successfully removed')}");
                    $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                }
            });
        });
    });
    // delete a group
    $('a.delete').on('click', function(event) {
        event.preventDefault();
        // reset infopanel
        infoPanelReset();
        var id = $(this).data('group-id');
        $('#bsmodal_groupLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> {translate("Delete group")}: '+$(this).data('name'));
        $('div.modal-body').html("{translate('Do you really want to delete this group?')}");
        $('#modal_group').modal('show');
    });
    // add a group member
    $('a.add_member').on('click', function(event) {
        infoPanelReset();
        var id = $(this).data('group-id');
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/users/notingroup/' + id,
            dataType: 'json',
            success : function(data, status) {
                // activate for debugging:
                //console.log(data);
                // add select box to modal
                if(data.length)
                {
                    if($('div.modal-body').find('select').length) {
                        $('div.modal-body select').remove();
                    }
                    $('div.modal-body').append('<form><select id="users" multiple="multiple">');
                    var select = $('select#users');
                    for(i=0;i<data.length;i++)
                    {
                        select.append('<option value="'+data[i].user_id+'">'+data[i].username+'</option>');
                    }
                    $('#modal_users').modal('show');
                    var _this = $(this);
                    $('button.btn-primary').on('click',function(e) {
                        $('#modal_users').modal('hide');
                        if($('select#users :selected').length)
                        {
                            $.ajax({
                                type    : 'POST',
                                url     : CAT_ADMIN_URL + '/groups/addmember/',
                                dataType: 'json',
                                data    : $(_this).find('form').serialize(),
                                success : function(data, status) {
                                    _this.parent().parent().remove();
                                    $('div.infopanel span#message').html(cattranslate('Group member successfully removed'));
                                    $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                                }
                            });
                        }
                    });
                }
                else {
                    $('div.infopanel span#message').html(
                        cattranslate('No addable users found') + '<br />' +
                        cattranslate('Please note') + ': ' +
                        cattranslate('Users of group "Administrators" and users that are already member of this group cannot be added.')
                    );
                    $('div.infopanel').removeClass('alert-danger').addClass('alert-warning').show();
                }
            }
        });
    });
    // show group members
    $('a.members').on('click', function(event) {
        var id = $(this).data('group-id');
        if($('.bs_group_users').length) {
            // remove other
            $('tr').removeClass('highlight');
            $('.bs_group_users').hide('slow').remove();
        }
        var _this = $(this);
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/users/bygroup/' + id,
            dataType: 'json',
            success : function(data, status) {
                // activate for debugging:
                //console.log(data);
                if(data.length) {
                    var table = $('div#bs_userlist table').clone();
                    var tbody = $(table).find('tbody');
                    var line  = $(tbody).find('tr').remove();
                    for(i=0;i<data.length;i++)
                    {
                        // activate for debugging:
                        //console.log('adding data line',data[i]);
                        var curr_line = $(line).clone();
                        $(curr_line).find('td[data-col="2"]').html(data[i].id);
                        $(curr_line).find('td[data-col="3"]').html(data[i].username);
                        $(curr_line).find('td[data-col="4"]').html(data[i].display_name);
                        $(curr_line).find('td[data-col="5"]').html(data[i].email);
                        if(data[i].protected == 'Y') {
                            $(curr_line).find('td[data-col="1"]').html('<span class="fa fa-fw fa-anchor" data-title="{translate("Built-in objects must not be removed")}"></span>');
                        }
                        else {
                            $(curr_line).find('td[data-col="1"] > a').attr('data-id',data[i].id);
                            $(curr_line).find('td[data-col="1"] > a').attr('data-username',data[i].username);
                        }
                        $(curr_line).appendTo($(tbody));
                    }
                    var parent = $(_this).parent().parent(),
                        td     = $('<td>').prop('colspan',6).html(table.show());
                        row    = $('<tr>').addClass('bs_group_users highlight').html(td);
                    row.insertAfter(parent).show('slow');
                    parent.addClass('highlight');
                    $('[data-title!=""]').qtip({
                        content: { attr: 'data-title' },
                        style: { classes: 'qtip-bootstrap' }
                    });
                }
            }
        });
        event.preventDefault();
    });
});