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
<h3 style='{if $styleH3 != ''}{$styleH3}{/if}' >
	<a href="#idTabavisverifies" class="avisverifies_tab" data-toggle="tab" id="tab_avisverifies" style='{if $styleA != ''}{$styleA}{/if}'>
		{$count_reviews|escape:'htmlall':'UTF-8'}
		{if $count_reviews > 1}
			{l s='Reviews' mod='netreviews'}
		{else}
			{l s='Review' mod='netreviews'}
		{/if}
	</a>
</h3>