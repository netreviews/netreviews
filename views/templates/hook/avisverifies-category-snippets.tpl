<!--
* 2012-2015 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2015 NetReviews SAS
*  @version   Release: $Revision: 7.1.41
*  @license   NetReviews
*  @date      25/08/2015
*  International Registered Trademark & Property of NetReviews SAS
-->

{if $count_reviews != 0}
    <div class="col-sm-6" id="netreviews_category_review" itemscope="item" itemtype="http://data-vocabulary.org/Review-aggregate">
        <div >
        <span itemprop="itemReviewed">{$nom_category|escape:'htmlall':'UTF-8'}</span> -
            <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/rating">
                <span>
                 <div class="ratingWrapper">
                    <div class="ratingInner" style="width:{$av_rate_percent|escape:'htmlall':'UTF-8}%"></div>
                </div>
                    <b itemprop="average">{$average_rate|escape:'htmlall':'UTF-8'}</b> /
                    <span itemprop="best">5</span> {l s='on' mod='netreviews'}
                </span>
             </span>
            <b itemprop="votes">{$count_reviews|escape:'htmlall':'UTF-8'}</b>
            {if $count_reviews > 1}
                {l s='reviews' mod='netreviews'}
            {else}
                {l s='review' mod='netreviews'}
            {/if}
            <meta content="Avis-vérifiés.com" temprop="reviewer"/>
        </div>
    </div>
{/if}





