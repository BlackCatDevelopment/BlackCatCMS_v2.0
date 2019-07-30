{foreach $blocks as block}
            <li class="card border-secondary" data-id="{$block.section_id}" id="section_{$block.section_id}">
              <div class="card-header">
                <table style="width:100%;">
                  <tr>
                    <td><span class="fa fa-fw fa-arrows"></span></td>
                    <td>
                        {if $avail_blocks}
                        <button class="btn dropdown-toggle" type="button" id="bsBlockDropdown{$block.section_id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span class="font-weight-bold">{translate('Block')}:</span> {$block.block}
                        </button>
                        <div class="dropdown-menu keep-open">
                          <div class="form-row">
                            <div class="col">
                              <select name="blocknr" class="form-control form-control-sm">
                              {foreach $avail_blocks number name}<option value="{$number}"{if $number == $block.block} selected="selected"{/if}>{$name}</option>{/foreach}
                              </select>
                            </div>
                            <div class="col">
                              <input type="submit" data-id="{$block.section_id}" class="form-control form-control-sm bsVariantSave" value="{translate('Save')}" />
                            </div>
                          </div>
                        </div>
                        {else}
                        <strong>{translate('Block')}:</strong> {$block.block}
                        {/if}
                    </td>
                    <td><strong>{translate('Name')}:</strong> <span class="editable" data-name="name" data-type="text" data-pk="{$block.page_id}#{$block.block}" data-url="{$CAT_ADMIN_URL}/section/edit/{$block.section_id}">{if !$block.name}<i>{translate('no name')}</i>{else}{$block.name}{/if}</span></td>
                    <td><strong>{translate('Module')}:</strong> {$block.module}</td>
                    <td><strong>{translate('Section ID')}:</strong> {$block.section_id}</td>
                    <td>
                      {if $block.available_variants}
                      <div class="dropdown">
                        {if $block.infofiles}
                        <i class="fa fa-fw fa-info-circle text-primary" data-toggle="modal" data-target="#bsVariantInfo{$block.section_id}"></i>
                        <div class="modal" tabindex="-1" role="dialog" id="bsVariantInfo{$block.section_id}">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">{translate('Variants')}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                              <div class="modal-body">
                                {foreach $block.available_variants variant}
                                <strong>{$variant}</strong><br />
                                {if $block.infofiles.$variant}
                                {include file=$block.infofiles.$variant}<br />
                                {else}
                                <p>{translate('No additional info available')}</p>
                                {/if}
                                {/foreach}
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{translate('Close')}</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        {/if}
                        <button class="btn dropdown-toggle" type="button" id="bsVariantDropdown{$block.section_id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span class="font-weight-bold">{translate('Variant')}:</span> {$block.variant}
                        </button>
                        <div class="dropdown-menu keep-open">
                          <div class="form-row">
                            <div class="col">
                              <select name="variant" class="form-control form-control-sm">
                              {foreach $block.available_variants variant}<option value="{$variant}"{if $variant == $block.variant} selected="selected"{/if}>{$variant}</option>{/foreach}
                              </select>
                            </div>
                            <div class="col">
                              <input type="submit" data-id="{$block.section_id}" class="form-control form-control-sm bsVariantSave" value="{translate('Save')}" />
                            </div>
                          </div>
                        </div>
                      </div>
                      {/if}
                    </td>
                    <td><span class="bsChangedFlag fa fa-fw" title="{translate('This section has unsaved changes')}"></span></td>
                    <td><span class="float-right toggle fa fa-fw fa-chevron-down"></span></td>
                  </tr>
                </table>
              </div>{* end card-header *}
              <div class="card-body pos-r">
                <div class="card-icon-wrapper bg-light">
                  {if user_has_perm('pages_edit')}
                  <ul class="nav nav-left">
                    {if $block.state_id==2}
                      <li><span class="fa fa-life-saver" title="{translate('Recover')}" data-id="{$block.section_id}"></span></li>
                    {else}
                      {if $block.options_file or $block.options_form}
                      <li class=""><span class="fa fa-cogs text-primary" title="{translate('This module has additional options, click here to show the settings panel.')}" data-id="{$block.section_id}"></span></li>
                      {/if}
                      <li><span class="fa fa-eye" title="{translate('If you set visibility to false, the section will <strong>not</strong> be shown. This means, all other settings - like periods of time - are ignored.')}" data-id="{$block.section_id}"></span></li>
                      <li><span class="fa fa-calendar" title="{translate('Set publishing period')}" data-id="{$block.section_id}" data-pubstart="{$block.publ_start}" data-pubend="{$block.publ_end}" data-timestart="{$block.publ_by_time_start}" data-timeend="{$block.publ_by_time_end}"></span></li>
                      {if user_has_perm('pages_section_move') && user_has_module_perm($block.module)}
                      <li><span class="fa fa-external-link" title="{translate('Move')}" data-id="{$block.section_id}" data-module="{$block.module}"></span></li>
                      {/if}
                      {if block_has_revisions($block.section_id)}
                      <li><span class="fa fa-clone" title="{translate('View revisions')}"></span></li>
                      {/if}
                    {/if}
                    {if user_has_perm('pages_section_delete') && user_has_module_perm($block.module)}
                      <li><span class="fa fa-trash text-danger" title="{translate('Delete')}" data-id="{$block.section_id}" data-module="{$block.module}"></span></li>
                    {/if}
                  </ul>
                  {/if}
                </div>
                <div class="card-content">
                  {if $block.options_file or $block.options_form}
                  <div id="bsOptionsPanel_{$block.section_id}" class="options-panel" style="display:none">
                  {if $block.options_file}{include $block.options_file}<br />{/if}
                  {if $block.options_form}{$block.options_form}{/if}
                  </div>
                  {/if}
                  {if $block.active}
                  <div class="section-content">{$block.section_content}</div>
                  {else}
                  <i><small>{translate('This section is marked as deleted.')}{if user_has_perm('pages_section_recover')} {translate('You may recover it by clicking on the recover icon.')} <i class="fa fa-life-saver"></i>{/if}</small></i>
                  {/if}
                </div>
                <div class="card-footer text-right">
                  <button class="btn btn-primary btn-save" data-id="{$block.section_id}" data-page="{$page.page_id}">{translate('Save')}</button>
                </div>
              </div>
            </li>
{/foreach}