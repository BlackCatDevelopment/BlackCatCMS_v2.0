<table class="treetable">
  <tbody>
    {foreach $permissions perm}
    <tr class="treegrid treegrid-{$perm.perm_id}{if $perm.requires>0} treegrid-parent-{$perm.requires}{/if}">
      <td class="title">{$perm.title}</td>
      <td class="title">{translate($perm.description)}</td>
    </tr>
    {/foreach}
  </tbody>
</table><br /><br />

<script>
//<![CDATA[
    $('.treetable').treegrid();
    $('.treetable').treegrid('expandAll');
    $('ul.list li').each( function() {
        var text = $(this).contents().get(0).nodeValue;
        $(this).attr('data-value',text);
    });
    $('ol.list').bonsai({
        expandAll: true,
        checkboxes: true
    });
//]]>
</script>