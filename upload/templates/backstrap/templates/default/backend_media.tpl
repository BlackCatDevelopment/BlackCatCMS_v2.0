HUHU



<div class="row">
    <div class="col-lg-3">
        <ul class="tree tree-folder-select" role="tree" id="myTree">
            <li class="tree-branch hide" data-template="treebranch" role="treeitem" aria-expanded="false">
                <div class="tree-branch-header">
                    <button type="button" class="glyphicon icon-caret glyphicon-play"><span class="sr-only">Open</span></button>
                    <button type="button" class="tree-branch-name">
                        <span class="glyphicon icon-folder glyphicon-folder-close"></span>
                        <span class="tree-label"></span>
                    </button>
                </div>
                <ul class="tree-branch-children" role="group"></ul>
                <div class="tree-loader" role="alert">Loading...</div>
            </li>
            <li class="tree-item hide" data-template="treeitem" role="treeitem">
                <button type="button" class="tree-item-name">
                    <span class="glyphicon icon-item fueluxicon-bullet"></span>
                    <span class="tree-label"></span>
                </button>
            </li>
        </ul>
    </div>
</div>
<script type="text/javascript">
//<![CDATA[
    function dynamicDataSource(openedParentData, callback) {
        var childNodesArray = [];
        // call API, posting options
        $.ajax({
            type   : 'json',
            url    : '{$CAT_ADMIN_URL}/media/list',
            data   : openedParentData,  // first call with be an empty object
            success: function(data, status) {
                console.log(data);
            }
        });
    }
    dynamicDataSource();
    //$('#myTree').tree({ dataSource: dynamicDataSource });
//]]>
</script>