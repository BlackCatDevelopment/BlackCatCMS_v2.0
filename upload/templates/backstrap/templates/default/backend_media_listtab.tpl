{* List Tab Start *}
                <table class="table datatable compact">
                    <thead>
                        <tr>
                            <th>{translate('Actions')}</th>
                            <th>{translate('Preview')}</th>
                            <th>{translate('Filename')}</th>
                        </tr>
                    </thead>
                    <tbody class="gridder-table">
                        <tr class="gridder-expander hidden" data-gridder-url="" data-gridder-title="">
                            <td>
                                {if user_has_perm('media_delete')}
                                <a href="" class="delete" data-title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id="">
                                    <span class="fa fa-fw fa-trash text-danger"></span>
                                </a>
                                {else}<span class="fa fa-fw"></span>{/if}
                            </td>
                            <td>
                                <img src="{$CAT_URL}" alt="{translate('Preview')}" title="{translate('Preview')}" class="thumb bs-media-details hidden" data-field="preview" />
                                <div class="fa fa-fw fa-file-movie-o hidden"></div>
                            </td>
                            <td class="small">
                                <strong data-field="filename"></strong><br />
                                <span class="dtlabel">{translate('Type')}</span>
                                <span class="value filetype" data-field="mime_type"></span>
                                <span class="dtlabel">{translate('Size')}</span>
                                <span class="value" data-field="hfilesize"></span>
                                <span class="dtlabel">{translate('Date')}</span>
                                <span class="value" data-field="moddate"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
{* List Tab End *}