<div class="alert alert-info">
    {translate('Below you can see which Favicon files BlackCat CMS is looking for to populate the page header. A checkmark shows if the file is available.')}
    {translate('While there are several different sizes (for older devices in most cases), we only look for the files with the highest possible pixel rate, as these will still look good when sized down by the device.')}
    {translate('The CMS will also look for a &quot;browserconfig.xml&quot; file (for Internet Explorer >= 11) and manifest.json (for Web Apps).')}
</div>
<table class="table table-striped table-hover table-condensed">
{foreach $seen group items}
    <thead>
        <tr><th colspan="2">{translate($group)}</th></tr>
    </thead>
    <tbody>
    {foreach $items name avail}
    <tr><td>{$name}</td><td><i class="fa fa-fw{if $avail} fa-check{/if}"></i></td></tr>
    {/foreach}
    </tbody>
{/foreach}
</table>