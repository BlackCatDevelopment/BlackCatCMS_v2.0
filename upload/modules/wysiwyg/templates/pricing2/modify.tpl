{if $options.columns}{$cols = $options.columns}{else}{$cols = count($columns)}{/if}{$index=$cols-1}
<div class="mod-wysiwyg-pricing-table">
    {for i 1 $cols}
    <div class="plan plan-{$cols}-cols plan{$dwoo.for.i.iteration}{if $options.lift_panel && $options.lift_panel == $dwoo.for.i.iteration} popular-plan{/if}">
        <div class="header" data-option="plan_tier_heading" data-col="{$i}" data-section="{$section_id}" contenteditable="true">
            {$columns.$i.plan_tier_heading}
        </div>
        <div class="price">
            {if $options.currency}<span title="{translate('You can set the currency in the options. Click on the cogs to open the options form.')}">{$options.currency}</span>{/if}
            <span data-option="price" data-col="{$i}" data-section="{$section_id}" contenteditable="true">{if $columns.$i.price}{$columns.$i.price}{else}0{/if}</span>
        </div>
        <div class="monthly" data-option="per" data-col="{$i}" data-section="{$section_id}" contenteditable="true">
            per month
        </div>
        <div contenteditable="true" data-col="{$i+1}" data-content="true">
            {$columns.$i.content}
        </div>
        <a class="signup" href="#">{translate('Sign up')}</a>
    </div>
    {/for}
</div>
