{include file='backend_addons_tabs.tpl'}

{$form}

{* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_javascript/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>