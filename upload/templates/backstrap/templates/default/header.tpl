<!DOCTYPE html>
<html lang="{$LANGUAGE}">
<head>
    {get_page_headers()}
</head>
<body class="bg-dark">
    {include file='backend_nav_top.tpl'}
    <div class="container-fluid h-100">
        <div class="row h-100">
            {include file='backend_nav_sidebar.tpl'}
            <main role="main" class="col bg-white">
                <nav aria-label="breadcrumb" role="navigation" class="" id="bsBreadcrumb" aria-labelledby="breadcrumb-header">
                  <header aria-hidden="true"><strong id="breadcrumb-header">{translate('You are here')}:</strong></header>
                  {menu(2)}
                </nav>
