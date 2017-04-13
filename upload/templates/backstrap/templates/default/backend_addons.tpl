<div>
    <span id="bsFilterSelect" class="pull-right">
      <label for="filter">{translate('Filter by type')}</label>
      <select name="filter" id="filter">
        <option value="">{translate('All')}</option>
        <option value="pages">{translate('Page modules')}</option>
        <option value="librarys">{translate('Libraries')}</option>
        <option value="tools">{translate('Admin-Tools')}</option>
        <option value="languages">{translate('Languages')}</option>
        <option value="templates">{translate('Templates')}</option>
      </select>
    </span>

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#installed" aria-controls="installed" role="tab" data-toggle="tab">{translate('Installed')}</a></li>
    <li role="presentation"><a href="#catalog" aria-controls="catalog" role="tab" data-toggle="tab">{translate('Catalog')}</a></li>
    <li role="presentation"><a href="#notinstalled" aria-controls="notinstalled" role="tab" data-toggle="tab">{translate('Not (yet) installed')}</a></li>
  </ul>

  {* Tab panes *}
  <div class="tab-content">
    {* Installed addons *}
    <div role="tabpanel" class="tab-pane active" id="installed">
    {include file="backend_addons_table.tpl" id="bsInstalledAddons"}
    </div>
    {* Catalog *}
    <div role="tabpanel" class="tab-pane" id="catalog">
      <table class="table">
        <thead>
          <tr>
            <th>{translate('Type')}</th>
            <th>{translate('Name')}</th>
            <th>{translate('Description')}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
    </div>
    {* Not installed *}
    <div role="tabpanel" class="tab-pane" id="notinstalled">
    {include file="backend_addons_table.tpl" id="bsNotInstalledAddons" type="notinstalled"}
    </div>
  </div>

</div>

    <div style="display:none;" id="bsAddonTemplate">
        <table>
          <tr class="type_%%type%%">
            <td class="bs-module-type" data-search="%%type%%s"><span class="fa fa-fw fa-%%type%%" title="%%type%%"></span></td>
            <td class="bs-module-name">
              <p><strong>%%name%%</strong></p>
              <span class="small">
                <a href="#" class="btn btn-xs btn-success"><span class="fa fa-plus"></span> {translate('Install')}</a>
              </span>
            </td>
            <td class="bs-module-desc">
              <p>%%description%%</p>
              <span class="small">
                <strong>{translate('Version')}:</strong> %%version%% |
                <strong>{translate('By')}:</strong> %%author%% |
                <strong>{translate('License')}:</strong> %%license%%
              </span>
            </td>
            <td></td>
          </tr>
        </table>
    </div>

{* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>