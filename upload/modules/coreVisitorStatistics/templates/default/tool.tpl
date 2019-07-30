<table class="table">
    <thead>
        <tr>
            <th colspan="2">{translate('Page')}</th>
            <th>{translate('Impressions')}</th>
            <th>{translate('Last visited')}</th>
        </tr>
    </thead>
    <tbody>
{foreach $data line}
        <tr>
            <td>{$line.page_id}</td>
            <td>{$line.menu_title}</td>
            <td>{$line.visits}</td>
            <td>{cat_format_date($line.last,1)}</td>
        </tr>
{/foreach}
    </tbody>
</table>