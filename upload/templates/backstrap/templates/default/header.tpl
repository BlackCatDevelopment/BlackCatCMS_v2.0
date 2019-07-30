<!DOCTYPE html>
<html lang="{$LANGUAGE}">
<head>
    {get_page_headers()}
    {* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_javascript/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>
</head>
<body class="bg-dark">
    {include file='backend_nav_top.tpl'}
    <div class="container-fluid h-100">
        <div class="row h-100">
            {include file='backend_nav_sidebar.tpl'}
            <main role="main" class="col bg-white">
                <nav aria-label="breadcrumb" role="navigation" class="" id="bsBreadcrumb">
                  {menu(2)}
                </nav>
