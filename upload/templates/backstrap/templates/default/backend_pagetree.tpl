    <nav id="tree">
        <div id="switch-wrapper">
            <div class="onoffswitch">
                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch">
                <label class="onoffswitch-label" for="myonoffswitch" title="{translate('Keep open')}"></label>
            </div>
        </div>
        <a href="#" id="pagetree_open_all"><span class="fa fa-fw fa-angle-down" title="{translate('Open all')}"></span></a>
        <a href="#" id="pagetree_open_all"><span class="fa fa-fw fa-angle-up" title="{translate('Close all')}"></span></a>
		<ul id="treeData" style="display:none;">
		</ul>
	</nav>

{literal}
    <script type="text/javascript">
		$(function(){
            var convert = function(val)
            {
                //console.log('convert val',val);
                var converted = new Array;
                converted.title = val.menu_title;
                converted.visibility = val.visibility;
                if(val.has_children === true) {
                    converted.children = new Array();
                    for(i=0;i<val.children.length;i++) {
                        converted.children.push(convert(val.children[i]));
                    }
                }
                return converted;
            };

            $('a#pagetree_open_all').on('click',function(e) {
                e.preventDefault();
                $("#tree").fancytree("getRootNode").visit(function(node){
                    node.setExpanded(true);
                });
            });

            $('a#pagetree_close_all').on('click',function(e) {
                e.preventDefault();
                $("#tree").fancytree("getRootNode").visit(function(node){
                    node.setExpanded(false);
                });
            });

			// using default options
			$("#tree").fancytree({
                activeVisible: true, // Make sure, active nodes are visible (expanded).
                aria: false, // Enable WAI-ARIA support.
                autoActivate: true, // Automatically activate a node when it is focused (using keys).
                autoCollapse: false, // Automatically collapse all siblings, when a node is expanded.
                autoScroll: false, // Automatically scroll nodes into visible area.
                clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
                checkbox: false, // Show checkboxes.
                debugLevel: 0, // 0:quiet, 1:normal, 2:debug
                disabled: false, // Disable control
                focusOnSelect: true, // Set focus when node is checked by a mouse click
                escapeTitles: false, // Escape `node.title` content for display
                generateIds: false, // Generate id attributes like <span id='fancytree-id-KEY'>
                idPrefix: "ft_", // Used to generate node id´s like <span id='fancytree-id-<key>'>.
                keyboard: true, // Support keyboard navigation.
                keyPathSeparator: "/", // Used by node.getKeyPath() and tree.loadKeyPath().
                minExpandLevel: 1, // 1: root node is not collapsible
                quicksearch: true, // Navigate to next node by typing the first letters.
                selectMode: 2, // 1:single, 2:multi, 3:multi-hier
                tabindex: "0", // Whole tree behaves as one single control
                titlesTabbable: true, // Node titles can receive keyboard focus

click: function(e, data) {
                   data.node.toggleExpanded();
                },

                source: {
{/literal}
                    url: "{$CAT_ADMIN_URL}/pages/list"
{literal}
                },
                icon: function(event, data) {
                    return "fa fa-fw fa-file-o blue";
                },
                postProcess: function(event, data) { // convert data for fancytree
                    var converted = new Array;
                    jQuery.each(data.response, function(i, val) {
                        if(i !== '__is_recursive') {
                            converted.push(convert(val));
                        }
                    });
                    data.result = converted;
                }
            });
            //Navigation Menu Slider
            $('#mm-pages').on('click',function(e){
          		//e.preventDefault();
          		$('body').toggleClass('nav-expanded');
          	});
          	$('#nav-close').on('click',function(e){
          		//e.preventDefault();
          		$('body').removeClass('nav-expanded');
          	});
		});
	</script>
{/literal}