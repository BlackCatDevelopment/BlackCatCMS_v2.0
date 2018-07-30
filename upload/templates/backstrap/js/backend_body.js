(function($) {
    jQuery.ajaxSetup({
        error: function( x, e )
        {
            //console.log('x: ',x);
            //console.log('e: ',e);
            if( x.status == 0 )           { bsErrorMsg('You are offline!\nPlease Check Your Network.');     }
            else if( x.status == 404 )    { bsErrorMsg('404 - Requested URL not found.');                   }
            else if( x.status == 500 )    { bsErrorMsg('500 - Internal Server Error.');                     }
            // dismiss parse errors
            else if( e == 'parsererror' ) { console.log('Parse error. Maybe caused by invalid JSON data.'); }
            else if( e == 'timeout' )     { bsErrorMsg('Request timed out.');                               }
            else                          { bsErrorMsg('Unknown Error.\n'+x.responseText);                  }
         }
    });

    /* page tree filter */
    $("input#bsPageSearch").keyup(function() {
        var find = $(this).val().toLowerCase();
        if(find.length) {
            $('div#sidebar li span.pagename').each(function() {
                var text = $(this).text().toLowerCase();
                if(text.indexOf(find) != -1) {
                    $(this).removeClass('text-muted').addClass('text-danger');
                } else {
                    $(this).addClass('text-muted').removeClass('text-danger');
                }
            });
        } else {
            $('div#sidebar li span.pagename').removeClass('text-muted').removeClass('text-danger');
        }
    });

    /* show javascript errors as modal */
    function bsErrorMsg(message)
    {
        $('#bsDialog .modal-title').html('<div class="text-danger"><i class="fa fa-fw fa-warning"></i> '+$.cattranslate('Sorry, there was an error')+'</div>');
        $('#bsDialog .modal-body').html('<div class="text-danger">'+$.cattranslate(message)+'</div>');
        $('#bsDialog').modal('show');
    }

    /* handle session timeout and re-login */
    function CATSessionTimedOut()
    {
        $('#bsSessionTimedOutDialog').modal('show');
        $('#bsSessionToFE').unbind('click').on('click',function(e) {
            e.preventDefault();
            window.location.replace(CAT_URL); // also removes history
        });
        $('button#bsSessionLogin').unbind('click').on('click',function(e) {
            $('div#login-error').text('').hide(); // make sure there is no old error
            var ufield = $('input.form-control.u').prop('id');
            var pfield = $('input.form-control.p').prop('id');
            var dates  = {
                'username_fieldname': $('input.form-control.u').prop('id'),
                'password_fieldname': $('input.form-control.p').prop('id'),
            };
            dates[ufield] = $('input.form-control.u').val();
            dates[pfield] = $('input.form-control.p').val();
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL+'/authenticate',
                dataType: 'json',
                data    : dates,
                success : function(data, status) {
                    if(data.success === false)
                    {
                        $('div#login-error').text(data.message).show();
                    }
                    else
                    {
                        // reset form
                        $('input.form-control.u').val('');
                        $('input.form-control.p').val('');
                        $('#bsSessionTimedOutDialog').modal('hide');
                        // reset session timer
                        CATSessionSetTimer(sess_time,CATSessionTimedOut,'span#sesstime','sesstimealert');
                    }
                }
            });
            e.preventDefault();
        });
    }

    // really disable disabled links
    $('a.disabled').on('click',function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    // allow to add a new page everywhere
    $("a.bsAddPage, button.bsAddPage").unbind("click").on("click",function(e) {
        e.preventDefault();
        $("#add_page_modal .modal-title").text($.cattranslate("Add page"));
        // remove buttons from form
        $("#add_page_modal .form-group.row.buttonline").remove();
        // select parent
        parent = $(e.target).parent().data("parent");
        if(typeof parent == "undefined") { parent = 0; }
        $("#add_page_modal select[name=page_parent]").val(parent).change();
        $("#add_page_modal").modal("show");
        var _this = $(this);
        $("#add_page_modal .modal-content button.btn-primary").unbind("click").on("click",function(e) {
            e.preventDefault();
            $("#add_page_modal").modal("hide");
            $.ajax({
                type    : "POST",
                url     : CAT_ADMIN_URL+"/page/add/",
                dataType: "json",
                data    : $("#add_page_modal form").serialize(),
                success : function(data, status) {
                    BCGrowl(data.message,data.success);
                    window.location.href = CAT_ADMIN_URL + "/page/edit/" + data.page_id
                }
            });
        });
    });

    // handle nested tabs
    $("ul.nav-tabs.inner a").click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // convert fieldsets to tabs
    $('form.tabbed').fieldset_to_tabs();

    // tooltips
    if(typeof tippy != 'undefined') {
        tippy(document.querySelectorAll('*:not([title=""])'),{arrow:true,theme:'light'});
    } else {
        alert('no tippy');
    }
/**
    // page tree hover tooltips
    $('.hasTooltip').each(function() { // Notice the .each() loop, discussed below
        $(this).qtip({
            content: {
                text: $(this).next('div').html() // Use the "div" element next to this for the content
            },
            style: { classes: 'qtip-bootstrap' }
        });
    });
**/
    // detach = move to region header
    $('.detach').each( function() {
        $(this).detach()
               .addClass("float-right")
               .appendTo('.breadcrumb');
    });

    // format buttons
    $('input[type="submit"]').addClass('btn btn-primary');
    $('input[type="reset"]').addClass('btn btn-default');
    $('input[type="button"]').addClass('btn btn-default');

    // avoid modal contents to be sent more than once
    $('body').on('hidden.bs.modal', '.modal', function() {
        $(this).removeData('bs.modal');
    });

    // close any modals before opening a new one
    $('body').on('show.bs.modal', ".modal", function(e) {
        if($('.modal:visible').length) {
            $('.modal').modal('hide');
        }
    });

    // trigger primary button on enter
    $("body").on("shown.bs.modal", ".modal", function() {
        $(this).keypress(function(e) {
            if (e.which == "13") {
                $("div.modal-footer > button.btn-primary").trigger('click');
            }
        });
    });

    // populate AdminTools list
    $.ajax({
        type    : "GET",
        url     : CAT_ADMIN_URL+"/admintools/list",
        dataType: "json",
        success : function(data, status) {
            if(data) {
                submenu = $("<ul></ul>");
                submenu.addClass("dropdown-menu");
                for(index=0;index<=data.length;index++) {
                    submenu.append('<li><a class="dropdown-item" href="//localhost:444/site1/backend/addons">Erweiterungen</a></li>');
                }
                //console.log(submenu);
            }
        }
    });

    // style file upload form fields
    //$(":file").filestyle({buttonName:"btn-primary"});

    // add session timer
    var sess_time = CATTimeStringToSecs($('div#sessiontimer span#sesstime').text());
    CATSessionSetTimer(sess_time,CATSessionTimedOut,'span#sesstime','sesstimealert');

})(jQuery);