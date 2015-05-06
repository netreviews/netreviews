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

<li>
	<a href="#idTabavisverifies" class="avisverifies_tab" id="tab_avisverifies">
		{$count_reviews|intval}
		{if $count_reviews > 1}
			{l s='Reviews' mod='netreviews'} 
		{else}
			{l s='Review' mod='netreviews'} 
		{/if} 
	</a>
</li>