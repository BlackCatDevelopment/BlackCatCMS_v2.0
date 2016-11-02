<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$meta.LANGUAGE}" lang="{$meta.LANGUAGE}">
<head>
    <meta charset="{$meta.CHARSET}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{translate('Login')}</title>
    <link href="{$CAT_URL}/modules/lib_bootstrap/vendor/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$CAT_URL}/modules/lib_bootstrap/vendor/css/font-awesome.min.css" rel="stylesheet">
    <link href="{$CAT_URL}/templates/backstrap/css/default/login.css" rel="stylesheet">
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
                                <input type="text" class="form-control" required="required" name="{$USERNAME_FIELDNAME}" id="{$USERNAME_FIELDNAME}" placeholder="{translate('Your username')}" autofocus="autofocus" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-key fa-fw"></span></div>
                                <input type="password" class="form-control" required="required" name="{$PASSWORD_FIELDNAME}" id="{$PASSWORD_FIELDNAME}" placeholder="{translate('Your password')}" />
                            </div>
                        </div>
                        {if $ENABLE_TFA}
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
    <script src="{$CAT_URL}/modules/lib_jquery/jquery-core/jquery-core.min.js"></script>
    <script src="{$CAT_URL}/modules/lib_bootstrap/vendor/js/bootstrap.min.js"></script>

    <script type="text/javascript">
    //<![CDATA[
    $('input#{$USERNAME_FIELDNAME}').on('focusout', function() {
        $.ajax({
            type:     'POST',
            context:  $(this),
            url:      '{$CAT_ADMIN_URL}/tfa',
            dataType: 'json',
            data:     {
                user: this.value
            },
            cache:    false,
            success:  function(data,textStatus,jqXHR)
            {
                console.log(data);
                if(data.message === true) {
                    $('div#tfagroup').show('slow');
                } else {
                    $('div#tfagroup').hide('slow');
                }
            }
        });
    });
    $('.btn-primary').click( function(e) {
        e.preventDefault();

        // reset error message
        $('div#login-error p').html('');
        $('div#login-error').hide();

        var username_fieldname    = $('form').find('input[name=username_fieldname]').val(),
            password_fieldname    = $('form').find('input[name=password_fieldname]').val(),
            token_fieldname     = $('form').find('input[name=token_fieldname]').val(),
            dates                = {
                'username_fieldname': username_fieldname,
                'password_fieldname': password_fieldname,
                'token_fieldname'   : token_fieldname,
            };
        dates[username_fieldname]    = $('input#' + username_fieldname).val();
        dates[password_fieldname]    = $('input#' + password_fieldname).val();
        dates[token_fieldname]        = $('input#' + token_fieldname).val();

        $.ajax({
            type:        'POST',
            context:    $(this),
            url:        '{$CAT_ADMIN_URL}/authenticate/',
            dataType:    'json',
            data:        dates,
            cache:        false,
            success:    function( data, textStatus, jqXHR  )
            {
                if ( data.success === true )
                {
                    window.location        = data.url
                }
                else {
                    
                    $('div#login-error p').html(data.message);
                    $('div#login-error').show();
                    $('input[name=' + password_fieldname + ']').val('').focus();
                }
            },
            error:        function( jqXHR, textStatus, errorThrown )
            {
                alert(textStatus + ': ' + jqXHR.responseText );
            }
        });
    });
    //]]>
    </script>
</body>
</html>
