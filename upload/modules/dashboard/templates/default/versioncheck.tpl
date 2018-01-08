    {if $error || $version == 'unknown'}
    <div class="alert alert-danger">
    {translate('Version check failed!')}
    {if $error}<br />{$error}{/if}
    </div>
    {else}
    {if ! $newer && $version !== 'unknown'}
    {if $version < $CAT_VERSION}<div class="alert alert-warning">{translate("Your version is <strong>newer</strong> than the official version. You're a developer, aren't you? :)")}</div>
    {else}<div class="alert alert-success">{translate("You're up-to-date!")}</div>{/if}{/if}
    {if $newer}<div class="alert alert-info">{translate('A newer version is available!')}</div>{/if}
    {/if}

    <span style="display:inline-block;width:40%;">{translate('Local version')}:</span>{$CAT_VERSION}<br />
    <span style="display:inline-block;width:40%;">{translate('Remote version')}:</span>{translate($version)}<br />
    <span style="display:inline-block;width:40%;">{translate('Last checked')}:</span>{$last}<br />
    <span style="display:inline-block;width:40%;">{translate('Next check')}:</span>{$next}<br /><br />

    <form method="get" action="{$uri}">
      <input type="submit" name="widget_versioncheck_refresh" value="{translate('Check now')}" />
    </form>

    {if $newer}<a href="https://blackcat-cms.org/page/download.php" target="_blank">{translate('Visit download page')}</a>{/if}