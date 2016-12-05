<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$LANGUAGE}" lang="{$LANGUAGE}">
<head>
<?php
    CAT_Helper_Page::addCSS('modules/lib_bootstrap/vendor/css/default/bootstrap.min.css');
    CAT_Helper_Page::addCSS('modules/lib_bootstrap/vendor/css/font-awesome.min.css');
    CAT_Helper_Page::addCSS('templates/backstrap/css/default/login.css');
    CAT_Helper_Page::addJS('modules/lib_jquery/jquery-core/jquery-core.min.js','footer');
    CAT_Helper_Page::addJS('modules/lib_bootstrap/vendor/js/bootstrap.min.js','footer');
    CAT_Helper_Page::addJS('templates/backstrap/js/login.js','footer');
    CAT_Helper_Page::addMeta(array('charset' => (defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : "utf-8")));
    CAT_Helper_Page::addMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
    CAT_Helper_Page::addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
    //CAT_Helper_Page::addMeta(array('name' => 'description', 'content' => 'BlackCat CMS - '.$pg->lang()->translate('Administration')));
?>
    {get_page_headers(false,true)}
    <title>{translate('Login')}</title>
</head>

<body class="login-screen-bg">
    <div class="container">
        <div class="row vertical-center-row">
            <div class="col-md-4 col-center-block login-widget">
                <h1 class="text-center"><span class="fa fa-lock"></span> {translate('Login')}</h1>
                <form name="login" action="{$CAT_ADMIN_URL}/authenticate" method="post">
                    <input type="hidden" name="username_fieldname" value="{$USERNAME_FIELDNAME}" />
                    <input type="hidden" name="password_fieldname" value="{$PASSWORD_FIELDNAME}" />
                    <input type="hidden" name="token_fieldname" value="{$TOKEN_FIELDNAME}" />
                    <div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-user fa-fw"></span></div>
                                <input type="text" class="form-control field1" required="required" name="{$USERNAME_FIELDNAME}" id="{$USERNAME_FIELDNAME}" placeholder="{translate('Your username')}" autofocus="autofocus" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-key fa-fw"></span></div>
                                <input type="password" class="form-control" required="required" name="{$PASSWORD_FIELDNAME}" id="{$PASSWORD_FIELDNAME}" placeholder="{translate('Your password')}" />
                            </div>
                        </div>
                        {if cat_get('enable_tfa'}}
                        <div class="form-group" id="tfagroup" style="display:none;">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-fw fa-lock"></span></div>
                                <input type="text" class="form-control" name="{$TOKEN_FIELDNAME}" id="{$TOKEN_FIELDNAME}" placeholder="{translate('Your OTP code (PIN)')}" aria-describedby="{$TOKEN_FIELDNAME}helpBlock" />
                            </div>
                            <span id="{$TOKEN_FIELDNAME}helpBlock" class="help-block">{translate('If you have Two Step Authentication enabled, you will have to enter your one time password here. Leave this empty otherwise.')}</span>
                        </div>
                        {/if}
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">{translate('Login')}</button>
                        </div>
                    </div>
                </form>
                <div class="alert alert-danger alert-dismissible" role="alert" id="login-error" style="display:none;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <p></p>
                </div>
            </div>
        </div>
    </div>
{get_page_footers("backend",true,true)}

</body>
</html>
