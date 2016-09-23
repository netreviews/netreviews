<!--
* 2012-2016 NetReviews
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2016 NetReviews SAS
*  @version   Release: $Revision: 7.2.0
*  @license   NetReviews
*  @date      20/09/2016
*  International Registered Trademark & Property of NetReviews SAS
-->

{literal}
	<style type="text/css">
		.groupAvis{
			display: none;
		}
	</style>
{/literal}

<div id="av_more_info_tabs"></div>
<div class="clear"></div>
<div class="tab-pane fade tab_media" id="idTabavisverifies"  >

	<div id="headerAV">{l s='Product Reviews' mod='netreviews'}</div>
	<div id="under-headerAV"  style="background: url({$modules_dir|escape:'htmlall':'UTF-8'}netreviews/views/img/{l s='Sceau_100_en.png' mod='netreviews'}) no-repeat #f1f1f1;background-size:45px 45px;background-repeat:no-repeat;">
		<ul id="aggregateRatingAV">
			<li><b>
				{l s='Number of Reviews' mod='netreviews'}
			</b> : {$count_reviews|escape:'htmlall':'UTF-8'}</li>
			<li><b>{l s='Average Grade' mod='netreviews'}</b> : {$average_rate|floatval} /5 <div class="ratingWrapper" style="display:inline-block;">
    	<div class="ratingInner" style="width:{$average_rate_percent|escape:'htmlall':'UTF-8'}%"></div>
    </div></li>

		</ul>
		<ul id="certificatAV">
			<li><a href="{$url_certificat|escape:'htmlall':'UTF-8'}" target="_blank" class="display_certificat_review" >{l s='Show the attestation of Trust' mod='netreviews'}</a></li>
		</ul>

		<div class="clear"></div>

	</div>

	<div id="ajax_comment_content">

		{assign var = 'i' value = 0}
		{assign var = 'first' value = true}

		{foreach from=$reviews key=k_review item=review}
			{if $i == 1 && !$first}
				<span class="groupAvis">
			{/if}
			<div class="reviewAV" itemprop="review" itemscope itemtype="http://schema.org/Review" >
				<ul class="reviewInfosAV"   >
					<li style="text-transform:capitalize" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">{$review['customer_name']|escape:'htmlall':'UTF-8'}</span></li>
					<li>&nbsp;{l s='the' mod='netreviews'} {$review['horodate']|escape:'htmlall':'UTF-8'}</li>
					<li class="rateAV" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><img src="{$modules_dir|escape:'htmlall':'UTF-8'}netreviews/views/img/etoile{$review['rate']|escape:'htmlall':'UTF-8'}.png" width="80" height="15" /> <span itemprop="ratingValue">{$review['rate']|escape:'htmlall':'UTF-8'}</span>/<span  itemprop="bestRating">5</span></li>
				</ul>

				<div class="triangle-border top"  itemprop="reviewBody" >{$review['avis']|escape:'htmlall':'UTF-8'}</div>

			{if $review['discussion']|escape:'htmlall':'UTF-8'}
				{foreach from=$review['discussion'] key=k_discussion item=discussion}

				<div class="triangle-border top answer" {if $k_discussion > 0} review_number={$review['id_product_av']|escape:'htmlall':'UTF-8'} style= "display: none" {/if}>

					<span>&rsaquo; {l s='Comment from' mod='netreviews'}  <b style="text-transform:capitalize; font-weight:normal">{$discussion['origine']|escape:'htmlall':'UTF-8'}</b> {l s='the' mod='netreviews'} {$discussion['horodate']|escape:'htmlall':'UTF-8'}</span>
					<p class="answer-bodyAV">{$discussion['commentaire']|escape:'htmlall':'UTF-8'}</p>

				</div>

				{/foreach}

				{if $k_discussion > 0}
					<a href="javascript:switchCommentsVisibility('{$review['id_product_av']|escape:'htmlall':'UTF-8'}')" style="padding-left: 6px;margin-left: 30px; display: block; font-style:italic" id="display{$review['id_product_av']|escape:'htmlall':'UTF-8'}" class="display-all-comments" review_number={$review['id_product_av']|escape:'htmlall':'UTF-8'} >{l s='Show exchanges' mod='netreviews'}</a>

					<a href="javascript:switchCommentsVisibility('{$review['id_product_av']|escape:'htmlall':'UTF-8'}')" style="padding-left: 6px;margin-left: 30px; display: none; font-style:italic" id="hide{$review['id_product_av']|escape:'htmlall':'UTF-8'}" class="display-all-comments" review_number={$review['id_product_av']|escape:'htmlall':'UTF-8'} >{l s='Hide exchanges' mod='netreviews'}</a>
					</a>
			  	{/if}
			{/if}

			</div>
			{if $i == $avisverifies_nb_reviews && !$first}
				</span>
				{$i = 1}
			{else}
				{$i = $i + 1}
			{/if}
			{if $i == $avisverifies_nb_reviews && $first}
				{$first = false}
				{$i = 1}
			{/if}
		{/foreach}


	</div>
	<img src="{if $is_https}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/netreviews/views/img/pagination-loader.gif" id="av_loader" style="display:none" />
	{if $count_reviews > $avisverifies_nb_reviews}
		<a href="#" id="av_load_comments" class="av-btn-morecomment" rel="2">{l s='More reviews...' mod='netreviews' }</a>
	{/if}

