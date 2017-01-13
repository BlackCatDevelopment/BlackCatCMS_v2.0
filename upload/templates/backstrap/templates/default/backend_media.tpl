{* will be cloned and added to list and grid view *}
<span class="bsFilterSelect pull-right" style="display:none">
    <label for="filter">{translate('Filter by type')}</label>
    <select name="filter">
        <option value="">{translate('All')}</option>
        <option value="image">{translate('Images')}</option>
        <option value="video">{translate('Videos')}</option>
        <option value="audio">{translate('Audio')}</option>
        <option value="other">{translate('Other')}</option>
    </select>
</span>

{* template to fill the subdir select *}
{template option data}{foreach $data item}
    <option value="{$item.path}"{if $__.curr_folder == $item.path} selected="selected"{/if}>{$item.path}</option>
    {if $item.children}{option $item.children}{/if}
{/foreach}{/template}

<ul class="nav nav-tabs" role="tablist">{* Tabs *}
    <li role="presentation">
        <a href="#list" aria-controls="list" role="tab" data-toggle="tab">
          <span class="fa fa-fw fa-bars"></span>
          {translate('List')}
        </a>
    </li>
    <li role="presentation">
        <a href="#grid" aria-controls="grid" role="tab" data-toggle="tab">
          <span class="fa fa-fw fa-th"></span>
          {translate('Grid')}
        </a>
    </li>
    {if user_has_perm('media_upload')}
    <li role="presentation">
        <a href="#upload" aria-controls="upload" role="tab" data-toggle="tab">
          <span class="fa fa-fw fa-upload"></span>
          {translate('Upload')}
        </a>
    </li>
    {/if}
    <li>
        <span class="fa fa-fw fa-folder"></span>
        <label for="media_folder">{translate('Select folder')}:</label>
        <select id="root_folder" name="root_folder">
            <option value="">[{translate('Root folder')}]</option>
            {option $dirs}
        </select>
    </li>
</ul>

<div class="tab-content">{* Tab panes *}
    {include file="backend_media_listtab.tpl"}
    {include file="backend_media_gridtab.tpl"}
    {if user_has_perm('media_upload')}{include file="backend_media_uploadtab.tpl"}{/if}
</div>{* Tab panes End *}


{* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
{* set javascript var for later use *}
<script type="text/javascript">
//<![CDATA[
    var CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
//]]>
</script>


<pre style="display:none">
mime_type = 'image/jpeg'
filesize = 1563209
filepath = 'P:/BlackCat2/cat_engine/media'
filename = 'Firebird (002).jpg'
filenamepath = 'P:/BlackCat2/cat_engine/media/Firebird (002).jpg'
encoding = 'UTF-8'
error = 'n/a'
warning = 'n/a'
hfilesize = '1.49 MB'
moddate = '22-04-2016 12:39'
image = true
preview = '/media/Firebird (002).jpg'
exif (array):
    ExposureTime = 0.030303030303030304
    ISOSpeedRatings = 320
    ShutterSpeedValue = 5.0599999999999996
    FocalLength = 4.1500000000000004
    ExifImageWidth = 3264
    ExifImageLength = 2448
    DateTimeOriginal = '2016:04:22 11:45:04'
    Make = 'Apple'
    Model = 'iPhone 6'
    Orientation = 1
    XResolution = 72
    YResolution = 72
    FileDateTime = 1461328763
</pre>