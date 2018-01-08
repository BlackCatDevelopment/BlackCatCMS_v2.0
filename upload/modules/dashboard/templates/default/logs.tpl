<div id="bclogs">
    {if $logs}
    <table>
    {foreach $logs item}
    <tr style="border-bottom:1px solid #ccc">
        <td><a style="margin-right:20px" class="bcshowlog" href="#" data-file="{$item.file}">{$item.file}</a></td>
        <td style="font-size:x-small">{$item.size}</td>
        <td style="white-space:nowrap">
            <a href="#" class="fa fa-fw fa-download" data-file="{$item.file}"></a>
            {if $item.removable === true}<a href="#" class="fa fa-fw fa-trash" data-file="{$item.file}"></a>{/if}
        </td>
    </tr>
    {/foreach}
    </table>
    {/if}
    <div id="bclogdialog" title="{translate('Log')}:" style="display:none;font-size:.8em;"></div>
    <div id="bclogapprove" title="{translate('Are you sure?')}" style="display:none"></div>
</div>

<script charset=windows-1250 type="text/javascript">
    (function($) {
        var toTrash = function() {
            $.ajax({
				type:	 'GET',
				url:	 '{$CAT_ADMIN_URL}/dashboard',
				data:	 {
                    widget: {$id},
                    remove: _target
                },
				cache:	 false,
                success: function(data,textStatus,jqXHR)
				{
                    BCGrowl(data.message,data.success);
                    $.ajax({
        				type:	 'POST',
                        dataType: 'json',
        				url:	 '{$CAT_ADMIN_URL}/dashboard/reload',
        				data:	 {
                            widget_id: {$id}
                        },
        				cache:	 false,
                        success: function(data,textStatus,jqXHR)
        				{
                            $('div[data-id="3"] .card-body').html(data.message);
                        }
                    });
                }
            });
        };

        var bcDialogConfig = {
            modal: true,
            autoOpen: false,
            width: "90%",
            maxWidth: "90%",
            height: 600,
            buttons:
            [
                { text: $.cattranslate('Close'), click: function() { $( this ).dialog( "close" ); } }
            ]
/*        ,
            create: function(event,ui) {
                if(dialog.options.maxWidth && dialog.options.width) {
                    // fix maxWidth bug
                    $this.css("max-width", dialog.options.maxWidth);
                }
            }
*/
        };
        $('#bclogdialog').dialog($.extend({ }, bcDialogConfig));
        $('#bclogapprove').dialog($.extend({ }, bcDialogConfig, {
              width: 300,
              height: 200,
              buttons: [
                { text: "OK", click: function() { toTrash(); $(this).dialog("close"); } },
                { text: $.cattranslate('Close'), click: function() { $(this).dialog("close"); } }
              ]
        }));
        $('.bcshowlog').click(function(e) {
            e.preventDefault();
            $.ajax(
			{
				type:	 'GET',
				url:	 '{$CAT_ADMIN_URL}/dashboard',
				data:	 {
                    widget: "{$id}",
                    log: $(e.target).data("file")
                },
				cache:	 false,
                success: function(data,textStatus,jqXHR)
				{
                    if(data.success) {
                        $('#bclogdialog').html(data.content);
                        $('#bclogdialog').dialog("option", "title", $.cattranslate('Log') + ': ' + $(e.target).html() );
                        $('#bclogdialog').dialog("open");
                    } else {
                        BCGrowl(data.message,false);
                    }
                }
            });
        });
        $('a.fa-trash').unbind('click').on('click',function(e) {
            e.preventDefault();
            _target = $(e.target).data("file");
            $('#bclogapprove').html("{translate('Delete')}: " + $(e.target).data("file"));
            $('#bclogapprove').dialog("open");
        });
        $('a.fa-download').unbind('click').on('click',function(e) {
            e.preventDefault();
            $.ajax(
			{
				type:	 'GET',
				url:	 '{$CAT_ADMIN_URL}/dashboard',
				data:	 {
                    widget: {$id},
                    dl    : $(e.target).data("file")
                },
				cache:	 false,
                success: function(data,textStatus,jqXHR)
				{
                    BCGrowl(data.message,data.success);
                }
            });
        });
    })(jQuery);
</script>