</div>
<div class="clear"></div>


{literal}
<script>
	//<![CDATA[
    $('#av_load_comments').live("click", function(){

		vnom_group = {/literal}{if !empty({$nom_group|escape:'htmlall':'UTF-8'})}{$nom_group|escape:'htmlall':'UTF-8'}{else}0{/if}{literal} ;
		vid_shop = {/literal}{if !empty({$id_shop|escape:'htmlall':'UTF-8'})}{$id_shop|escape:'htmlall':'UTF-8'}{else}0{/if}{literal} ;

		counted_reviews = {/literal}{$count_reviews|escape:'htmlall':'UTF-8'}{literal};
		maxpage = Math.ceil(counted_reviews / {/literal}{$avisverifies_nb_reviews|escape:'htmlall':'UTF-8'}{literal}) ;

		console.log(counted_reviews);
		console.log(maxpage);

		if($('.groupAvis:hidden').first().length !== 0){
			$('.groupAvis:hidden').first().css({ visibility: "visible", display: "block" });

			console.log($(this).attr('rel'));
			console.log(maxpage);

			if(maxpage == parseInt($(this).attr('rel')) && $('.groupAvis:hidden').length === 0){
				$(this).hide();
			} else {
				$(this).attr('rel',parseInt($(this).attr('rel')) + 1 );
			}

			return false;
		}

		$.ajax({
			url: "{/literal}{if $is_https}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}{literal}modules/netreviews/ajax-load.php",
			type: "POST",
			data: {p : $(this).attr('rel'), id_product : $('input[name="id_product"]').val(), count_reviews : counted_reviews, id_shop : vid_shop, nom_group : vnom_group},
			beforeSend: function() {
				backup_content = $("#ajax_comment_content").html();
			   // $("#ajax_comment_content").slideUp().empty();
			   $('#av_loader').show();
			},
			success: function( html ){
			  //  $("#ajax_comment_content").empty();
			  $('#av_loader').hide();
				$("#ajax_comment_content").append(html);
				$('#av_load_comments').attr('rel', parseInt($('#av_load_comments').attr('rel')) + 1);
				//$('html,body').animate({scrollTop: $("#ajax_comment_content").offset().top}, 'slow');
				//console.log($('#av_load_comments').attr('rel'));
			},
			error: function ( jqXHR, textStatus, errorThrown ){
				alert('something went wrong...');
				$("#ajax_comment_content").html( backup_content );
			}
		});
		return false;
    })
	//]]>
</script>
{/literal}


