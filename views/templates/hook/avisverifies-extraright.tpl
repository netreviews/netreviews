<!--
* 2012-2016 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2016 NetReviews SAS
*  @version   Release: $Revision: 7.2.2
*  @license   NetReviews
*  @date      26/10/2016
*  International Registered Trademark & Property of NetReviews SAS
-->

<!-- <div class="av_product_award">

<div id="top">
	<div class="ratingWrapper">
    	<div class="ratingInner percent{$av_rate_percent|escape:'htmlall':'UTF-8'}" ></div>
    </div>
	<b>{$av_nb_reviews|escape:'htmlall':'UTF-8'} &nbsp;
	{if $av_nb_reviews > 1}
		{l s='reviews' mod='netreviews'}
	{else}
		{l s='review' mod='netreviews'}
	{/if}
	</b>
</div>
<div id="bottom"><a href="javascript:()" id="AV_button">{l s='See the reviews' mod='netreviews'}</a></div>
	<img id="sceau" src="{$modules_dir|escape:'htmlall':'UTF-8'}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}" />
</div>
 -->

<div class="av_product_award">
<div id="top">
	<div class="ratingWrapper">
    	<div class="ratingInner" style="width:{$av_rate_percent|escape:'htmlall':'UTF-8'}%"></div>
    </div>
	<div class="ratingText">
		<b><span class="ratingValue">{$av_rate|escape:'htmlall':'UTF-8'}</span> / <span class="bestRating">5</span></b> -
		<span class="reviewCount">
			{$av_nb_reviews|escape:'htmlall':'UTF-8'}
		</span>
		{if $av_nb_reviews > 1}
			{l s='reviews' mod='netreviews'}
		{else}
			{l s='review' mod='netreviews'}
		{/if}
	</div>
</div>
<div id="bottom"><a href="javascript:()" id="AV_button">{l s='See the reviews' mod='netreviews'}</a></div>
	<img id="sceau" src="{$modules_dir|escape:'htmlall':'UTF-8'}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}" />
</div>