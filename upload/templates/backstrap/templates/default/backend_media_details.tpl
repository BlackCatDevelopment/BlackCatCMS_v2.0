            <td colspan="3">
                <div id="" class="gridder-content card">
                    <div class="row">
    				    <div class="col-sm-6">
                            {if $is_image}
                            <img src="{$CAT_URL}/{$url}" alt="{translate('Preview')}" class="img-responsive" />
                            {else}
                                {if $video && ! $mime_type == 'video/x-flv'}
                                    <video width="320" height="240" controls>
                                        <source src="{$CAT_URL}" type="">
                                        {translate('Your browser does not support the video tag')}
                                    </video>
                                {else}
                                    <div class="fa fa-fw fa-file-movie-o"></div><br />
                                    <small class="text-info">{translate('Sorry, no preview available for this mime type!')}</small>
                                {/if}
                            {/if}
                        </div>
    					<div class="col-sm-6">
                            <strong data-field="filename">{$filename}</strong>
                            {if user_has_perm('media_delete')}
                            <a href="" class="delete" title="{translate('Delete item')}" data-url="{$CAT_ADMIN_URL}/media/delete" data-id=""><span class="fa fa-fw fa-trash text-danger"></span></a>
                            {else}<span class="fa fa-fw"></span>{/if}
                            <br /><br />
                            {if $copyright}
                            <div class="row">
                                <div class="col-sm-6"><strong>{translate('Copyright')}:</strong></div>
                                <div class="col-sm-6"><strong data-field="copyright">{$copyright}</strong></div>
                            </div>{/if}
                            <div class="row">
                                <div class="col-sm-6">{translate('Type')}:</div>
                                <div class="col-sm-6" data-field="mime_type">{$mime_type}</div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">{translate('Size')}:</div>
                                <div class="col-sm-6" data-field="hfilesize">{$hfilesize}</div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">{translate('Last modified')}:</div>
                                <div class="col-sm-6" data-field="moddate">{$moddate}</div>
                            </div>
                            {if $encoding}
                            <div class="row">
                                <div class="col-sm-6">{translate('Encoding')}:</div>
                                <div class="col-sm-6" data-field="encoding">{$encoding}</div>
                            </div>{/if}
                            {if $bits_per_sample}
                            <div class="row">
                                <div class="col-sm-6">{translate('Bits per sample')}:</div>
                                <div class="col-sm-6">{$bits_per_sample} {translate('Bit')}</div>
                            </div>{/if}
                            {if $resolution_y}
                            <div class="row">
                                <div class="col-sm-6">{translate('Resolution Y')}:</div>
                                <div class="col-sm-6" data-field="resolution_y">{$resolution_y}</div>
                            </div>{/if}
                            {if $resolution_x}
                            <div class="row">
                                <div class="col-sm-6">{translate('Resolution X')}:</div>
                                <div class="col-sm-6" data-field="resolution_x">{$resolution_x}</div>
                            </div>{/if}
                            {if $Make}
                            <div class="row">
                                <div class="col-sm-6">{translate('Exif Make')}:</div>
                                <div class="col-sm-6" data-field="Make">{$Make}</div>
                            </div>{/if}
                            {if $exif.Model}
                            <div class="row">
                                <div class="col-sm-6">{translate('Exif Model')}:</div>
                                <div class="col-sm-6" data-field="Model"></div>
                            </div>{/if}
                        </div>
    				</div>
                </div>
            </td>