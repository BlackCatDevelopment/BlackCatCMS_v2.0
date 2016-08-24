{if $addable}
<div style="width:100%;text-align:right;">
    <form>
        <input type="hidden" name="dashboard_add_widget_module" id="dashboard_add_widget_module" value="{$module}" />
        <label for="dashboard_add_widget">{translate('Add widget')}</label>
        <select name="dashboard_add_widget" id="dashboard_add_widget">
            {foreach $addable item}
            <option value="{$item.path}">{$item.title}</option>
            {/foreach}
        </select>
        <button id="dashboard_add_widget_submit">{translate('Insert')}</button>
        <button id="dashboard_reset">{translate('Reset Dashboard')}</button>
    </form>
</div>
{/if}

{if ! $dashboard}
<div class="alert alert-info">
    {translate('This is your dashboard, but it\'s empty because you do not have any widgets to show.')}
</div>
{else}
{for row 0 $dashboard.row_count-1}{* render rows *}
<div class="row flex">
    {for col 1 $dashboard.col_count}{* render columns *}
    <div class="col-lg-{math "12 / $dashboard.col_count"} col-md-{math "12 / $dashboard.col_count * 2"}">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="fa fa-{$dashboard.columns.$col.widgets.$row.settings.fa_icon}"></span>
                    </div>
                    <div class="col-xs-7 text-right">
                        <div>{$dashboard.columns.$col.widgets.$row.settings.widget_title}</div>
                    </div>
                    <div class="col-xs-2 text-right">
                        <button class="btn btn-primary{if $dashboard.columns.$col.widgets.$row.isMinimized} collapsed{/if}" type="button" data-toggle="collapse" data-target="#collpanel_{$col}_{$row}" aria-expanded="false" aria-controls="collpanel_{$col}_{$row}"><span class="fa fa-eye"></span></button>
                        <button class="btn btn-primary eraser" data-widget="{$dashboard.columns.$col.widgets.$row.widget_path}" type="button" data-module="{$dashboard.columns.$col.widgets.$row.module_directory}" data-name="{$dashboard.columns.$col.widgets.$row.settings.widget_title}" data-toggle="modal" data-target="#confirm"><span class="fa fa-eraser"></span></button>
                    </div>
                </div>
            </div>
            <div id="collpanel_{$col}_{$row}" class="panel-collapse collapse{if ! $dashboard.columns.$col.widgets.$row.isMinimized} in{/if}">
                <div class="panel-body">
                    {$dashboard.columns.$col.widgets.$row.content}
                </div>
            </div>
        </div>
    </div>
    {/for}
</div>
{/for}
{include(file='fuelux_modal.tpl' modal_id='confirm' modal_title='Remove widget', modal_text='Do you really want to remove this widget from your dashboard?')}
{/if}
