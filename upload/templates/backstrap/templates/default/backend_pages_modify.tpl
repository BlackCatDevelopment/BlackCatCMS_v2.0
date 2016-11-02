<div class="row flex">
    <div class="col-md-12">
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
                        <ul class="nav nav-left">
                            <li><span class="fa fa-eye" data-title="{translate('Visibility')}"></span></li>
                            <li><span class="fa fa-calendar" data-title="{translate('Set publishing period')}" data-id="{$block.meta.section_id}" data-pubstart="{$block.meta.publ_start}" data-pubend="{$block.meta.publ_end}"></span></li>
                            <li><span class="fa fa-trash" data-title="{translate('Delete')}" data-id="{$block.meta.section_id}"></span></li>
                        </ul>
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
     </div>
</div>

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
console.log($(this).parent().find('input.timepicker'));
            $(this).datetimepicker(
            {
	           altField: "#time_from"
            });
        });
/*
        $('body').on('focus',"input.datepicker",function(){
            var date = $.datepicker.parseDate('@', $(this).data('date')*1000);
            $(this).datepicker(
                $.datepicker.regional[{/literal}'{$LANGUAGE}'{literal}.toLowerCase()],
                { defaultValue: date }
            );
        });
        $('body').on('focus',"input.timepicker",function(){
            var date = $.datepicker.parseDate('@', $(this).data('date')*1000);
            $(this).timepicker(
                $.timepicker.regional[{/literal}'{$LANGUAGE}'{literal}.toLowerCase()],
                { defaultValue: date }
            );
        });
*/
    });
//]]>
</script>
{/literal}