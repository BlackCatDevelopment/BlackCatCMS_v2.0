/**
 * Source: https://bootsnipp.com/snippets/featured/bootstrap-30-treeview
 *
 * Extended for use with BlackCat CMS v2.0
 *   - use FontAwesome instead of Glyphicon (no longer part of Bootstrap)
 *   - trigger FA icon by CSS class ("treed" for plus/minus view (default),
 *     "treed folder" for folder view, "treed chevron" for chevron view)
 *   - add "openAll" to have the complete tree opened
 *
 **/
$.fn.extend({
    treed: function (o) {

      var openedClass = 'fa fa-fw fa-minus'; // default
      var closedClass = 'fa fa-fw fa-plus';

      if (typeof o != 'undefined'){
        if (typeof o.openedClass != 'undefined'){
          openedClass = o.openedClass;
        }
        if (typeof o.closedClass != 'undefined'){
          closedClass = o.closedClass;
        }
      };

      //initialize each of the top levels
      var tree = $(this);
      tree.addClass("tree");
      
      tree.find('li').has("ul").each(function () {
        var branch = $(this); //li with children ul
        var indicator = (tree.hasClass("openAll") ? openedClass : closedClass);
        branch.prepend("<i class='indicator " + indicator + "'></i>");
        branch.addClass('branch');
        branch.on('dblclick', function (e) {
          if (this == e.target) {
            var icon = $(this).children('i:first');
            icon.toggleClass(openedClass + " " + closedClass);
            $(this).find('ul').toggle();
          }
        })
        if(!tree.hasClass("openAll")) {
            branch.find('ul').toggle();
        }
      });
      //fire event from the dynamically added icon
      tree.find('.branch .indicator').each(function(){
        $(this).on('dblclick', function () {
            $(this).closest('li').dblclick();
        });
      });
      //fire event to open branch if the li contains an anchor instead of text
/*
      tree.find('.branch>a').each(function () {
        $(this).on('dblclick', function (e) {
          $(this).closest('li').dblclick();
          e.preventDefault();
        });
      });
*/
      //fire event to open branch if the li contains a button instead of text
      tree.find('.branch>button').each(function () {
        $(this).on('click', function (e) {
          $(this).closest('li').click();
          e.preventDefault();
        });
      });
    }
});

//Initialization of treeviews
$('ul.treed:not(.folder,.chevron)').treed();
$('ul.treed.folder').treed({openedClass:'fa fa-fw fa-folder-open-o', closedClass:'fa fa-fw fa-folder-o'});
$('ul.treed.chevron').treed({openedClass:'fa fa-fw fa-chevron-right', closedClass:'fa fa-fw fa-chevron-down'});
