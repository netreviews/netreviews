<div class="av_product_award light">
	<div id="top">
		<div class="ratingWrapper">
			<div class="ratingInner" style="width:{$av_rate_percent|intval}%;"></div>
		</div>
		<div  id="slide" style="display: inline-block;vertical-align: text-bottom;">
			<a href="javascript:()" id="AV_button">
				<b>
					{$av_nb_reviews|intval} &nbsp;
					{if $av_nb_reviews > 1}
						{l s='reviews' mod='netreviews'}
					{else}
						{l s='review' mod='netreviews'}
					{/if}
				</b>
			</a>
		</div>
	</div>
</div>