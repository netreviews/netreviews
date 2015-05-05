<!--
* 2012-2015 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2015 NetReviews SAS
*  @version   Release: $Revision: 7.1.31
*  @license   NetReviews
*  @date      13/02/2015
*  International Registered Trademark & Property of NetReviews SAS
-->
 
<div class="av_product_award">
<div id="top">
	<div class="ratingWrapper">
    	<div class="ratingInner percent{$av_rate_percent|intval}" ></div>
    </div>
	<b>{$av_nb_reviews|intval} &nbsp;
	{if $av_nb_reviews > 1}
		{l s='reviews' mod='netreviews'}
	{else}
		{l s='review' mod='netreviews'}
	{/if}
	</b>
</div>
<div id="bottom"><a href="javascript:()" id="AV_button">{l s='See the reviews' mod='netreviews'}</a></div>
	<img id="sceau" src="{$modules_dir|escape:'htmlall'}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}" />
</div>