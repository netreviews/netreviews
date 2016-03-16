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

<div id="avisverifies_module">
	<div id="av-header-intro">
		<div id="av-top">
			<table class="tg" cellspacing="0" cellpadding="0">
			  <tr>
			    <th class="tg-031e"><img src="{$av_path|escape:'htmlall'}views/img/{l s='logo_full_en.png' mod='netreviews'}"/></th>
			    <th class="tg-031e"><span id="av-title">{l s='Increase your sales through customer reviews' mod='netreviews'}</span></th>
			    <th class="tg-031e"><a href="{l s='url_avisverifies_track' mod='netreviews'}" class="av-button-calltoaction" target="_blank">{l s='Start now' mod='netreviews'}</a></th>
			  </tr>
			</table>
		</div>
		<hr class="orange"/>
		<div id="av-middle">
			<table class="tg">
			  <tr>
			    <th class="tg-031e"><p>{l s='Verified Reviews is an innovative and independent solution that enables you to collect, moderate and publish your customer reviews. You will increase your credibility and visibility on the web towards new customers!' mod='netreviews'}</p>
					<p>{l s='We provide you with a solution that enables you to collect customer reviews about your website and products which will show on your website and on a attestation which will increase the credibility of published reviews.' mod='netreviews'}</p>
					<br>
					<p><center><img src="{$av_path|escape:'htmlall'}views/img/widget-screen-uk.png" ></center></p>
					<p><img src="{$av_path|escape:'htmlall'}views/img/NFS_Avis-en-ligne.png" width="50"> {l s='Our services are approved by AFNOR certification (France)' mod='netreviews'}*</p>
				</th>
			    <th class="tg-031e valigntop">
			       	<ul class="av-list-star">
			    		<li>{l s='Give your clients a voice' mod='netreviews'}</li>
			    		<li>{l s='Increase your sales up to 25%' mod='netreviews'}</li>
			    		<li>{l s='Improve your SEO with Rich Snippets' mod='netreviews'}</li>
			    		<li>{l s='Boost your Adwords campaign by gaining star ratings from our partner' mod='netreviews'}* <img src="{$av_path|escape:'htmlall'}views/img/google-adwords.png" width="100"></li>
			    		<li>{l s='Control your e-reputation' mod='netreviews'}</li>
			    		<li>{l s='Enjoy our multiple tools' mod='netreviews'}</li>
			    	</ul>
			    	<br>
			    	<center><a href="{l s='url_avisverifies_track' mod='netreviews'}" class="av-button-calltoaction"  target="_blank">{l s='Start now' mod='netreviews'}</a> <br><i>{l s='No commitment, free trial for 15 days' mod='netreviews'}</i></center>
			    </th>
			  </tr>
			</table>
			<p><center><img src="{$av_path|escape:'htmlall'}views/img/prestashop_partner_logo_shadow.png" width="250"></center></p>
			<span class="asterisc">*{l s='Only available for some specific countries, please get in touch' mod='netreviews'}</span>
		</div>
	</div>
	<br>
	<fieldset class="fieldsetav">
		<legend>{l s='Informations' mod='netreviews'}</legend>
		<div class='informations'>
			<p>{l s='The Module Verified Reviews allows you to show verified product reviews on your product urls, to show the Widget Verified Reviews and to collect automatically verified customer reviews via Email after each single order.' mod='netreviews'}</p>
			<p>{l s='Attention : It is obligatory to register first on' mod='netreviews'} <a href="{l s='url_avisverifies_track' mod='netreviews'}" target="_blank">{l s='www.verified-reviews.com' mod='netreviews'}</a> {l s='to start your free trial period' mod='netreviews'}</p>
		</div>
	</fieldset>
	<br>
	<fieldset class="fieldsetav">
		<legend>{l s='Export my orders' mod='netreviews'}</legend>
		<div class='export'>
			<p>{l s='Export your recently received orders to collect immediately your first customer reviews and to show your attestation Verified Reviews.' mod='netreviews'}</p>
			<ul>
				<li>{l s='Without Product Reviews : Your customers will only be asked for their reviews regarding the order (obligatory)' mod='netreviews'}</li>
				<li>{l s='With Product Reviews : Your customers will be asked for their review regarding the order (obligatory) AND regarding the purchased products as well' mod='netreviews'}</li>
			</ul>
			<br>
			<form method="post" action="{$url_back|strip}" enctype="multipart/form-data">
				<label class="floatleft">{l s='Since' mod='netreviews'}</label>
				<select id="duree" name="duree" class="floatleft">
					<option value="1w">{l s='1 week' mod='netreviews'}</option>
					<option value="2w">{l s='2 weeks' mod='netreviews'}</option>
					<option value="1m">{l s='1 month' mod='netreviews'}</option>
					<option value="2m">{l s='2 months' mod='netreviews'}</option>
					<option value="3m">{l s='3 months' mod='netreviews'}</option>
					<option value="4m">{l s='4 months' mod='netreviews'}</option>
					<option value="5m">{l s='5 months' mod='netreviews'}</option>
					<option value="6m">{l s='6 months' mod='netreviews'}</option>
					<option value="7m">{l s='7 months' mod='netreviews'}</option>
					<option value="8m">{l s='8 months' mod='netreviews'}</option>
					<option value="9m">{l s='9 months' mod='netreviews'}</option>
					<option value="10m">{l s='10 months' mod='netreviews'}</option>
					<option value="11m">{l s='11 months' mod='netreviews'}</option>
					<option value="12m">{l s='12 months' mod='netreviews'}</option>
				</select>
				<div class="clear"></div>
				<label class="">{l s='Collect Product Reviews' mod='netreviews'}</label>
				<select id="productreviews" name="productreviews" class="floatleft">
					<option value="1">{l s='Yes' mod='netreviews'}</option>
					<option value="0">{l s='No' mod='netreviews'}</option>
				</select>
				<div class="clear"></div>
				<label class="">{l s='Export orders with status' mod='netreviews'}</label>
				<div class="floatleft">
					{foreach from=$order_statut_list item=state}
						<input type="checkbox" checked="checked" name="orderstates[]" value="{$state['id_order_state']|intval}"/> <span id="{$state['id_order_state']}">{$state['name']}</span><br>
					{/foreach}
				</div>
				<div class="clear"></div>
				<hr class="orange" >
				<center><input type="submit"  name="submit_export" id="submit_export" value="{l s='Export' mod='netreviews'}" class="button pointer"></center>
				<i id="VersionModule">Module Version {$version|escape:'html'}</i>
			</form>
		</div>
	</fieldset>
	<br>
	<fieldset class="fieldsetav">
		<legend>{l s='Configuration' mod='netreviews'}</legend>
		<div class='config'>
			<p>{l s='Please check your' mod='netreviews'} <a href="{l s='url_avisverifies_track' mod='netreviews'}" target="_blank">{l s='customer area on verified-reviews.com' mod='netreviews'}</a> {l s='to see your login data' mod='netreviews'}</p>
			<br />
			<br />
			<form method="post" action="{$url_back|unescape:'htmlall'}" enctype="multipart/form-data">
				<div id="blockConfiguration">
					{if $current_multilingue_checked  != 'checked'}
						{foreach from=$languages key=id item=lang}
							<input type="hidden" name="avisverifies_clesecrete_{$lang.iso_code}" id="avisverifies_clesecrete" value="{$current_avisverifies_clesecrete[$lang.iso_code]|escape:'html'}"/>
							<input type="hidden" name="avisverifies_idwebsite_{$lang.iso_code}" id="avisverifies_idwebsite" value="{$current_avisverifies_idwebsite[$lang.iso_code]|escape:'html'}"/>
						{/foreach}
						<div>
							<label class="titlepart">{l s='General configuration' mod='netreviews'}</label>
							<div class="clear"></div>
							<label>{l s='Secret Key' mod='netreviews'}</label>
							<input type="text" name="avisverifies_clesecrete" id="avisverifies_clesecrete" value="{$current_avisverifies_clesecrete['root']|escape:'html'}"/>
							<div class="clear"></div>
							<label>{l s='ID Website' mod='netreviews'}</label>
							<input type="text" name="avisverifies_idwebsite" id="avisverifies_idwebsite" value="{$current_avisverifies_idwebsite['root']|escape:'html'}"/>
							<div class="clear"></div>
						</div>
					{else}
						<input type="hidden" name="avisverifies_clesecrete" id="avisverifies_clesecrete" value="{$current_avisverifies_clesecrete[root]|escape:'html'}"/>
						<input type="hidden" name="avisverifies_idwebsite" id="avisverifies_idwebsite" value="{$current_avisverifies_idwebsite[root]|escape:'html'}"/>
						<div >
							<label class="titlepart">{l s='Multilingual configuration' mod='netreviews'}</label>
							<div class="clear"></div>
							{foreach from=$languages key=id item=lang}
								<div class="languages">
									<span >
									<img height="11" src="/img/l/{$lang.id_lang}.jpg" width="16" /><label class="namelanguage">{$lang.name|escape:'html'}</label></span><br />
									<div class="clear"></div>
									<label>{l s='Secret Key' mod='netreviews'}</label><input type="text" name="avisverifies_clesecrete_{$lang.iso_code}" id="avisverifies_clesecrete" value="{$current_avisverifies_clesecrete[$lang.iso_code]|escape:'html'}"/>
									<div class="clear"></div>
									<label>{l s='ID Website' mod='netreviews'}</label><input type="text" name="avisverifies_idwebsite_{$lang.iso_code}" id="avisverifies_idwebsite" value="{$current_avisverifies_idwebsite[$lang.iso_code]|escape:'html'}"/>
									<div class="clear"></div>
								</div>
							{/foreach}
						</div>
					{/if}
				</div>
				<hr class="orange">
				<center><input type="submit"  name="submit_configuration" id="submit_configuration" value="{l s='Save' mod='netreviews'}" class="button pointer"></center>
			</form>
		</div>
	</fieldset>
	<br />
	<fieldset class="fieldsetav">
		<legend>{l s='Advanced actions' mod='netreviews'}</legend>
		<div class='config'>
			<form method="post" action="{$url_back|unescape:'htmlall'}" enctype="multipart/form-data">
				<label>{l s='Purge all orders for this shop' mod='netreviews'}&nbsp;({$shop_name})</label>&nbsp;<input type="submit"  name="submit_purge" id="submit_purge" value="{l s='Purged' mod='netreviews'}" class="button pointer"></center>
				<div class="clear"></div>
			</form>
		</div>
	</fieldset>
	<br /><fieldset class="fieldsetav">
		<legend>{l s='Advanced configuration' mod='netreviews'}</legend>
		<div class='config'>
			<form method="post" action="{$url_back|unescape:'htmlall'}" enctype="multipart/form-data">
				<label>{l s='Use the light product widget' mod='netreviews'}</label><input type="checkbox" name="avisverifies_lightwidget" id="avisverifies_lightwidget" {$current_lightwidget_checked|escape:'html'} value="checked"/>
				<div class="clear"></div>
				<label>{l s='Use the multilingual configuration' mod='netreviews'}</label><input type="checkbox" name="avisverifies_multilingue" id="avisverifies_multilingue" {$current_multilingue_checked|escape:'html'} value="checked"/>
				<div class="clear"></div>
				<label>{l s='Activate the google rich snippets on your product page' mod='netreviews'}</label><input type="checkbox" name="avisverifies_displaygooglesnippet" id="avisverifies_displaygooglesnippet" {$current_displaygooglesnippet_checked|escape:'html'} value="checked"/>
				<div class="clear"></div>
				<hr class="orange" >
				<center><input type="submit"  name="submit_advanced" id="submit_advanced" value="{l s='Save' mod='netreviews'}" class="button pointer"></center>
			</form>
		</div>
	</fieldset>
	<br />
	<fieldset class="fieldsetav">
		<legend>{l s='Debug' mod='netreviews'}</legend>
		<div class='config'>
			<div id="debug-part">
				<div id='hidden-part'>
					<ul>
						<li>Reviews : {$debug_nb_reviews|intval}</li>
						<li>Average reviews : {$debug_nb_reviews_average|intval}</li>
						<li>Orders pending : {$debug_nb_orders_not_flagged|intval}</li>
						<li>Orders getted : {$debug_nb_orders_flagged|intval}</li>
						<li>Orders all : {$debug_nb_orders_all|intval}</li>
					</ul>
				</div>
			</div>
		</div>
	</fieldset>
</div>