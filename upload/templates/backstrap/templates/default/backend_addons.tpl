<div>
    <span id="bsFilterSelect" class="pull-right">
      <label for="filter">{translate('Filter by type')}</label>
      <select name="filter" id="filter">
        <option value="">{translate('All')}</option>
        <option value="languages">{translate('Languages')}</option>
        <option value="modules">{translate('Modules')}</option>
        <option value="templates">{translate('Templates')}</option>
      </select>
    </span>

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#installed" aria-controls="installed" role="tab" data-toggle="tab">{translate('Installed')}</a></li>
    <li role="presentation"><a href="#catalog" aria-controls="catalog" role="tab" data-toggle="tab">{translate('Catalog')}</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="installed">
      <table class="table datatable compact" id="bsInstalledAddons">
        <thead>
          <tr>
            <th>{translate('Type')}</th>
            <th>{translate('Name')}</th>
            <th>{translate('Description')}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
{foreach $modules module}
          <tr class="type_{$module.type}">
            <td class="bs-module-type" data-search="{$module.type}s"><span class="fa fa-fw fa-{$module.type}" title="{$module.type}"></span></td>
            <td class="bs-module-name">
              <p><strong>{$module.name}</strong></p>
              <span class="small">
                <a href="#" class="btn btn-xs btn-danger{if $module.removable != 'Y'} disabled{/if}"><span class="fa fa-remove"></span> {translate('Uninstall')}</a>
              </span>
            </td>
            <td class="bs-module-desc">
              <p>{$module.description}</p>
              <span class="small">
                <strong>{translate('Version')}:</strong> {$module.version} |
                <strong>{translate('By')}:</strong> {$module.author} |
                {if $module.license}<strong>{translate('License')}:</strong> {$module.license} |{/if}
                <strong>{translate('Installed')}:</strong> {$module.install_date}
              </span>
            </td>
            <td>{if $module.icon}<img src="{$module.icon}" alt="Icon" />{/if}</td>
          </tr>
{/foreach}
        </tbody>
      </table>
    </div>
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
    var lang   = '{$LANGUAGE}'.toLowerCase();
    var dtable = $('table.datatable').DataTable({
        mark: true,
        stateSave: true,
        orderClasses: false{if cat_file_exists($file)},
        language: {
            url: "{cat_asset_url($file,'js')}"
        }
        {/if}
    });
    /* Custom filtering function which will search data in column four between two values */
    $.fn.dataTable.ext.search.push(
        function(settings,data,dataIndex) {
            var find = $('select#filter option:selected').val();
            if(find == '' || data[0] == find) {
                return true;
            }
            return false;
        }
    );
    $('select#filter').on('change', function () {
        dtable.draw();
    });
//]]>
</script>
