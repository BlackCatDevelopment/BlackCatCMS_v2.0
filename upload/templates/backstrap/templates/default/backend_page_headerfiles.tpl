    <div class="alert alert-info">
    {translate('You can manage Javascript- and CSS-Files resp. jQuery plugins to be loaded into the page header here.')}<br />
    {translate('Please note that there is a bunch of files that is loaded automatically, so there\'s no need to add them here.')}<br />
    {translate('These settings are page based, to manage global settings, goto Settings -> Header files.')}
    </div><br /><br />

    <div class="row">
        <div class="col">
            <div class="form-check">
              <label class="form-check-label custom-control custom-checkbox">
                <input class="form-check-input custom-control-input" type="checkbox" value="" />
                <span class="custom-control-indicator"></span>
                {translate('Use jQuery')}
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label custom-control custom-checkbox">
                <input class="form-check-input custom-control-input" type="checkbox" value="" />
                <span class="custom-control-indicator"></span>
                {translate('Use jQuery UI')}
              </label>
            </div>
        </div>
        <div class="col">
            <button class="btn btn-primary">{translate('Add jQuery Plugin')}</button>
            <button class="btn btn-primary" id="bsAddCSS">{translate('Add Template CSS')}</button>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-striped table-sm">
                <thead class="thead-dark">
                    <tr><th colspan="2" scope="col">CSS</th></tr>
{if is_array($files.css) && count($files.css)}
                    <tr>
                        <th scope="col">{translate('File')}</th>
                        <th scope="col">{translate('Position')}</th>
                    </tr>
                </thead>
                <tbody>
{foreach $files.css item}
                    <tr>
                        <td>{$item.file}</td>
                        <td>{$item.pos}</td>
                    </tr>
{/foreach}
{else}
                </thead>
                <tr><td colspan="2">No files</td></tr>
{/if}
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table">
                <thead class="thead-dark">
                    <tr><th colspan="2" scope="col">JavaScripts</th></tr>
{if is_array($files.js) && count($files.js)}
                    <tr>
                        <th scope="col">{translate('File')}</th>
                        <th scope="col">{translate('Position')}</th>
                    </tr>
                </thead>
                <tbody>
{foreach $files.js item}
                    <tr>
                        <td>{$item.file}</td>
                        <td>{$item.pos}</td>
                    </tr>
{/foreach}
{else}
                </thead>
                <tr><td colspan="2">No files</td></tr>
{/if}
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:none">
        <div id="bsCSSFiles">
            {if is_array($tplcss)}{foreach $tplcss file}
                <div class="form-check">
                    <label class="form-check-label custom-control custom-checkbox">
                        <input class="form-check-input custom-control-input" type="checkbox" value="" />
                        <span class="custom-control-indicator"></span>
                        {$file}
                    </label>
                </div>
            {/foreach}{/if}
        </div>
    </div>

{include(file='backend_modal.tpl' modal_id='tplcss' modal_title='Add Template CSS', modal_text='', modal_savebtn='1')}

