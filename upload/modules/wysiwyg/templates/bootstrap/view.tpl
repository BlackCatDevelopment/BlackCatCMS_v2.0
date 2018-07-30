<div class="container-flex">
    {if $options.heading || $options.heading_text}
    <div class="row">
        <div class="col-md-12 heading">
            {if $options.heading_text}<h{if $options.heading_level}{$options.heading_level}{else}3{/if} class="title">{$options.heading_text}<span></span></h{if $options.heading_level}{$options.heading_level}{else}3{/if}>{/if}
            {if $options.overall_text}<p class="subtitle">{$options.overall_text}</p>{/if}
        </div>
    </div>
    {/if}
    {if $options.columns}{$cols = $options.columns}{else}{$cols = count($content)}{$cols = $cols -1 }{/if}
    <div class="row">
    {for i 0 $cols}
        <div class="col{if $options.border} mx-1 border{/if}">{$content.$i}</div>
    {/for}
    </div>
</div>

