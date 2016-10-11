<!--
* 2012-2016 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2016 NetReviews SAS
*  @version   Release: $Revision: 7.2.1
*  @license   NetReviews
*  @date      20/09/2016
*  International Registered Trademark & Property of NetReviews SAS
-->

<div class="av_product_award light">
	<a href="javascript:()" id="AV_button">
		<div id="top">
			<div class="ratingWrapper">
				<div class="ratingInner" style="width:{$av_rate_percent|escape:'htmlall':'UTF-8'}%"></div>
			</div>
			<div id="slide">
				<b><span class="ratingValue">{$av_rate|escape:'htmlall':'UTF-8'}</span> / <span class="bestRating">5</span></b> -  <meta content="1">
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
	</a>
</div>