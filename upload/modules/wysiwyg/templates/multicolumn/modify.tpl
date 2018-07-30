{if $options.columns}{$cols=$options.columns}{else}{$cols=count($columns)}{/if}
<section class="mod-wysiwyg-columns">
    {for i 1 $cols}
    <div data-col="{$i}" id="editor_{$section_id}_{$i}" contenteditable="true" class="mod-wysiwyg-column{if $options.border} mod-wysiwyg-column-bordered{/if}"{if $options.border_radius} style="border-radius:{$options.border_radius}px"{/if}>
        {$columns.$i.content}
    </div>
    {/for}
</section>
