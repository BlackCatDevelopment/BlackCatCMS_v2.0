<div class="modal fade" id="bsModalAboutVariants" tabindex="-1" role="dialog" aria-labelledby="bsModalAboutVariantsLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="bsModalAboutVariants">{translate('About module variants')}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="info">
            <p>{translate('Variants allow the selection of a specific presentation, possibly combined with specific settings.')}</p>
            <p>{translate('For example, WYSIWYG sections have variants for multiple columns per row (shown next to each other), accordion, tabs, etc.')}</p>
            <p>{translate('Please refer to the documentation of each module to learn more about the available variants.')}</p>
        </div><br /><br />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">{translate('Close')}</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
//<![CDATA[
   $('.bsAbout').on('click',function(e){
        $('#bsModalAboutVariants').modal('show');
   });
//]]>
</script>