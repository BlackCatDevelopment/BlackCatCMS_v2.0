      </main>
    </div>
  </div>

  <footer class="navbar fixed-bottom navbar-expand navbar-dark bg-dark">
    <div class="navbar-nav flex-row ml-md-auto d-none d-md-flex">{$WEBSITE_TITLE}</div>
    <div id="sessiontimer" class="navbar-nav flex-row ml-md-auto d-none d-md-flex text-muted">{translate('Remaining session time')}: <span id="sesstime">{$SESSION_TIME}</span></div>
  </footer>

  {include(file='backend_modal.tpl' modal_id='bsDialog' modal_title='', modal_text='', modal_savebtn='1')}
  {include(file='backend_modal.tpl' modal_id='add_page_modal' modal_title='', modal_text='$add_page_form', modal_savebtn='1')}

  {* Session timeout dialog *}
  <div class="modal fade dark" id="bsSessionTimedOutDialog" tabindex="-1" role="dialog" aria-labelledby="bsSessionTimedOutDialogLabel" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="bsSessionTimedOutDialogLabell"><i class="fa fa-fw fa-warning text-danger"></i>{translate('Session timed out!')}</h1>
        </div>
        <div class="modal-body">
          <h2>{translate('Do you wish to login again?')}</h2>
          <div>
            <form method="post">
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-addon"><span class="fa fa-user fa-fw"></span></div>
                  <input type="text" class="form-control u" required="required" name="{$USERNAME_FIELDNAME}" id="{$USERNAME_FIELDNAME}" placeholder="{translate('Your username')}" autofocus="autofocus" />
                </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-addon"><span class="fa fa-key fa-fw"></span></div>
                  <input type="password" class="form-control p" required="required" name="{$PASSWORD_FIELDNAME}" id="{$PASSWORD_FIELDNAME}" placeholder="{translate('Your password')}" />
                </div>
              </div>
            </form>
            </div>
            <div class="alert alert-danger alert-dismissible" role="alert" id="login-error" style="display:none;">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <p></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" id="bsSessionToFE" title="{translate('Close the Backend and open Homepage (Frontend)')}">{translate('Close Backend')}</button>
            <button type="button" class="btn btn-primary" id="bsSessionLogin" title="{translate('Login with the given credentials and stay on current page')}">{translate('Login')}</button>
        </div>
      </div>
    </div>
  </div>
  {get_page_footers()}
</body>
</html>