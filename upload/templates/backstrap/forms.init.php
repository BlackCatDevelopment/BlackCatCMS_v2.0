<?php

\wblib\wbFormsJQuery::set('load_ui_theme',false);
\wblib\wbFormsElementForm::setClass('form-horizontal');
\wblib\wbFormsElement::setClass('form-control');
\wblib\wbFormsElementSubmit::setClass('btn btn-primary');
\wblib\wbFormsElementButton::setClass('btn btn-secondary');
\wblib\wbFormsElementLabel::setTemplate('<label class="col-sm-4 control-label"%for%%style%>%label%</label>');
\wblib\wbFormsElement::setTemplate(
    '<div class="form-group row">
      %label%
      <div class="col-sm-1">%is_required%</div>
      <div class="col-sm-7">
        <input%type%%name%%id%%class%%style%%title%%value%%required%%aria-required%%pattern%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />
        <span class="help-block">%after%</span>
      </div>
    </div>'
);
\wblib\wbFormsElementSelect::setTemplate(
    '<div class="form-group">
	  %label%
      <div class="col-sm-1">%is_required%</div>
      <div class="col-sm-7">
        <select%name%%id%%class%%style%%title%%multiple%%tabindex%%accesskey%%disabled%%readonly%%required%%aria-required%%onblur%%onchange%%onclick%%onfocus%%onselect%>%options%</select>
        <span class="help-block">%after%</span>
      </div>
    </div>'
);
\wblib\wbFormsElementCheckbox::setTemplate(
    '<div class="form-group">
      %label%
      <div class="col-sm-1">%is_required%</div>
      <div class="col-sm-7">
        <input%type%%name%%id%%class%%checked%%style%%title%%value%%required%%aria-required%%pattern%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />
        <span class="help-block">%after%</span>
      </div>
    </div>'
);
\wblib\wbForms::set('required_span','<i class="fa fa-fw fa-asterisk text-danger"></i>');