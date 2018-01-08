{foreach $news item}
    <a href="{$item.link}" target="_blank" data-title="">{$item.title}</a> <span style="font-size:.5em;">({$item.published})</span><br />
    <span style="font-size:.6em;">{$item.content} <a href="{$item.link}">{translate('Read more')}</a></span><br />
{/foreach}