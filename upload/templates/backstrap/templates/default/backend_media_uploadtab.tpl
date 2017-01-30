            <div role="tabpanel" class="tab-pane" id="upload">
                <span class="btn btn-success fileinput-button">
                    <i class="fa fa-fw fa-plus"></i>
                    <span>{translate('Add files...')}</span>
                    <input id="fileupload" type="file" name="files[]" multiple="multiple" />
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="fa fa-fw fa-upload"></i>
                    <span>{translate('Start upload')}</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="fa fa-fw fa-times-circle"></i>
                    <span>{translate('Cancel upload')}</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="fa fa-fw fa-trash"></i>
                    <span>{translate('Delete')}</span>
                </button><br /><br />

                <div id="progress" class="progress">
                    <div class="progress-bar progress-bar-success"></div>
                </div>

                <table id="bsUploadFiles" class="table" role="presentation">
                    <tbody class="files">
                        
                    </tbody>
                </table>
            </div>
