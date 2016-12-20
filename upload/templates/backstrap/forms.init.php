<?php

\wblib\wbFormsElementForm::setClass('form-horizontal');
\wblib\wbFormsElement::setClass('form-control');
\wblib\wbFormsElementLabel::setTemplate('<label class="col-sm-2 control-label"%for%%style%>%label%</label>');
\wblib\wbFormsElement::setTemplate(
    '<div class="form-group">
      %label%
      <div class="col-sm-10">
        <input%type%%name%%id%%class%%style%%title%%value%%required%%aria-required%%pattern%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />
        <span class="help-block">%after%</span>
      </div>
    </div>'
);
\wblib\wbFormsElementSelect::setTemplate(
    '<div class="form-group">
	  %label%
      <div class="col-sm-10">
        <select%name%%id%%class%%style%%title%%multiple%%tabindex%%accesskey%%disabled%%readonly%%required%%aria-required%%onblur%%onchange%%onclick%%onfocus%%onselect%>%options%</select> %after%
      </div>
    </div>'
);
\wblib\wbFormsElementButton::setClass('btn btn-primary');
