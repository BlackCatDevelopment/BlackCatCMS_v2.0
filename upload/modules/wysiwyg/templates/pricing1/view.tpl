<section class="mod-wysiwyg-pricing{if $options.color_scheme} {$options.color_scheme}{/if}">{* outer wrapper *}
  <section>{if $options.columns}{$cols = $options.columns}{else}{$cols = count($columns)}{/if}
    {for i 1 $cols}
    <section class="mod-wysiwyg-pricing-panel-3 lift mod-wysiwyg-pricing-panel{if $options.lift_panel && $options.lift_panel == $i} callout{/if}" onclick="location.href='#';">
        {if $options.lift_panel && $options.lift_panel == $i}<h6>{if $options.lift_panel_text}{translate($options.lift_panel_text)}{else}{translate('Most popular')}{/if}</h6>{/if}
        <h4>{$options.$i.plan_tier_heading}</h4>
        <h5>
            <sup class="superscript">{if $options.currency}{$options.currency}{/if}</sup>
            <span class="mod-wysiwyg-pricing-price">{if $options.$i.price}{$options.$i.price}{else}0{/if}</span>
            <sub>/mo</sub>
        </h5>
        <span>{$columns.$i}</span>
    </section>
    {/for}
    <div style="clear: both"></div>
  </section>
</section>