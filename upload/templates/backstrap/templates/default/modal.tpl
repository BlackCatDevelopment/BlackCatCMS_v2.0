<div class="modal fade" id="{$modal_id}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">{translate($modal_title)}</h4>
      </div>
      <div class="modal-body">
        {translate($modal_text)}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{translate('Cancel')}</button>
        <button type="button" class="btn btn-primary">{translate('Save changes')}</button>
      </div>
    </div>
  </div>
</div>