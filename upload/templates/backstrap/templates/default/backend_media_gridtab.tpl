            <div role="tabpanel" class="tab-pane" id="grid">
                <ul class="gridder">
                    {foreach $files item}
                    <li class="gridder-list" data-griddercontent="#gridder-content{$.foreach.default.iteration}">
                        <div class="actions">
                            {if user_has_perm('media_delete')}
                            <a href="" class="delete" data-title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id="{$item.filename}"><span class="fa fa-fw fa-trash text-danger"></span></a>
                            {else}<span class="fa fa-fw"></span>{/if}
                        </div>
                    {if $item.image && $item.preview}
                        <img src="{$CAT_URL}{$item.preview}" class="thumb" alt="{translate('Preview')}" data-title="{translate('Left click for more details')}" />
                    {else}
                        {if $item.video}
                        <div class="fa fa-fw fa-file-movie-o"></div>
                        {/if}
                    {/if}
                    </li>
                    {/foreach}
                </ul>
                {foreach $files item}
                {include file="backend_media_griddercontent.tpl"}
                {/foreach}
            </div>
