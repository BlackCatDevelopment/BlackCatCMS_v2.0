{if $options.columns}{$cols = $options.columns}{else}{$cols = count($columns)}{/if}
<div class="mod-wysiwyg-pricing-table">
    {for i 1 $cols}
    <div class="plan plan-{$cols}-cols plan{$dwoo.for.i.iteration}{if $options.lift_panel && $options.lift_panel == $dwoo.for.i.iteration} popular-plan{/if}">
        <div class="header">
            {$columns.$i.plan_tier_heading}
            {if $options.lift_panel && $options.lift_panel == $dwoo.for.i.iteration}<div class="twelve-point-star"></div>{/if}
        </div>
        <div class="price">{if $options.currency}{$options.currency}{/if} {if $columns.$i.price}{$columns.$i.price}{else}0{/if}</div>
        <div class="monthly">{if $columns.$i.per}{$columns.$i.per}{/if}</div>
        <div>{$columns.$i.content}</div>
        <a class="signup" href="#">{translate('Sign up')}</a>
    </div>
    {/for}
</div>