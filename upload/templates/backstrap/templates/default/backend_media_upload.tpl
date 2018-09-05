{include file="backend_media_tabs.tpl"}
<br /><br />

<form id="bsFileupload" action="{$CAT_ADMIN_URL}/media/upload" method="POST" enctype="multipart/form-data" class="">
    <div class="row fileupload-buttonbar pull-right">
        <div class="col">
             <span class="btn btn-success fileinput-button">
                <i class="fa fa-fw fa-plus"></i>
                <span>{translate('Add files...')}</span>
                <input id="fileupload" type="file" name="files[]" multiple="multiple" />
            </span>
            <button type="submit" class="btn btn-primary start disabled">
                <i class="fa fa-fw fa-upload"></i>
                <span>{translate('Start upload')}</span>
            </button>
            <button type="reset" class="btn btn-warning cancel disabled">
                <i class="fa fa-fw fa-times-circle"></i>
                <span>{translate('Cancel upload')}</span>
            </button>
            <button type="button" class="btn btn-danger delete disabled">
                <i class="fa fa-fw fa-trash"></i>
                <span>{translate('Delete')}</span>
            </button>
        </div>
    </div><br /><br />

    <!-- The global progress state -->
    <div class="fileupload-progress">
        <!-- The global progress bar -->
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
        </div>
        <!-- The extended global progress state -->
        <div class="progress-extended">&nbsp;</div>
    </div>

    <!-- The table listing the files available for upload/download -->
    <table role="presentation" class="table table-striped" id="bsUploadFiles">
        <tbody class="files"></tbody>
    </table>
</form>