{* List Tab Start *}
            <div role="tabpanel" class="tab-pane active" id="list">
                <table class="table datatable compact">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>{translate('Preview')}</th>
                            <th>{translate('Filename')}</th>
                        </tr>
                    </thead>
                    <tbody class="gridder-table">
                    {foreach $files item}
                        <tr class="gridder-list" data-griddercontent="#gridder-content{$.foreach.default.iteration}">
                            <td>
                                {if user_has_perm('media_delete')}
                                <a href="" class="delete" data-title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id="{$item.filename}"><span class="fa fa-fw fa-trash text-danger"></span></a>
                                {else}<span class="fa fa-fw"></span>{/if}
                            </td>
                            <td>
                                {if $item.image && $item.preview}
                                <img src="{$CAT_URL}{$item.preview}" alt="{translate('Preview')}" title="{translate('Preview')}" class="thumb bs-media-details" />
                                {/if}
                                {if $item.video}
                                <div class="fa fa-fw fa-file-movie-o"></div>
                                {/if}
                            </td>
                            <td>
                                <strong>{cat_filename($item.filename)}</strong><br />
                                <span class="label">{translate('Type')}</span>
                                <span class="value filetype">{$item.mime_type}</span>
                                <span class="label">{translate('Size')}</span>
                                <span class="value">{$item.hfilesize}</span>
                                <span class="label">{translate('Date')}</span>
                                <span class="value">{$item.moddate}</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="hidden">
                            {include file="backend_media_griddercontent.tpl"}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
{* List Tab End *}