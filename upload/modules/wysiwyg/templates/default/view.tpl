{if $options.heading || $options.heading_text}
    {if $options.heading_text}<h{if $options.heading_level}{$options.heading_level}{else}3{/if} class="title">{$options.heading_text}<span></span></h{if $options.heading_level}{$options.heading_level}{else}3{/if}>{/if}
    {if $options.overall_text}<p class="subtitle">{$options.overall_text}</p>{/if}
{/if}
{if $options.columns}{$cols = $options.columns}{else}{$cols = count($columns)}{$cols = $cols - 1 }{/if}
<section class="mod-wysiwyg-columns">
    {for i 0 $cols}
    <div class="mod-wysiwyg-column{if $options.border} mod-wysiwyg-column-bordered{/if}">
        {$columns.$i}
    </div>
    {/for}
</section>