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

<style>
#av_snippets_block {
	margin:10px;
	padding:10px;
	border-top:1px solid #E7E8E3 ;
	border-bottom:1px solid #E7E8E3 ;
}

#av_snippets_left{
	float:left;
	display: block;
	width:50px;
	height:50px;
	padding-right: 10px;
}

#av_snippets_right{
	float:left;
}


</style>
<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" style="clear:both" id="av_snippets_block" >

	<div id="av_snippets_left">

		<img src="{$modules_dir|escape:'htmlall':'UTF-8'}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}" width="50"/>
	</div>

	<div id="av_snippets_right">
		{l s='Product' mod='netreviews'} : <span itemprop="itemreviewed" >{$product_name|escape:'htmlall':'UTF-8'}</span> -
		<span property="description">{$product_description|escape:'htmlall':'UTF-8'|truncate:75}</span>
		<br>
		<div>
			{l s='Evaluation of' mod='netreviews'} <span>{$product_name|escape:'htmlall':'UTF-8'}</span> :

			<span  itemprop="ratingValue" >{$average_rate|escape:'htmlall':'UTF-8'}</span>/<span itemprop="bestRating" class="bestRating">5</span> <meta itemprop="worstRating" content="1"> {l s='out of' mod='netreviews'} <span  itemprop="reviewCount">{$count_reviews|escape:'htmlall':'UTF-8'}</span> {l s='reviews' mod='netreviews'}

			<div class="ratingWrapper" style="display:inline-block;">
				<div class="ratingInner" style="width:{$average_rate_percent|escape:'htmlall':'UTF-8'}%"></div>
			</div>

		</div>
	</div>

	<div style="clear:both"></div>


</div>

