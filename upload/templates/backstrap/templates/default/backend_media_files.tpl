{include file="backend_media_tabs.tpl"}
{include file="backend_media_folderselect.tpl"}
                <table class="table">
                    <thead>
                        <tr>
                            <th>{translate('Actions')}</th>
                            <th>{translate('Preview')}</th>
                            <th>{translate('Filename')}</th>
                        </tr>
                    </thead>
                    <tbody class="gridder-table">
{foreach $files file}
                        <tr class="gridder-expander" data-gridder-url="" data-gridder-title="">
                            <td>
                                {if user_has_perm('media_delete')}
                                <a href="" class="delete" title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id="{$file.media_id}">
                                    <span class="fa fa-fw fa-trash text-danger"></span>
                                </a>
                                {else}<span class="fa fa-fw"></span>{/if}
                            </td>
                            <td>
                                <a href="{$baseurl}/{$file.filename}" data-toggle="lightbox" data-gallery="preview">
{if $file.isImage}
                                <img src="{$baseurl}/{$file.filename}" alt="{translate('Preview')}" title="{translate('Preview')}" class="thumb bs-media-details" data-field="preview" />
{else}
                                <div class="fa fa-fw fa-file-movie-o"></div>
{/if}
                                </a>
                            </td>
                            <td class="small">
                                <span class="filename">{$file.filename}</span><br />
                                <span class="dtlabel">{translate('Type')}</span>
                                <span class="value filetype">{$file.mime_type}</span>
                                <span class="dtlabel">{translate('Size')}</span>
                                <span class="value hfilesize">{$file.hfilesize}</span>
                                <span class="dtlabel">{translate('Date')}</span>
                                <span class="value moddate">{$file.moddate}</span>
                            </td>
                        </tr>
{/foreach}
                    </tbody>
                </table>

        <form method="GET" action="{$CAT_ADMIN_URL}/media/update">
            <button class="btn btn-primary">{translate('Scan for updates')}</button>
            <span class="text-muted">{translate('If you have added, moved or deleted files or folders by FTP, click this button to update the database.')}</span>
        </form>

