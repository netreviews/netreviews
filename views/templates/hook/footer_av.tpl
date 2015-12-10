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

<div vocab="http://rdf.data-vocabulary.org/#" typeof="Product" style="clear:both" id="av_snippets_block" >

	<div id="av_snippets_left">

		<img src="{$modules_dir}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}" width="50"/>
	</div>

	<div id="av_snippets_right">
		{l s='Product' mod='netreviews'} : <span property="name">{$product_name}</span> - 
		<span property="description">{$product_description|strip_tags|truncate:75}</span>
		<br>
		<span rel="offerDetails" typeof="Offer">
			<meta property="currency" content="{$currency->iso_code}">
			{if $product->quantity > 0}<link itemprop="availability" content="in_stock"/>{/if}
			{l s='Price' mod='netreviews'} : <span property="price">{$product_price}</span>{$currencySign}
		</span>
		<br>
		<div rel="review" typeof="Review-aggregate">
			{l s='Evaluation of' mod='netreviews'} <span property="itemreviewed">{$product_name}</span> : 
			
			<span property="rating">{$average_rate}</span>/5 {l s='out of' mod='netreviews'} <span property="count">{$count_reviews}</span> {l s='reviews' mod='netreviews'}

			<div class="ratingWrapper" style="display:inline-block;">
				<div class="ratingInner" style="width:{$average_rate_percent}%"></div>
			</div>

		</div>
	</div>

	<div style="clear:both"></div>


</div>

