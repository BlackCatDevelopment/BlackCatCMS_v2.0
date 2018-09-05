    <form method="GET" action="{$CAT_ADMIN_URL}/media/{$current}" id="bsMediaFolderSelect">
        <span class="fa fa-fw fa-folder"></span>
        <label for="dir_id">{translate('Select folder')}:</label>
        <select id="dir_id" name="dir_id">
            {foreach $dirs item}
            <option value="{$item.dir_id}"{if $__.curr_folder == $item.dir_id} selected="selected"{/if}>{if $item.path == ''}[{translate('Root folder')}]{else}{$item.path}{/if}</option>
            {/foreach}
        </select>
    </form>