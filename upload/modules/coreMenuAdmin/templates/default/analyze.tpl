    <div id="coreMenuAdmin">
        <h2>{translate('BlackCat Menu Manager')}</h2>
        <form method="post">
            <fieldset>
                <legend>{translate('Analyze HTML')}</legend>
                <textarea class="form-control" id="html" name="html" placeholder="{translate('Paste the HTML to be analyzed here')}">{$html}</textarea><br />
                <div class="alert alert-info">
                {translate('By default, the Menu Manager will search for a &lt;nav&gt; node and then analyze its content. If there\'s no &lt;nav&gt; element in your HTML, please paste the complete start node into the field below.')}<br />
                </div>
                <input type="text" name="startnode" id="startnode" value="{$startnode}" placeholder="{translate('Example')}: &lt;div class=&quot;navbar&quot;&gt;"/><br />
                <input type="submit" value="{translate('Submit')}" />
                <input type="submit" name="cancel" id="cancel" class="cancel" value="{translate('Cancel')}" />
            </fieldset>
        </form>
{if $html}
        <br />
        <h3>{translate('Analyze result')}</h3>
        <div class="alert alert-info">
            {translate('You can change any settings before saving them to a menu')}
        </div>
        {$form}
{/if}
    </div>
