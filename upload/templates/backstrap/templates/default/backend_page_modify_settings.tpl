        <ul class="nav nav-tabs inner" role="tablist">{* Tabs *}
          <li role="presentation" class="active"><a href="#meta" aria-controls="meta" role="tab" data-toggle="tab">{translate('Meta')} / SEO</a></li>
          <li role="presentation"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{translate('General')}</a></li>
          <li role="presentation"><a href="#header" aria-controls="header" role="tab" data-toggle="tab">{translate('Header files')}</a></li>
        </ul>

        <div class="tab-content">{* INNER Tab panes *}

          {* START meta tab *}
          <div role="tabpanel" class="tab-pane active" id="meta">
            {cat_form('be_page_settings')}
          </div>
          {* END meta tab *}

          {* START general tab *}
          <div role="tabpanel" class="tab-pane" id="general">
            {cat_form('be_page_general')}
          </div>
          {* END general tab *}

          {* START header tab *}
          <div role="tabpanel" class="tab-pane" id="header">
            {if $headerfiles}
            <table class="table">
              <thead>
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              {foreach $headerfiles item}
              {if $item.js}
                <tr><th colspan="3">{translate('Javascript')}</th></tr>
                {foreach $item.js js}
                <tr><td><span class="fa fa-fw fa-chain-broken"></span></td><td>{$js}</td><td></td></tr>
                {/foreach}
              {/if}
              {if $item.css}
                <tr><th colspan="3">{translate('CSS')}</th></tr>
                {foreach $item.css css}
                <tr><td><span class="fa fa-fw fa-chain-broken"></span></td><td>{$css}</td><td></td></tr>
                {/foreach}
              {/if}
              {/foreach}
              </tbody>
            </table>
            {else}
            <div class="alert alert-info">{translate('Currently, no extra files are defined for this page.')}</div>
            {/if}

            <button id="bsAddPlugin" class="btn btn-primary">
                {translate('Add jQuery Plugin')}
            </button><br /><br />

            {translate('You can manage Javascript- and CSS-Files resp. jQuery plugins to be loaded into the page header here.')}<br />
            {translate('Please note that there is a bunch of files that is loaded automatically, so there\'s no need to add them here.')}<br />
            {translate('These settings are page based, to manage global settings, goto Settings -> Header files.')}<br /><br />
            {cat_form('be_page_headerfiles')}
          </div>
          {* END header tab *}

        </div>{* end INNER *}