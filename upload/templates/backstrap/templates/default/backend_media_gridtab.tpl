            <div role="tabpanel" class="tab-pane" id="grid">
                <ul class="gridder">
                    <li class="gridder-expander" data-griddercontent="" hidden>
                        <div class="actions">
                            {if user_has_perm('media_delete')}
                            <a href="" class="delete" title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id=""><span class="fa fa-fw fa-trash text-danger"></span></a>
                            {else}<span class="fa fa-fw">ss</span>{/if}
                        </div>
                        <img src="" alt="{translate('Preview')}" title="{translate('Preview')}" class="thumb bs-media-details hidden" data-field="preview" />
                        <div class="fa fa-fw fa-file-movie-o" hidden></div>
                    </li>
                </ul>
            </div>
