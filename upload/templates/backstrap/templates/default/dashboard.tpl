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
    {translate('This is your dashboard, but it\'s empty because you do not have superuser permissions. Use the page tree to edit pages. Use the top links to navigate through the backend.')}
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
                        <div data-action="close" data-widget="{$dashboard.columns.$col.widgets.$row.widget_path}" class="fa fa-eye{if $dashboard.columns.$col.widgets.$row.isMinimized}-slash{/if}"></div>
                        <div data-action="remove" data-widget="{$dashboard.columns.$col.widgets.$row.widget_path}" class="fa fa-eraser"></div>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                {$dashboard.columns.$col.widgets.$row.content}
            </div>
        </div>
    </div>
    {/for}
</div>
{/for}
{/if}


                