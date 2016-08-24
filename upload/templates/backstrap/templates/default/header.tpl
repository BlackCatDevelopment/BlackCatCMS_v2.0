<!DOCTYPE html>
<html lang="{$meta.LANGUAGE}">
<head>
    {get_page_headers("backend",true,"{$SECTION}")}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="fuelux">
    <div id="wrapper">
        {include file='backend_pagetree.tpl'}
        {include 'nav.tpl'}
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">{translate($SECTION)}</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="infopanel alert alert-danger alert-dismissible fade in" role="alert" style="display:none">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <span id="message"></span>
                    </div>
                </div>
            </div>
