<button role="button" class="btn btn-warning detach" id="dashboard_reset">{translate('Reset Dashboard')}</button>
<button style="display:none" class="btn btn-primary detach" id="bs_add_widget">{translate('Add widget')}</button>

<div class="dashboard">
    <ul class="columnize">
    {foreach $dashboard.widgets widget}
        <li data-row="{$widget.position}" data-col="{$widget.column}">
            <div class="panel" data-id="{$widget.widget_id}">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        {if $widget.icon}<span class="fa fa-fw {$widget.icon}"></span>{/if}
                        {translate($widget.widget_title)}
                        <span class="pull-right remove fa fa-fw fa-trash" data-id="{$widget.widget_id}"></span>
                        <span class="pull-right toggle{if $widget.open != 'Y'} panel-collapsed{/if} fa fa-fw fa-eye{if $widget.open != 'Y'}-slash{/if}"></span>
                    </div>
                    <div class="panel-body"{if $widget.open != 'Y'} style="display:none;"{/if}>
                        {$widget.content}
                    </div>
                </div>
            </div>
        </li>
    {/foreach}
    </ul>
</div>

{include(file='backend_modal.tpl' modal_id='modal_remove' modal_title='<span id="modalicon" class="fa fa-fw fa-warning"></span> Are you sure?', modal_text='Do you really want to remove this widget?' modal_savebtn='1')}
{include(file='backend_modal.tpl' modal_id='modal_dialog' modal_title='Add widget', modal_text='' modal_savebtn='1')}

<div style="display:none" id="bs_available_widgets">
    <label for="dashboard_add_widget">{translate('Widget')}</label>
    <select name="dashboard_add_widget" id="dashboard_add_widget"></select>
    <label for="dashboard_add_widget">{translate('Column')}</label>
    <select name="dashboard_widget_lane">
        <option value="" selected="selected">{translate('Use widget setting')}</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </select>
</div>


{literal}
<script type="text/javascript">
    $(function(){ //DOM Ready

        // avoid modal contents to be sent more than once
        $('body').on('hidden.bs.modal', '.modal', function() {
            $(this).removeData('bs.modal');
        });

        // dashboard lanes
        $('ul.columnize').columnize(
            {/literal}{$dashboard.columns}{literal},
            {
                ul_classes:'bs_sortable'
            }
        );

        // addable widgets
        $.ajax({
            type    : 'POST',
            url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/widgets/{/literal}{$dashboard.id}{literal}',
            data    : {},
            dataType: 'json',
            success : function(data, status) {
                // activate for debugging:
                // console.log('data',data);
                // if there are widgets that are not yet an the dashboard...
                if(data.data && data.data.length)
                {
                    var select = $('div#bs_available_widgets select#dashboard_add_widget');
                    for(var index in data.data) {
                        var obj = data.data[index];
                        var option = new Option(obj.widget_name,obj.widget_id);
                        select.append($(option));
                    }
                    $('button#bs_add_widget').show();
                }
            }
        });
        // delegate the click event to the detached button
        $('body').delegate('#bs_add_widget','click',function(e) {
            e.preventDefault();
            $('#modal_dialog .modal-body').html( $('div#bs_available_widgets').html() );
            $('#modal_dialog').modal('show');
            $('button.btn-primary').unbind('click').on('click',function(e) {
                var id = $('#modal_dialog select#dashboard_add_widget option:selected').val();
                $('#modal_dialog').modal('hide');
                $.ajax({
                    type    : 'POST',
                    url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/add/{/literal}{$dashboard.id}{literal}',
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

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Hier fehlt noch die Dashboard-ID!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // drag & drop
        $(".bs_sortable").sortable({
            connectWith: ".bs_sortable",
            placeholder: "bs_placeholder",
            forcePlaceholderSize: true,
            forceHelperSize: true,
            over:function(event,ui){
                $('.bs_placeholder').parent().addClass('bs_highlight');
            },
            out:function(event,ui){
                $('.bs_placeholder').parent().removeClass('bs_highlight');
            },
            update:function(event,ui){
                // make sure this only fires once
                if (this === ui.item.parent()[0]) {
                    $(this).removeClass('bs_highlight');
                    $(this).find('.panel').effect("highlight","slow");
                    $.ajax({
                        type    : 'POST',
                        url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/order',
                        data    : {
                            col      : $(this).data('col'),
                            row      : ui.item.index()+1,
                            id       : ui.item.find('.panel').data('id'),
                            dashboard: {/literal}'{$dashboard.id}'{literal}
                        },
                        dataType: 'json'
                    });
                }
            }
        }).disableSelection();

        // toggle panel
        $(document).on('click', '.panel-heading span.toggle', function(e){
            var $this = $(this);
        	if(!$this.hasClass('panel-collapsed')) {
        		$this.parents('.panel').find('.panel-body').slideUp();
        		$this.addClass('panel-collapsed');
        		$this.removeClass('fa-eye').addClass('fa-eye-slash');
                $.ajax({
                    type    : 'POST',
                    url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/toggle',
                    data    : {
                        id : $(this).parent().parent().parent().data('id'),
                        vis: 'N',
                        dashboard: {/literal}'{$dashboard.id}'{literal}
                    },
                    dataType: 'json'
                });
        	} else {
        		$this.parents('.panel').find('.panel-body').slideDown();
        		$this.removeClass('panel-collapsed');
        		$this.removeClass('fa-eye-slash').addClass('fa-eye');
                $.ajax({
                    type    : 'POST',
                    url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/toggle',
                    data    : {
                        id : $(this).parent().parent().parent().data('id'),
                        vis: 'Y',
                        dashboard: {/literal}'{$dashboard.id}'{literal}
                    },
                    dataType: 'json'
                });
        	}
        });

        // remove panel
        $(document).on('click', '.panel-heading span.remove', function(e){
            var $this = $(this);
            $('#modal_remove').modal('show');
            $('#modal_remove button.btn-primary').unbind('click').on('click',function(e) {
                $('#modal_remove').modal('hide');
                var id = $this.data('id');
                $.ajax({
                    type    : 'POST',
                    url     : '{/literal}{$CAT_ADMIN_URL}{literal}/dashboard/remove/{/literal}{$dashboard.id}{literal}',
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
    });
</script>{/literal}