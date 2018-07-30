<section class="mod-wysiwyg-pricing{if $options.color_scheme} {$options.color_scheme}{/if}">{* outer wrapper *}
  <section>{if $options.columns}{$cols = $options.columns}{else}{$cols = count($columns)}{/if}
    {for i 1 $cols}
    <section class="mod-wysiwyg-pricing-panel mod-wysiwyg-pricing-panel-{$cols}{if $options.lift_panel && $options.lift_panel == $i} callout{/if}">
        {if $options.lift_panel && $options.lift_panel == $i}<h6>{if $options.lift_panel_text}{translate($options.lift_panel_text)}{else}{translate('Most popular')}{/if}</h6>{/if}
        <h4 data-option="plan_tier_heading" data-col="{$i}" data-section="{$section_id}" contenteditable="true">
            {$options.$i.plan_tier_heading}
        </h4>
        <h5>
            <sup class="superscript">{if $options.currency}{$options.currency}{/if}</sup>
            <span class="mod-wysiwyg-pricing-price" contenteditable="true" data-option="price" data-col="{$i}" data-section="{$section_id}">
                {if $options.$i.price}{$options.$i.price}{else}0{/if}
            </span>
            <sub>/mo</sub>
        </h5>
        <div contenteditable="true" data-col="{$i+1}" data-content="true" class="mod-wysiwyg-column{if $options.border} mod-wysiwyg-column-bordered{/if}">
            {$columns.$i}
        </div>
    </section>
    {/for}
    <div style="clear: both"></div>
  </section>
</section>
