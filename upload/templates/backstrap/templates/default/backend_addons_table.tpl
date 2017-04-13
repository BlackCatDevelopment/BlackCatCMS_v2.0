      <table class="table dtable compact" id="{$id}">
        <thead>
          <tr>
            <th>{translate('Type')}</th>
            <th>{translate('Name')}</th>
            <th>{translate('Description')}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
{if $type=='notinstalled'}{$modules=$notinstalled}{/if}
{foreach $modules module}
          <tr class="type_{$module.type}">
            <td class="bs-module-type" data-search="{$module.type}s"><span class="fa fa-fw fa-{$module.type}" title="{$module.type}"></span></td>
            <td class="bs-module-name">
              <p><strong>{$module.name}</strong></p>
              <span class="small">
              {if $module.install_date}<a href="#" class="btn btn-xs btn-danger{if $module.removable != 'Y'} disabled{/if}"><span class="fa fa-remove"></span> {translate('Uninstall')}</a>
              {else}<a href="#" class="btn btn-xs btn-success"><span class="fa fa-plus"></span> {translate('Install')}</a>
              {/if}
              </span>
              
            </td>
            <td class="bs-module-desc">
              {if $module.description}<p>{$module.description}</p>{/if}
              <span class="small">
                <strong>{translate('Version')}:</strong> {$module.version}
                {if $module.author} | <strong>{translate('By')}:</strong> {$module.author}{/if}
                {if $module.license} | <strong>{translate('License')}:</strong> {$module.license}{/if}
                {if $module.install_date} | <strong>{translate('Installed')}:</strong> {$module.install_date}{/if}
              </span>
            </td>
            <td>{if $module.icon}<img src="{$module.icon}" alt="Icon" />{/if}</td>
          </tr>
{/foreach}
        </tbody>
      </table>