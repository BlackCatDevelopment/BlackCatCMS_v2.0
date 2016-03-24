	{get_page_footers('backend')}
	<div class="clear"></div>
</div>
<div class="clear"></div>
<div id="fc_footer" class="fc_gradient1 fc_border">
	<div id="fc_content_footer" {if !$permissions.pages} class="fc_no_sidebar"{/if}>
		<!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
		<a href="{$URL_HELP}" title="BlackCat CMS" target="_blank">BlackCat CMS</a> is released under the <a href="http://www.gnu.org/licenses/gpl.html" title="Black Cat CMS Core is GPL" target="_blank">GNU General Public License</a>.
		<!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
		<div id="fc_footer_info">
            <span>{translate('Remaining session time')}: <span id="fc_session_counter">{$SESSION_TIME}</span></span>
			<span id="fc_showFooter_info" class="icon-logo_bc fc_gradient1 fc_gradient_hover fc_border_all"> {translate('System information')}</span>
			<ul class="fc_gradient1 fc_border fc_shadow_small fc_br_top">
				<li><span>{$CAT_CORE}:</span> {$CAT_VERSION} {if $CAT_BUILD}(Build {$CAT_BUILD}){/if}</li>
				<li><span>{$THEME_NAME}:</span> {$THEME_VERSION}</li>
                <li><span>{translate('Website title')}:</span> {$WEBSITE_TITLE}</li>
				{foreach $system_information as info}
				<li><span>{$info.name}:</span> {$info.status}</li>
				{/foreach}
			</ul>
		</div>
	</div>
</div>
</body>
</html>