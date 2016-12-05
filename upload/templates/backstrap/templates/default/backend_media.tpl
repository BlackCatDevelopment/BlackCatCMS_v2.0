{include file="backend_modal.tpl"
         modal_id="itemDetails"
         modal_title="Details for media file: "
}


<div class="row">
    <div class="col-md-2">{* left navigation bar *}
        <h2>{translate('Folders')}</h2>
        {template mediadirs data}{foreach $data item}
        <li>
            <span class="pull-left fa fa-fw fa-folder" {if $item.children}data-toggle="collapse" data-target="#sub_{$item.id}" aria-expanded="false{/if}"></span>
            <a href="#" {if $item.children}data-toggle="collapse" data-target="#sub_{$item.id}" aria-expanded="false"{/if}>
                {$item.title}
                {if $item.children}<i class="fa fa-caret-down"></i>{/if}
            </a>
            {if $item.children}
            <ul class="nav collapse submenu" id="sub_{$item.id}" role="menu">
                {mediadirs $item.children}
            </ul>{/if}
        </li>{/foreach}{/template}
        <div role="navigation">
          <ul class="nav nav-media">
            {mediadirs $dirs}
          </ul>
        </div>
    </div>{* end left navigation bar *}

    <div class="col-md-10">
        <ul class="nav nav-tabs" role="tablist">{* Tabs *}
            <li role="presentation" class="active">
                <a href="#list" aria-controls="list" role="tab" data-toggle="tab">
                  <span class="fa fa-fw fa-bars"></span>
                  {translate('List')}
                </a>
            </li>
            <li role="presentation">
                <a href="#dia" aria-controls="dia" role="tab" data-toggle="tab">
                  <span class="fa fa-fw fa-th"></span>
                  {translate('Grid')}
                </a>
            </li>
        </ul>

        <div class="tab-content">{* Tab panes *}
          <div role="tabpanel" class="tab-pane active" id="list">
              <table class="table datatable compact">
                <thead>
                  <tr>
                        <th>{translate('Preview')}</th>
                        <th>{translate('Filename')}</th>
                        <th>{translate('Type')}</th>
                        <th>{translate('Size')}</th>
                        <th>{translate('Date')}</th>
                  </tr>
                </thead>
                <tbody>
                    {foreach $files file}
                    <tr>
                        <td>{if $file.image && $file.preview}<img src="{$CAT_URL}{$file.preview}" alt="{translate('Preview')}" title="{translate('Preview')}" class="thumb bs-media-details" />{/if}</td>
                        <td>{cat_filename($file.filename)}</td>
                        <td>{$file.mime_type}</td>
                        <td>{$file.hfilesize}</td>
                        <td>{$file.moddate}</td>
                    </tr>
                    {/foreach}
                </tbody>
              </table>
          </div>
          <div role="tabpanel" class="tab-pane" id="dia">
          </div>
        </div>
    </div>
</div>


{$file = cat('/modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        var lang   = '{$LANGUAGE}'.toLowerCase();
        var dtable = $('table.table').DataTable({
            mark: true,
            stateSave: true,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: 0 }
            ],
            orderClasses: false{if cat_file_exists($file)},
            language: {
                url: CAT_URL + "{$file}"
            }
            {/if}
        });
        $('a.has-child').on('mouseover',function() {
            $(this).addClass('fa-caret-right');
        }
    });
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