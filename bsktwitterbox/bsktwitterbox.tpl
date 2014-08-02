<div class="bsktwitterbox block" data-widgetid="{$widget_id}" data-limit="{$tweets_limit}">
    <p class="title_block">{l s="Latest Tweets" mod="bsktwitterbox"}</p>
    {if $follow_btn}
    <a href="https://twitter.com/{$user}" class="twitter-follow-button" data-dnt="true" data-show-count="false">Follow @{$user}</a>
    <script>!function(d,s,id){ var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){ js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs); } }(document,"script","twitter-wjs");</script>
    {/if}
    <div class="block_content"></div>
</div>