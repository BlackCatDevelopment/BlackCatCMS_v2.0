<!DOCTYPE html>
<html lang="{$meta.LANGUAGE}">
<head>
    {get_page_headers("backend",true,"{$SECTION}")}
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="apple-touch-icon" href="/bootstrap/img/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/bootstrap/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/bootstrap/img/apple-touch-icon-114x114.png">
</head>
<body class="fuelux">
{include file='backend_nav_top.tpl'}
  <div class="container-fluid">
    <div class="row row-offcanvas row-offcanvas-left">
{include file='backend_nav_sidebar.tpl'}
      <div class="col-sm-9 col-md-10 main">
        <p class="visible-xs">
          <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas"><i class="fa fa-fw fa-caret-right"></i></button>
        </p>