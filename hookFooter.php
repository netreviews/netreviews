<?php
/**
* 2012-2015 NetReviews
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2015 NetReviews SAS
*  @version   Release: $Revision: 7.1.3
*  @license   NetReviews
*  @date      13/02/2015
*  International Registered Trademark & Property of NetReviews SAS
*/

global $smarty, $cookie;
$iso_lang = null;
$id_lang = null;
$group_name = null;
if (Configuration::get('AV_MULTILINGUE') == 'checked')
{
	$id_lang = $this->context->language->id;
	$iso_lang = pSQL(Language::getIsoById($id_lang));
	$group_name = $this->getIdConfigurationGroup($iso_lang);
}
$id_product = (int)Tools::getValue('id_product');
if (empty($id_product))
	return '';
$o_av = new NetReviewsModel();
$stats_product = (!isset($this->stats_product) || empty($this->stats_product)) ?
					$o_av->getStatsProduct($id_product, $group_name, $this->context->shop->getContextShopID()) :
					$this->stats_product;
if ($stats_product['nb_reviews'] == 0)
	return '';
$lang_id = (int)$this->context->language->id;
if (empty($lang_id))
	$lang_id = 1;
$product = new Product((int)$id_product);
$link = new LinkCore();
$a_image = Image::getCover($id_product);
$smarty->assign(array(
	'count_reviews' => $stats_product['nb_reviews'],
	'average_rate' => round($stats_product['rate'], 1),
	'average_rate_percent' => $stats_product['rate'] * 20,
	'product_name' => $this->getProductName($id_product, $lang_id),
	'product_description' => $product->description_short[$lang_id],
	'product_price' => $product->getPrice(true, null, 2),
	'product_quantity' => $product->quantity,
	'url_image' =>  !empty($a_image)? $link->getImageLink($product->link_rewrite, $id_product.'-'.$a_image['id_image']): '',
));
if (_PS_VERSION_ < 1.5)
	$return = $this->display(__FILE__, 'views/templates/hook/footer_av.tpl');
else
	$return = $this->display(__FILE__, 'footer_av.tpl');
if (isset($return))
	return $return;
else
	return '';
?>