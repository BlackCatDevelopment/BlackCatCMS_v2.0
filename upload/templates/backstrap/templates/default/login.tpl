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
                    <div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-user fa-fw"></span></div>
                                <input type="text" class="form-control" name="{$USERNAME_FIELDNAME}" id="{$USERNAME_FIELDNAME}" placeholder="{translate('Your username')}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><span class="fa fa-key fa-fw"></span></div>
                                <input type="password" class="form-control" name="{$PASSWORD_FIELDNAME}" id="{$PASSWORD_FIELDNAME}" placeholder="******">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">{translate('Login')}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{$CAT_URL}/modules/lib_jquery/jquery-core/jquery-core.min.js" type="text/javascript"></script>
    
    <script type="text/javascript">
    //<![CDATA[
    $('.btn-primary').click( function(e) {
		e.preventDefault();
        var username_fieldname	= $('form').find('input[name=username_fieldname]').val(),
			password_fieldname	= $('form').find('input[name=password_fieldname]').val(),
			dates				= {
				'username_fieldname': username_fieldname,
				'password_fieldname': password_fieldname
			};
		dates[username_fieldname]	= $('form').find('input[name=' + username_fieldname + ']').val();
		dates[password_fieldname]	= $('form').find('input[name=' + password_fieldname + ']').val();
        $.ajax(
		{
			type:		'POST',
			context:	$(this),
			url:		'{$CAT_ADMIN_URL}/authenticate/',
			dataType:	'json',
			data:		dates,
			cache:		false,
			success:	function( data, textStatus, jqXHR  )
			{
				if ( data.success === true )
				{
					window.location		= data.url
				}
				else {
					$('input[name=' + password_fieldname + ']').val('').focus();
				}
			},
			error:		function( jqXHR, textStatus, errorThrown )
			{
				alert(textStatus + ': ' + jqXHR.responseText );
			}
		});
    });
    //]]>
    </script>
</body>
</html>
