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

<!-- DEBUT - Integration etoiles AvisVerifies -->
{if isset($av_rate) && !empty($av_rate)}
	<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">

		<meta itemprop="itemreviewed" content="{$product_name|escape:'htmlall':'UTF-8'}"/>
		<meta itemprop="ratingValue" content="{$average_rate|escape:'htmlall':'UTF-8'}"/>
		<meta itemprop="bestRating" content="5"/>
		<meta itemprop="reviewCount" content="{$av_nb_reviews|escape:'htmlall':'UTF-8'}"/>

		<div >
			<div class="ratingWrapper" style="background-size: 100px; width: 100px;position: relative;">
				<div class="ratingInner" style=" background-size: 100px;width:{$av_rate_percent|escape:'htmlall':'UTF-8'}%"></div>
			</div>
			<div style="padding:1px;">
				<a href="{$link_product|escape:'htmlall':'UTF-8'}" title="{$av_nb_reviews|escape:'htmlall':'UTF-8'} {if $av_nb_reviews > 1}{l s='reviews' mod='netreviews'}{else}{l s='review' mod='netreviews'}{/if}">
					{$av_nb_reviews|escape:'htmlall':'UTF-8'}
					{if $av_nb_reviews > 1}
						{l s='reviews' mod='netreviews'}
					{else}
						{l s='review' mod='netreviews'}
					{/if}
				</a>
			</div>
		</div>
	</div>
{/if}
<!-- FIN - Integration etoiles AvisVerifies -->