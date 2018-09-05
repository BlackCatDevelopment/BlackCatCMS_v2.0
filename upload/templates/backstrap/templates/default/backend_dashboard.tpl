<button style="display:none" class="btn btn-sm btn-primary detach" id="bsAddWidget">{translate('Add widget')}</button>

<div class="dashboard" data-id="{$dashboard.id}" data-columns="{$dashboard.columns}">
{if count($dashboard.widgets)}
    <button role="button" class="btn btn-sm btn-warning detach" id="dashboard_reset" data-id="{$dashboard.id}">{translate('Reset Dashboard')}</button>
    <div class="card-columns column-count-{$dashboard.columns}">
{foreach $dashboard.widgets widget}
            <div class="card"{if $widget.widget_id} data-id="{$widget.widget_id}"{/if}>
                <div class="card-header">
                    {if $widget.icon}<span class="fa fa-fw {$widget.icon}"></span>{/if}
                    {if $widget.widget_name}{translate($widget.widget_name)}{/if}
                    {if $widget.link}{$widget.link}{/if}
                    <span class="float-right remove fa fa-fw fa-trash"{if $widget.widget_id} data-id="{$widget.widget_id}"{/if}></span>
                    <span class="float-right toggle{if $widget.open != 'Y'} card-collapsed{/if} fa fa-fw fa-eye{if $widget.open != 'Y'}-slash{/if}"></span>
                </div>
                <div class="card-body"{if $widget.open != 'Y'} style="display:none;"{/if}>
                    {$widget.content}
                </div>
            </div>
    {/foreach}
    </div>
{else}
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="{translate('Close')}"><span aria-hidden="true">&times;</span></button>
        <p>{translate('There are no widgets on your dashboard.')}</p>
        {if user_has_perm('dashboard_config')}<p id="bsAddWidgetInfo" style="display:none;">{translate('You can add widgets to your dashboard by clicking on the [Add widget] button')}</p>{/if}
        <p id="bsNoWidgets" style="display:none">{translate('No addable widgets found.')}</p>
    </div>
{/if}
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
