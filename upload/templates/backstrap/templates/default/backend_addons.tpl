    <span id="bsFilterSelect" class="detach">
      <label for="filter">{translate('Filter by type')}</label>
      <select name="filter" id="filter">
        <option value="">{translate('All')}</option>
        <option value="pages">{translate('Page modules')}</option>
        <option value="librarys">{translate('Libraries')}</option>
        <option value="tools">{translate('Admin-Tools')}</option>
        <option value="languages">{translate('Languages')}</option>
        <option value="templates">{translate('Templates')}</option>
      </select>
    </span>

{include file='backend_addons_tabs.tpl'}
{include file='backend_addons_table.tpl'}

{* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_javascript/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>