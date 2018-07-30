<!DOCTYPE html>
<html lang="{$LANGUAGE}">
<head>
    {get_page_headers()}
</head>
<body>
    {include file='backend_nav_top.tpl'}
    <div class="container-fluid h-100">
        <div class="row h-100">
            {include file='backend_nav_sidebar.tpl'}
            <main role="main" class="col">
                <nav aria-label="breadcrumb" role="navigation" class="" id="bsBreadcrumb" aria-labelledby="breadcrumb-header">
                  <header aria-hidden="true"><strong id="breadcrumb-header">{translate('You are here')}:</strong></header>
                  {cat_breadcrumb(show_current: true, link_current: true, top_ul_class: "breadcrumb", ul_class: "breadcrumb", iconclass: "fa fa-fw fa-", before: "<span class='fa fa-fw fa-home'></span>")}
                </nav>
