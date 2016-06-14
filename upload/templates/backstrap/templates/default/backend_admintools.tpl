<div class="row flex">
    <ul class="tools">
        {foreach $tools tool}
        <li>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <div>{$tool.TOOL_NAME}</div>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">{if $tool.ICON}<img src="{$tool.ICON}" />{/if}</div>
                    <div class="col-md-8">{$tool.TOOL_DESCRIPTION}</div>
                </div>
            </div>
        </div>
        </li>
        {/foreach}
    </ul>
</div>
