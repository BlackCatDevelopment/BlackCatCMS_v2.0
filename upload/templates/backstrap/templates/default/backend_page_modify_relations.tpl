        <div class="alert alert-info">
        {translate('You can link any page to other pages in different languages that have the same content.')}
        {translate('Use {language_menu()} in your frontend template to show links to the pages listed below.')}
        </div>
        {if $linked}
        <table class="table">
          <thead>
            <tr>
              <th></th>
              <th>{translate('Language')}</th>
              <th>{translate('Linked page')}</th>
              <th>{translate('Last modified')}</th>
            </tr>
          </thead>
          <tbody>
          {foreach $linked item}
          <tr>
            <td><span class="fa fa-fw fa-chain-broken" data-id="{$item.page_id}"></span></td>
            <td>{$item.lang}</td>
            <td>{$item.menu_title} (ID: {$item.page_id})</td>
            <td>{cat_format_date($item.modified_when)}</td>
          </tr>
          {/foreach}
          </tbody>
        </table>
        {else}
        <div class="alert alert-warning">{translate('There are no linked pages yet')}</div>
        {/if}
        
        <form action="{$CAT_ADMIN_URL}/page/save" id="bsAddPageRelation">
          <input type="hidden" name="page_id" id="page_id" value="{$page_id}" />
          <fieldset>
            <legend>{translate('Set language relation')}</legend>
            <div class="form-group row">
              <label for="relation_lang" class="col-sm-2 col-form-label">{translate('Target language')}</label>
              <div class="col-sm-10">
                <select class="form-control" id="relation_lang" name="relation_lang">
                {foreach $langs tag lang}
                  <option value="{$tag}"{if $tag==$page.language} disabled="disabled"{/if}>{$lang}</option>
                {/foreach}
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-sm-2 col-form-label">{translate('Page')}</label>
              <div class="col-sm-10">
                <select name="linked_page" id="linked_page" title="" aria-describedby="linked_page_helpText" class="form-control">
                  <option value="">{translate('[none]')}</option>
                {foreach $pages p}{* if $p.language != $_.page.language *}
                  <option value="{$p.page_id}" data-lang="{$p.language}">{$p.menu_title} ({$p.language})</option>
                {* /if *}{/foreach}
                </select>
              </div>
            </div>
            <button class="btn btn-primary" id="bsSaveLangRelation" style="display:none">{translate('Save')}</button>
          </fieldset>
        </form>
        <div class="alert alert-info" id="bsNoPagesInfo" style="display:none">
          {translate('There are no pages in the selected target language available.')}
        </div>
