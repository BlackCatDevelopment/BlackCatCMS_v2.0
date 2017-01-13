                <div id="gridder-content{$.foreach.default.iteration}" class="gridder-content panel">
                    <div class="row">
    				    <div class="col-sm-6">
                            {if $item.image && $item.preview}
                            <img src="{$CAT_URL}{$item.preview}" alt="{translate('Preview')}" class="img-responsive" />
                            {else}
                                {if $item.video && ! $item.mime_type == 'video/x-flv'}
                                    <video width="320" height="240" controls>
                                        <source src="{$CAT_URL}{$item.url}" type="{$item.mime_type}">
                                        {translate('Your browser does not support the video tag')}
                                    </video>
                                {else}
                                    <div class="fa fa-fw fa-file-movie-o"></div><br />
                                    <small class="text-info">{translate('Sorry, no preview available for this mime type!')}</small>
                                {/if}
                            {/if}
                        </div>
    					<div class="col-sm-6">
                            <strong>{cat_filename($item.filename)}</strong>
                            {if user_has_perm('media_delete')}
                            <a href="" class="delete" data-title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id="{$item.filename}"><span class="fa fa-fw fa-trash text-danger"></span></a>
                            {else}<span class="fa fa-fw"></span>{/if}
                            <br /><br />
                            {if $item.copyright}
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>{translate('Copyright')}:</strong>
                                </div>
                                <div class="col-sm-6">
                                    <strong>{$item.copyright}</strong>
                                </div>
                            </div>
                            {/if}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Type')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.mime_type}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Size')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.hfilesize}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Last modified')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.moddate}
                                </div>
                            </div>
                            {if $item.encoding}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Encoding')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.encoding}
                                </div>
                            </div>
                            {/if}
                            {if $item.bits_per_sample}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Bits per sample')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.bits_per_sample} {translate('Bit')}
                                </div>
                            </div>
                            {/if}
                            {if $item.resolution_y}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Resolution Y')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.resolution_y}
                                </div>
                            </div>
                            {/if}
                            {if $item.resolution_x}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Resolution X')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.resolution_x}
                                </div>
                            </div>
                            {/if}
                            {if $item.exif.Make}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Exif Make')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.exif.Make}
                                </div>
                            </div>
                            {/if}
                            {if $item.exif.Model}
                            <div class="row">
                                <div class="col-sm-6">
                                    {translate('Exif Model')}:
                                </div>
                                <div class="col-sm-6">
                                    {$item.exif.Model}
                                </div>
                            </div>
                            {/if}
    					</div>
    				</div>
                </div>
                