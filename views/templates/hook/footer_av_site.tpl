<!--
* 2012-2016 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2016 NetReviews SAS
*  @version   Release: $Revision: 7.2.0
*  @license   NetReviews
*  @date      20/09/2016
*  International Registered Trademark & Property of NetReviews SAS
-->

<span id="netreviews_global_website_review" itemscope="item" itemtype="http://data-vocabulary.org/Review-aggregate" style="width: 500px;margin: 0px auto;float: none;display: block;text-align: center;">
    <div id="av_snippets_right"  style='width: 500px;display: block;float: none;'>
        <div itemprop="itemReviewed" itemscope itemtype="http://schema.org/Organization" class="hidden">
            <span itemprop="name">{$name_site|escape:'htmlall':'UTF-8'}</span>
        </div>
        <span itemprop="rating" itemscope="" itemtype="http://data-vocabulary.org/rating" class="bandeauServiceClientAvisNoteGros">
            <span itemprop="average">{$av_site_rating_rate|escape:'htmlall':'UTF-8'}</span> {l s='on' mod='netreviews'} <span itemprop="best">5</span>
        </span>
        <span itemprop="votes">{$av_site_rating_avis|escape:'htmlall':'UTF-8'}</span>
        {if $av_site_rating_avis > 1}
            {l s='reviews' mod='netreviews'}
        {else}
            {l s='review' mod='netreviews'}
        {/if}
        {l s='with' mod='netreviews'}
        <span itemprop="reviewer"><a href="http://{$url_platform|escape:'htmlall':'UTF-8'}">{$url_platform|escape:'htmlall':'UTF-8'}</a></span>
        <div style="clear:both"></div>
    </div>
</span>

