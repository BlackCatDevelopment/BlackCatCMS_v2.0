            <div role="tabpanel" class="tab-pane" id="upload">
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
                </button><br /><br />

                <div id="progress" class="progress" hidden>
                    <div class="progress-bar progress-bar-success"></div>
                </div>

                <table id="bsUploadFiles" class="table" role="presentation">
                    <thead>
                        <tr>
                            <th>{translate('Preview')}</th>
                            <th>{translate('Filename')}</th>
                            <th>{translate('Progress')}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="files">
                        
                    </tbody>
                </table>
            </div>
