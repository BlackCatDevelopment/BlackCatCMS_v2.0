<div class="detach">{translate('Page')}: {$meta.page.menu_title} (ID: {$meta.page.page_id})</div>

<div class="row flex">
  <div class="col-md-12">
    {if $addons}
    <span id="bsAddonSelect" class="pull-right">
      <label for="module">{translate('Add section')}</label>
      <select name="module" id="module">
        {foreach $addons addon}
        <option value="{$addon.addon_id}">{$addon.name}</option>
        {/foreach}
      </select>
      <button class="btn btn-primary" id="bsAddonAdd" data-page="{$meta.page.page_id}" data-block="1">{translate('Submit')}</button>
    </span>
    {/if}

    <ul class="nav nav-tabs" role="tablist">{* Tabs *}
      <li role="presentation" class="active"><a href="#contents" aria-controls="contents" role="tab" data-toggle="tab">{translate('Content')}</a></li>
      <li role="presentation"><a href="#config" aria-controls="config" role="tab" data-toggle="tab">{translate('Settings')}</a></li>
      <li role="presentation"><a href="#translations" aria-controls="translations" role="tab" data-toggle="tab">{translate('Translations')}</a></li>
    </ul>

    <div class="tab-content">{* Tab panes *}

      <div role="tabpanel" class="tab-pane active" id="contents">{* START #contents tab-pane *}
{if $blocks}
        <ul class="draggable-panel">
{foreach $blocks as block}
          <li class="panel panel-primary">
            <div class="panel-heading">
              <table style="width:100%;">
                <tr>
                    <td><strong>{translate('Block')}:</strong> {$block.meta.blockname} ({translate('Block number')}: {$block.meta.block})</td>
                    <td><strong>{translate('Name')}:</strong> <span class="editable" data-name="name" data-type="text" data-pk="{$block.meta.section_id}" data-url="{$CAT_ADMIN_URL}/sections/edit">{if !$block.meta.name}<i>{translate('no name')}</i>{else}{$block.meta.name}{/if}</span></td>
                    <td><strong>{translate('Module')}:</strong> {$block.meta.module}</td>
                    <td><strong>{translate('Section ID')}:</strong> {$block.meta.section_id}</td>
                </tr>
              </table>
            </div>
            <div class="panel-body pos-r">
              <div class="panel-icon-wrapper bg-white">
                {if user_has_perm('pages_edit')}
                <ul class="nav nav-left">
                    <li><span class="fa fa-eye" data-title="{translate('Visibility')}" data-id="{$block.meta.section_id}"></span></li>
                    <li><span class="fa fa-calendar" data-title="{translate('Set publishing period')}" data-id="{$block.meta.section_id}" data-pubstart="{$block.meta.publ_start}" data-pubend="{$block.meta.publ_end}"></span></li>
                    {if user_has_perm('pages_section_delete') && user_has_module_perm($block.meta.module)}
                    <li><span class="fa fa-trash" data-title="{translate('Delete')}" data-id="{$block.meta.section_id}"></span></li>
                    {/if}
                </ul>
                {/if}
              </div>
              <div class="panel-content">
                {$block.content}
              </div>
            </div>
          </li>
{/foreach}
        </ul>
{else}
        <div class="alert alert-info alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          {translate('No sections were found for this page')}
        </div>
{/if}
      </div>{* END #contents tab-pane *}

      <div role="tabpanel" class="tab-pane" id="config">{* START #config tab-pane *}
        <ul class="nav nav-tabs inner" role="tablist">{* Tabs *}
          <li role="presentation" class="active"><a href="#meta" aria-controls="meta" role="tab" data-toggle="tab">{translate('Meta')} / SEO</a></li>
          <li role="presentation"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{translate('General')}</a></li>
          <li role="presentation"><a href="#header" aria-controls="header" role="tab" data-toggle="tab">{translate('Header files')}</a></li>
        </ul>

        <div class="tab-content">{* INNER Tab panes *}

          {* START meta tab *}
          <div role="tabpanel" class="tab-pane active" id="meta">
            {cat_form('be_page_settings')}
          </div>
          {* END meta tab *}

          {* START general tab *}
          <div role="tabpanel" class="tab-pane" id="general">
            {cat_form('be_page_general')}
          </div>
          {* END general tab *}

          {* START header tab *}
          <div role="tabpanel" class="tab-pane" id="header">
            <form class="form-horizontal">
              
            </form>
          </div>
          {* END header tab *}

        </div>{* end INNER *}
        
      </div>{* END #config tab-pane *}

      <div role="tabpanel" class="tab-pane" id="translations">{* START #translations tab-pane *}
      {translate('You can link any page to other pages in different languages that have the same content.')}
      </div>
    </div>{* END tab content *}
  </div>{* END col *}
</div>{* END row *}

{include(file='backend_modal.tpl' modal_id='modal_dialog' modal_title='', modal_text='', modal_savebtn='1')}

<div class="hidden" id="publishing">
  <div class="container-fluid">
    <div class="input-group row">
      <span class="input-group-addon col-3">{translate('Date from')}</span>
      <input type="text" class="form-control datepicker col-3" name="date_from" id="date_from" />
      <span class="input-group-addon col-3">{translate('Time from')}</span>
      <input type="text" class="form-control timepicker col-3" name="time_from" id="time_from" />
    </div><br />
    <div class="input-group row">
      <span class="input-group-addon col-3">{translate('Date until')}</span>
      <input type="text" class="form-control datepicker col-3" name="date_until" id="date_until" />
      <span class="input-group-addon col-3">{translate('Time until')}</span>
      <input type="text" class="form-control timepicker col-3" name="time_until" id="time_until" />
    </div><br />
  </div>
</div>

{literal}
<script type="text/javascript">
//<![CDATA[
    $(function(){ //DOM Ready
        var _MS_PER_DAY = 1000 * 60 * 60 * 24;

        // a and b are javascript Date objects
        function dateDiffInDays(a, b) {
          // Discard the time and time-zone information.
          var utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
          var utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
          return Math.floor((utc2 - utc1) / _MS_PER_DAY);
        }

        // "disable" fuelUX datepicker
        if (!$.fn.bootstrapDP && $.fn.datepicker && $.fn.datepicker.noConflict) {
            var datepicker = $.fn.datepicker.noConflict();
            $.fn.bootstrapDP = datepicker;
        }

        // delete section
        $('.fa-trash').unbind('click').on('click', function(e) {
            var id = $(this).data('id');
            $.ajax({
                type    : 'POST',
                url     : '{/literal}{$CAT_ADMIN_URL}{literal}/section/delete/' + id,
                dataType: 'json',
                success : function(data, status) {
                }
            });
        });

        // add section
        $('button#bsAddonAdd').unbind('click').on('click', function(e) {
            var module = $('select#module option:selected').val(),
                page   = $(this).data('page');
            $.ajax({
                type    : 'POST',
                url     : '{/literal}{$CAT_ADMIN_URL}{literal}/section/add',
                dataType: 'json',
                data    : {
                    module: module,
                    block : 1,
                    page  : page
                },
                success : function(data, status) {
                }
            });
        });


        // attach publishing date dialog
        $('.fa-calendar').on('click',function(e) {
            var $this = $(this),
                id    = $this.data('id'),
                clone = $('#publishing').clone().detach()
                ;
            
            if($this.data('pubstart') != 0) {
                var date = $.datepicker.parseDate('@',$this.data('pubstart')*1000);
                $(clone).find('input[name="date_from"]')
                        .attr('data-date',$this.data('pubstart'))
                        .attr('value',$.datepicker.formatDate("dd.mm.yy", date));
                $(clone).find('input[name="time_from"]')
                        .attr('data-date',$this.data('pubstart'))
                        .attr('value',$.datepicker.formatTime('HH:mm', date));
            }
            if($this.data('pubend') != 0) {
                var date = $.datepicker.parseDate('@',$this.data('pubend')*1000);
                $(clone).find('input[name="date_until"]')
                        .attr('data-date',$this.data('pubend'))
                        .attr('value',$.datepicker.formatDate("dd.mm.yy", date));
                $(clone).find('input[name="time_until"]')
                        .attr('data-date',$this.data('pubend'))
                        .attr('value',$.datepicker.formatTime('HH:mm', (new Date(date))));
            }

            $('.modal-body').html(clone.html());
            $('.modal-title').text("{/literal}{translate('Set publishing period')}{literal}");
            $('#modal_dialog').modal('show');

            // note: the unbind() is necessary to prevent multiple execution!
            $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
                e.preventDefault();
                var dates = {};
                $('#modal_dialog').modal('hide');
                if($('#date_from').val() != '') {
                    var date_from = $("#date_from").datepicker("getDate");
                    if($('#time_from').val() != '') {
                        var time_from = $("#time_from").datetimepicker("getDate");
                        date_from.setHours(time_from.getHours());
                        date_from.setMinutes(time_from.getMinutes());
                    }
                    dates.publ_start = date_from.getTime() / 1000;
                }
                if($('#date_until').val() != '') {
                    var date_until = $("#date_until").datepicker("getDate");
                    if($('#time_until').val() != '') {
                        var time_until = $("#time_until").datetimepicker("getDate");
                        date_until.setHours(time_until.getHours());
                        date_until.setMinutes(time_until.getMinutes());
                    }
                    dates.publ_end = date_until.getTime() / 1000;
                }
                if(dates) {
                    dates.id = id;
                    $.ajax({
                        type    : 'POST',
                        url     : '{/literal}{$CAT_ADMIN_URL}{literal}/section/publish/' + id,
                        dataType: 'json',
                        data    : dates,
                        success : function(data, status) {
                        }
                    });
                }
            });
        });

        // attach datepicker; secure for dynamically added elements ('focus')
        $('body').on('focus','input.datepicker',function(){
            $(this).datetimepicker(
            {
	           altField: "#time_from"
            });
        });
    });
//]]>
</script>
{/literal}