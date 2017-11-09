    <div id="JSMgr">
        <h2>{translate('JavaScript & jQuery Plugin Manager')}</h2>
        {if is_array($plugins)}
        <table>
            <thead>
                <tr>
                    <th colspan="3">{translate('Installed plugins/JavaScripts')}</th>
                    <th style="text-align:right"><button id="JSMgrAdd"><i class="fa fa-fw fa-plus-circle"></i> {translate('Upload')}</button></th>
                </tr>
                <tr>
                    <th>{translate('Name')}</th>
                    <th>{translate('Version')}</th>
                    <th>{translate('Readme')}</th>
                    <th>{translate('jQuery')}</th>
                </tr>
            </thead>
            <tbody>
        {foreach $plugins p}{$dir = $p.directory}
                <tr>
                    <td>{$p.name}</td>
                    <td>{$p.version}</td>
                    <td>{if $readmes.$dir} <a href="{$readmes.$dir}" class="readmedlg"><img src="{$CAT_URL}/modules/tool_JSMgr/images/info.png" alt="info.png" title="{translate('Open Readme')}" /></a>{/if}</td>
                    <td>{if $p.jquery == 'Y'}<span class="fa fa-fw fa-check"></span>{/if}</td>
                </tr>
        {/foreach}
            </tbody>
        </table>
        {/if}
        <div class="dialog" style="display:none"></div>
        <div id="JSMgrForm" style="display:none">
            <form method="post" action="{$CAT_ADMIN_URL}/admintools/tool/JSMgr" enctype="multipart/form-data" name="upload">
                <input type="hidden" name="upload" value="1" />
                    <input type="file" name="userfile" />
                    <input type="submit" class="submit button" value="{translate('Upload')}" name="submit" /><br /><br />
                    <div>
                        <strong>{translate('Please note')}:</strong>
                        <ul>
                            <li>{translate('Upload ZIPs only')}</li>
                            <li>{translate('We will try to read the version information from the .js files, but there is no guarantee that this will work.')}</li>
                            <li>{translate('We will try to figure out if the uploaded plugin is for jQuery or not, but there is no guarantee that this will work.')}</li>
                            <li>{translate('Of course, we cannot guarantee that plugins uploaded here will work at all.')}</li>
                        </ul>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
{literal}
    <script charset=windows-1250 type="text/javascript">
        if(typeof jQuery != 'undefined') {
            jQuery(document).ready(function($) {
                $("div.dialog").dialog({
                    width: 960,
                    hide: 'clip',
                    show: 'blind',
                    autoOpen: false,
                    modal: true
                });
                $('a.readmedlg').click(function(e) {
                    e.stopPropagation();
                    var url = $(this).attr('href');
                    $('div.dialog').load(url, function() {
                        $('div.dialog').dialog('open');
                    });
                    return false;
                });
                $('button#JSMgrAdd').unbind('click').on('click',function(e) {
                    $('div.dialog').dialog('option','title','Upload');
                    $('div.dialog').html($('div#JSMgrForm').html()).dialog('open');
                });
            });
        }
    </script>
{/literal}