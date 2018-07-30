<div id="bcstats">
    <div class="row">
        <table style="width:100%" class="table table-striped table-borderless table-sm">
            <thead class="thead-light">
                <tr>
                    <th colspan="2">{translate('Pages by visibility')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $visibility key value}
                <tr><td>{translate($key)}</td><td>{$value}</td></tr>
                {/foreach}
            </tbody>
            <thead class="thead-light">
                <tr>
                    <th colspan="2">{translate('Latest changed pages')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $latest item}
                    <tr>
                        <td><a href="{$CAT_ADMIN_URL}/pages/modify.php?page_id={$item.page_id}">{$item.menu_title}</a></td>
                        <td>{cat_format_date($item.modified_when,1)}</td>
                    </tr>
                    {/foreach}
            </tbody>
        </table>
    </div>
</div>
