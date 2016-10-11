<?php
/**
* 2012-2016 NetReviews
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
*  @copyright 2016 NetReviews SAS
*  @version   Release: $Revision: 7.2.1
*  @license   NetReviews
*  @date      20/09/2016
*  International Registered Trademark & Property of NetReviews SAS
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

$specifiqueModel = file_exists(_PS_MODULE_DIR_."netreviews/models/NetReviewsModel-specifique.php");
if ($specifiqueModel) {
    require_once _PS_MODULE_DIR_.'netreviews/models/NetReviewsModel-specifique.php';
} else {
    require_once _PS_MODULE_DIR_.'netreviews/models/NetReviewsModel.php';
}

class NetReviews extends Module
{
    public $_html = null;
    public $iso_lang = null;
    public $id_lang = null;
    public $group_name = null;
    public $stats_product;

    public function __construct()
    {
        $this->name = 'netreviews';
        $this->tab = 'advertising_marketing';
        $this->version = '7.2.1';
        $this->author = 'NetReviews';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Verified Reviews');
        $this->description = $this->l('Collect service and product reviews with Verified Reviews. Display reviews on your shop and win the trust of your visitors, to increase your revenue.');
        $this->module_key = 'd63d28acbac0a249ec17b6394ac5a841';
        if (self::isInstalled($this->name)) {
            $this->id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
        }
        $this->confirmUninstall = sprintf($this->l('Are you sure you want to uninstall %s module?'), $this->displayName);
        $this->initContext();
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        // Create PS configuration variable
        Configuration::updateValue('AV_IDWEBSITE', '');
        Configuration::updateValue('AV_CLESECRETE', '');
        Configuration::updateValue('AV_LIGHTWIDGET', '0');
        Configuration::updateValue('AV_MULTILINGUE', '0');
        Configuration::updateValue('AV_PROCESSINIT', '');
        Configuration::updateValue('AV_ORDERSTATESCHOOSEN', '');
        Configuration::updateValue('AV_DELAY', '');
        Configuration::updateValue('AV_DELAY_PRODUIT', '0');
        Configuration::updateValue('AV_GETPRODREVIEWS', '');
        Configuration::updateValue('AV_DISPLAYPRODREVIEWS', '');
        Configuration::updateValue('AV_CSVFILENAME', 'Export_NetReviews_01-01-1970-default.csv');
        Configuration::updateValue('AV_SCRIPTFLOAT', '');
        Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED', '');
        Configuration::updateValue('AV_SCRIPTFIXE', '');
        Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED', '');
        Configuration::updateValue('AV_URLCERTIFICAT', '');
        Configuration::updateValue('AV_FORBIDDEN_EMAIL', '');
        Configuration::updateValue('AV_CODE_LANG', '');
        Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUIT', '0');
        Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUITLI', '0');
        Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETSITE', '0');
        Configuration::updateValue('AV_NBOFREVIEWS', '20');
        Configuration::updateValue('AV_DISPLAYSTARPLIST', '');
        Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEH3', '');
        Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEA', '');
        Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEHEADERAV', '');

        if (!($query = include dirname(__FILE__).'/sql/install.php')) {
            $this->context->controller->errors[] = sprintf($this->l('SQL ERROR : %s | Query can\'t be executed. Maybe, check SQL user permissions.'), $query);
        }


        return parent::install()
            && $this->registerHook('displayProductTabContent')
            && $this->registerHook('displayProductTab')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayLeftColumnProduct')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayProductListReviews')
            && $this->registerHook('actionValidateOrder');
    }

    public function uninstall()
    {
        //Uninstall NetReviews configurations variable
        $sql = 'SELECT name FROM '._DB_PREFIX_."configuration where name like 'AV_%'";
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                Configuration::deleteByName($row['name']);
            }
        }

        //Uninstall NetReviews Database
        if (!($query = include dirname(__FILE__).'/sql/uninstall.php')) {
            $this->context->controller->errors[] = sprintf($this->l('SQL ERROR : %s | Query can\'t be executed. Maybe, check SQL user permissions.'), $query);
        }


        return parent::uninstall()
            && $this->unregisterHook('displayProductTabContent')
            && $this->unregisterHook('displayProductTab')
            && $this->unregisterHook('displayRightColumnProduct')
            && $this->unregisterHook('displayLeftColumnProduct')
            && $this->unregisterHook('displayHeader')
            && $this->unregisterHook('displayFooter')
            && $this->unregisterHook('displayRightColumn')
            && $this->unregisterHook('displayLeftColumn')
            && $this->unregisterHook('displayProductListReviews')
            && $this->unregisterHook('actionValidateOrder');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        global $currentIndex;
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            Tools::addCSS(($this->_path).'views/css/avisverifies-style-back.css', 'all');
        } else {
            $this->context->controller->addCSS(($this->_path).'views/css/avisverifies-style-back.css', 'all');
        }
        if (!empty($_POST)) {
            $this->postProcess();
        }
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1 &&
        (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->_html .= $this->displayError($this->l('Multistore feature is enabled. Please choose above the store to configure.'));
            return $this->_html;
        }
        $o_av = new NetReviewsModel();
        $nb_reviews = $o_av->getTotalReviews();
        $nb_reviews_average = $o_av->getTotalReviewsAverage();
        $nb_orders = $o_av->getTotalOrders();
        $current_avisverifies_idwebsite = array();
        $current_avisverifies_clesecrete = array();
        $order_statut_list = OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'));
        $current_avisverifies_idwebsite['root'] = Configuration::get('AV_IDWEBSITE');
        $current_avisverifies_clesecrete['root'] = Configuration::get('AV_CLESECRETE');
        $languages = Language::getLanguages(true);
        foreach ($languages as $lang) {
            $this->group_name = $this->getIdConfigurationGroup($lang['iso_code']);
            if (!empty($this->group_name)) {
                if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                    if (!Configuration::get('AV_IDWEBSITE'.$this->group_name, null, null, $this->context->shop->getContextShopID())) {
                        Configuration::updateValue('AV_IDWEBSITE'.$this->group_name, '', false, null, $this->context->shop->getContextShopID());
                    }
                    if (!Configuration::get('AV_CLESECRETE'.$this->group_name, null, null, $this->context->shop->getContextShopID())) {
                        Configuration::updateValue('AV_CLESECRETE'.$this->group_name, '', false, null, $this->context->shop->getContextShopID());
                    }
                    $current_avisverifies_idwebsite[$lang['iso_code']] = Configuration::get('AV_IDWEBSITE'.$this->group_name, null, null, $this->context->shop->getContextShopID());
                    $current_avisverifies_clesecrete[$lang['iso_code']] = Configuration::get('AV_CLESECRETE'.$this->group_name, null, null, $this->context->shop->getContextShopID());
                } else {
                    if (!Configuration::get('AV_IDWEBSITE'.$this->group_name)) {
                        Configuration::updateValue('AV_IDWEBSITE'.$this->group_name, '');
                    }
                    if (!Configuration::get('AV_CLESECRETE'.$this->group_name)) {
                        Configuration::updateValue('AV_CLESECRETE'.$this->group_name, '');
                    }
                    $current_avisverifies_idwebsite[$lang['iso_code']] = Configuration::get('AV_IDWEBSITE'.$this->group_name);
                    $current_avisverifies_clesecrete[$lang['iso_code']] = Configuration::get('AV_CLESECRETE'.$this->group_name);
                }
            } else {
                $current_avisverifies_idwebsite[$lang['iso_code']] = '';
                $current_avisverifies_clesecrete[$lang['iso_code']] = '';
            }
        }
        $this->context->smarty->assign(array(
                'current_avisverifies_urlapi' => Configuration::get('AV_URLAPI'),
                'current_lightwidget_checked' => Configuration::get('AV_LIGHTWIDGET'),
                'current_multilingue_checked' => Configuration::get('AV_MULTILINGUE'),
                'current_starproductlist_checked' => Configuration::get('AV_DISPLAYSTARPLIST'),
                'current_snippets_produit_checked' => Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUIT'),
                'current_snippets_liste_produit_checked' => Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUITLI'),
                'current_snippets_site_checked' => Configuration::get('AV_DISPLAYGOOGLESNIPPETSITE'),
                'avisverifies_nb_reviews' => Configuration::get('AV_NBOFREVIEWS'),
                'avisverifies_hookproducttab_styleh3' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEH3'),
                'avisverifies_hookproducttab_stylea' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEA'),
                'avisverifies_hookproducttab_styleheaderav' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEHEADERAV'),
                'current_avisverifies_idwebsite' => $current_avisverifies_idwebsite,
                'current_avisverifies_clesecrete' => $current_avisverifies_clesecrete,
                'version' => $this->version,
                'order_statut_list' => $order_statut_list,
                'languages' => $languages,
                'debug_nb_reviews' => $nb_reviews['nb_reviews'],
                'debug_nb_reviews_average' => $nb_reviews_average['nb_reviews_average'],
                'debug_nb_orders_flagged' => $nb_orders['flagged']['nb'],
                'debug_nb_orders_not_flagged' => $nb_orders['not_flagged']['nb'],
                'debug_nb_orders_all' => $nb_orders['all']['nb'],
                'av_path' => $this->_path,
                'shop_name' => $this->context->shop->name,
                'url_back' => Tools::safeOutput($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'))
        ));
        $this->_html .= $this->display(__FILE__, '/views/templates/hook/avisverifies-backoffice.tpl');
        return $this->_html;
    }

    /**
     * Save configuration form.
     */
    private function postProcess()
    {
        if (Tools::isSubmit('submit_export')) {
            try {
                $o_av = new NetReviewsModel;
                if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                    //Do not use simple quote for \r\n
                    $header_colums = 'id_order;order_amount;email;lastname;firstname;date_order;delay;id_product;category;description;ean13;upc;mpn;brand;product_url;image_product_url;id_order_state;iso_lang;id_shop'."\r\n";
                    $return_export = $o_av->export($header_colums, $this->context->shop->getContextShopID());
                } else {
                    //Do not use simple quote for \r\n
                    $header_colums = 'id_order;order_amount;email;lastname;firstname;date_order;delay;id_product;category;description;ean13;upc;mpn;brand;product_url;image_product_url;id_order_state;iso_lang'."\r\n";
                    $return_export = $o_av->export($header_colums);
                }
                if (file_exists($return_export[2])) {
                    $this->_html .= $this->displayConfirmation(sprintf($this->l('%s orders have been exported.'), $return_export[1]).'<a href="../modules/netreviews/Export_NetReviews_'.$return_export[0].'"> '.$this->l('Click here to download the file').'</a>');
                } else {
                    $this->_html .= $this->displayError($this->l('Writing on the server is not allowed. Please assign write permissions to the folder netreviews').$return_export[2]);
                }
            } catch (Exception $e) {
                $this->_html .= $this->displayError($e->getMessage());
            }
        }

        if (Tools::isSubmit('submit_configuration')) {
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                if (Configuration::get('AV_MULTILINGUE', null, null, $this->context->shop->getContextShopID()) != 'checked') {
                    Configuration::updateValue('AV_IDWEBSITE', Tools::getValue('avisverifies_idwebsite'), false, null, $this->context->shop->getContextShopID());
                    Configuration::updateValue('AV_CLESECRETE', Tools::getValue('avisverifies_clesecrete'), false, null, $this->context->shop->getContextShopID());
                } else {
                    $sql = '
                    SELECT name FROM '._DB_PREFIX_."configuration
                    where (name like 'AV_GROUP_CONF_%'
                    OR name like 'AV_IDWEBSITE_%'
                    OR name like 'AV_CLESECRETE_%')
                    AND id_shop = ".$this->context->shop->getContextShopID()."
                    ";

                    if ($results = Db::getInstance()->ExecuteS($sql)) {
                        foreach ($results as $row) {
                            Configuration::deleteFromContext($row['name']);
                        }
                    }
                    $languages = Language::getLanguages(true);
                    $this->setIdConfigurationGroup($languages);
                }
            } else {
                if (Configuration::get('AV_MULTILINGUE') != 'checked') {
                    Configuration::updateValue('AV_IDWEBSITE', Tools::getValue('avisverifies_idwebsite'));
                    Configuration::updateValue('AV_CLESECRETE', Tools::getValue('avisverifies_clesecrete'));
                } else {

                    $sql = '
                    SELECT name FROM '._DB_PREFIX_."configuration
                    where name like 'AV_GROUP_CONF_%'
                    OR name like 'AV_IDWEBSITE_%'
                    OR name like 'AV_CLESECRETE_%'
                    ";

                    if ($results = Db::getInstance()->ExecuteS($sql)) {
                        foreach ($results as $row) {
                            Configuration::deleteByName($row['name']);
                        }
                    }
                    $languages = Language::getLanguages(true);
                    $this->setIdConfigurationGroup($languages);
                }
            }
            $this->_html .= $this->displayConfirmation($this->l('The informations have been registered'));
        }

        if (Tools::isSubmit('submit_advanced')) {
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                Configuration::updateValue('AV_LIGHTWIDGET', Tools::getValue('avisverifies_lightwidget'), false, null, $this->context->shop->getContextShopID());
                Configuration::updateValue('AV_MULTILINGUE', Tools::getValue('avisverifies_multilingue'), false, null, $this->context->shop->getContextShopID());
                Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUIT', Tools::getValue('netreviews_snippets_produit'), false, null, $this->context->shop->getContextShopID());
                Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETSITE', Tools::getValue('netreviews_snippets_site'), false, null, $this->context->shop->getContextShopID());
                Configuration::updateValue('AV_NBOFREVIEWS', Tools::getValue('avisverifies_nb_reviews'), false, null, $this->context->shop->getContextShopID());
                Configuration::updateValue('AV_DISPLAYSTARPLIST', Tools::getValue('avisverifies_star_productlist'), false, null, $this->context->shop->getContextShopID());
                if (Tools::getValue('avisverifies_star_productlist')) {
                    Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUITLI', Tools::getValue('netreviews_snippets_liste_produit'), false, null, $this->context->shop->getContextShopID());
                } else {
                    Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUITLI', 0, false, null, $this->context->shop->getContextShopID());
                }
            } else {
                Configuration::updateValue('AV_LIGHTWIDGET', Tools::getValue('avisverifies_lightwidget'));
                Configuration::updateValue('AV_MULTILINGUE', Tools::getValue('avisverifies_multilingue'));
                Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUIT', Tools::getValue('netreviews_snippets_produit'));
                Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETSITE', Tools::getValue('netreviews_snippets_site'));
                Configuration::updateValue('AV_NBOFREVIEWS', Tools::getValue('avisverifies_nb_reviews'));
                Configuration::updateValue('AV_DISPLAYSTARPLIST', Tools::getValue('avisverifies_star_productlist'));
                if (Tools::getValue('avisverifies_star_productlist')) {
                    Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUITLI', Tools::getValue('netreviews_snippets_liste_produit'));
                } else {
                    Configuration::updateValue('AV_DISPLAYGOOGLESNIPPETPRODUITLI', 0);
                }

            }
            $this->_html .= $this->displayConfirmation($this->l('The informations have been registered'));
        }

        if (Tools::isSubmit('submit_visual')) {
            Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEH3', Tools::getValue('avisverifies_hookproducttab_styleh3'), false, null, $this->context->shop->getContextShopID());
            Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEA', Tools::getValue('avisverifies_hookproducttab_stylea'), false, null, $this->context->shop->getContextShopID());
            Configuration::updateValue('AV_HOOKPRODUCTTAB_STYLEHEADERAV', Tools::getValue('avisverifies_hookproducttab_styleheaderav'), false, null, $this->context->shop->getContextShopID());

            $this->_html .= $this->displayConfirmation($this->l('The informations have been registered'));
        }

        if (Tools::isSubmit('submit_purge')) {
            $query_id_shop = "";
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                $query_id_shop = ' AND oav.id_shop = '.(int)$this->context->shop->getContextShopID();
            }

            $query = '  SELECT oav.id_order, o.date_add as date_order,o.id_customer
                        FROM '._DB_PREFIX_.'av_orders oav
                        LEFT JOIN '._DB_PREFIX_.'orders o
                        ON oav.id_order = o.id_order
                        LEFT JOIN '._DB_PREFIX_.'order_history oh
                        ON oh.id_order = o.id_order
                        WHERE (oav.flag_get IS NULL OR oav.flag_get = 0)'
                        .$query_id_shop;

            $orders_list = Db::getInstance()->ExecuteS($query);
            if (!empty($orders_list)) {
                foreach ($orders_list as $order) { /* Set orders as getted */
                    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'av_orders
                                                SET horodate_get = "'.time().'", flag_get = 1
                                                WHERE id_order = '.(int)$order['id_order']);
                }
                $this->_html .= $this->displayConfirmation(sprintf($this->l('The orders has been purged for %s'), $this->context->shop->name));
            } else {
                $this->_html .= $this->displayError(sprintf($this->l('No orders to purged for %s'), $this->context->shop->name));
            }
        }
    }

    /**
     * Return the widget flottant code to the hook header in front office if configurated
     *
     * Case 1: Return widget flottant code if configurated
     * Case 2: Return '' if not configurated
     *
     * @return javascript string in hook header
     */
    public function hookHeader()
    {
        $widget_flottant_code = '';
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $this->context->controller->addCSS(($this->_path).'views/css/avisverifies-style-front.css', 'all');
        $specifiqueCss = file_exists(_PS_MODULE_DIR_."netreviews/views/css/avisverifies-style-front-specifique.css");
        if ($specifiqueCss) {
             $this->context->controller->addCSS(($this->_path).'views/css/avisverifies-style-front-specifique.css', 'all');
        }
        $this->context->controller->addJS(($this->_path).'views/js/avisverifies.js', 'all');
        $specifiqueJs = file_exists(_PS_MODULE_DIR_."views/js/avisverifies-specifique.js");
        if ($specifiqueJs) {
             $this->context->controller->addJS(($this->_path).'views/js/avisverifies-specifique.js', 'all');
        }

        $avisverifies_scriptfloat_allowed = Configuration::get('AV_SCRIPTFLOAT_ALLOWED'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        if (Configuration::get('AV_SCRIPTFLOAT'.$this->group_name, null, null, $this->context->shop->getContextShopID())) {
            $widget_flottant_code .= "\n".Tools::stripslashes(html_entity_decode(Configuration::get('AV_SCRIPTFLOAT'.$this->group_name, null, null, $this->context->shop->getContextShopID())));
        }

        if (Configuration::get('AV_DISPLAYGOOGLESNIPPETSITE') == "2") {

            $rate_site = Configuration::get('AV_RATE_SITE', null, null, $this->context->shop->getContextShopID());
            $nb_site = Configuration::get('AV_AVIS_SITE', null, null, $this->context->shop->getContextShopID());
            $horodate = Configuration::get('AV_HORODATE_LASTGET', null, null, $this->context->shop->getContextShopID());
            $av_idwebsite = Configuration::get('AV_IDWEBSITE', null, null, $this->context->shop->getContextShopID());
            $av_urlcertificat = Configuration::get('AV_URLCERTIFICAT', null, null, $this->context->shop->getContextShopID());
            $name_site = Configuration::get('PS_SHOP_DOMAIN', null, null, $this->context->shop->getContextShopID());


            $url_platform = explode('/', $av_urlcertificat);

            $ex_datas = array();

            if (empty($rate_site) or empty($nb_site) or empty($horodate) or (($horodate+86400) < time())) {

                $datas = Tools::file_get_contents("https://".$url_platform[2]."/avis-clients/widget/".Tools::substr($av_idwebsite, 0, 1)."/".Tools::substr($av_idwebsite, 1, 1)."/".Tools::substr($av_idwebsite, 2, 1)."/".$av_idwebsite."/".$av_idwebsite."_infosite.txt");

                $ex_datas = explode(";", $datas);

                if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                    Configuration::updateValue('AV_HORODATE_LASTGET', time(), false, null, $this->context->shop->getContextShopID());
                    Configuration::updateValue('AV_AVIS_SITE', $ex_datas[0], false, null, $this->context->shop->getContextShopID());
                    Configuration::updateValue('AV_RATE_SITE', $ex_datas[1], false, null, $this->context->shop->getContextShopID());
                } else {
                    Configuration::updateValue('AV_HORODATE_LASTGET', time());
                    Configuration::updateValue('AV_AVIS_SITE', $ex_datas[0]);
                    Configuration::updateValue('AV_RATE_SITE', $ex_datas[1]);
                }

                $nb_site = $ex_datas[0];
                $rate_site = $ex_datas[1];
            }

            $this->context->smarty->assign(array(
                'av_site_rating_avis'                => $nb_site,
                'av_site_rating_rate'                => $rate_site,
                'name_site'                          => $name_site,
                'url_platform'                       => $url_platform[2]
            ));
            if ($avisverifies_scriptfloat_allowed) {
                $this->context->smarty->assign(array(
                    'avisverifies_scriptfloat_allowed'   => $avisverifies_scriptfloat_allowed,
                    'avisverifies_scriptfloat'           => $widget_flottant_code,
                ));
            }
            $tpl = 'header_av_site';
            return $this->displayTemplate($tpl);
        } else {
            if ($avisverifies_scriptfloat_allowed) {
                return $widget_flottant_code;
            } else {
                return '';
            }
        }
    }

    /**
     * Return the rich snippet code to the hook footer in front office if configurated
     *
     * Case 1: Return rich snippet code if configurated
     * Case 2: Return '' if not configurated or if the product are no reviews
     *
     * @return tpl string in hook footer
     */
    public function hookFooter()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $id_product = (int)Tools::getValue('id_product');

        if (empty($id_product)) {
            if (Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUITLI')== "1" && $this->context->controller->php_self == 'category') {
                // find the list of the id in a category
                $id_category=(int)Tools::getValue('id_category');
                $cat = new Category($id_category, $this->context->language->id);
                $nom_category=$cat->name;

                $shop_name = Configuration::get('PS_SHOP_NAME');
                $sql = 'SELECT * FROM '._DB_PREFIX_.'category_product where id_category="'.$id_category.'"';
                $results = Db::getInstance()->ExecuteS($sql);

                $reviews_list = array(); //Create array with all reviews data
                $stats_product = array('nb_reviews'=>0,'somme'=>0);  // predefine the stats of the reviews, contains the number and the total of the rates
                foreach ($results as $row) {

                    $id_product=(int)$row['id_product'];
                    $o_av = new NetReviewsModel();

                    $reviews = $o_av->getProductReviews($id_product, $this->group_name, $this->context->shop->getContextShopID(), false, 0);

                    $my_review = array(); //Create array with each reviews data

                    foreach ($reviews as $review) {
                        // calculate the number of review and the total of the rates
                        $stats_product['nb_reviews']++;
                        $stats_product['somme'] = $stats_product['somme'] + $review['rate'];
                    }
                }
                // calcul de la moyen
                $stats_product['rate'] = $stats_product['somme'] / $stats_product['nb_reviews'];

                $this->context->smarty->assign(array(
                    'count_reviews' => $stats_product['nb_reviews'],
                    'average_rate' => round($stats_product['rate'], 1),
                    'av_rate_percent' => ($stats_product['rate']*20),
                    'nom_category'=>$nom_category
                ));

                $tpl = 'avisverifies-category-snippets';
                return $this->displayTemplate($tpl);



            } else if (Configuration::get('AV_DISPLAYGOOGLESNIPPETSITE') == "1") {
                $rate_site = Configuration::get('AV_RATE_SITE', null, null, $this->context->shop->getContextShopID());
                $nb_site = Configuration::get('AV_AVIS_SITE', null, null, $this->context->shop->getContextShopID());
                $horodate = Configuration::get('AV_HORODATE_LASTGET', null, null, $this->context->shop->getContextShopID());
                $av_idwebsite = Configuration::get('AV_IDWEBSITE', null, null, $this->context->shop->getContextShopID());
                $av_urlcertificat = Configuration::get('AV_URLCERTIFICAT', null, null, $this->context->shop->getContextShopID());
                $av_code_lang = Configuration::get('AV_CODE_LANG', null, null, $this->context->shop->getContextShopID());
                $name_site = Configuration::get('PS_SHOP_DOMAIN', null, null, $this->context->shop->getContextShopID());

                $url_platform = explode('/', $av_urlcertificat);
                $code_lang = explode('_', $av_code_lang);


                $ex_datas = array();

                if (empty($rate_site) or empty($nb_site) or empty($horodate) or (($horodate+86400) < time())) {

                    $datas = Tools::file_get_contents("https://".$url_platform[2]."/avis-clients/widget/".Tools::substr($av_idwebsite, 0, 1)."/".Tools::substr($av_idwebsite, 1, 1)."/".Tools::substr($av_idwebsite, 2, 1)."/".$av_idwebsite."/".$av_idwebsite."_infosite.txt");


                    $ex_datas = explode(";", $datas);

                    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                        Configuration::updateValue('AV_HORODATE_LASTGET', time(), false, null, $this->context->shop->getContextShopID());
                        Configuration::updateValue('AV_AVIS_SITE', $ex_datas[0], false, null, $this->context->shop->getContextShopID());
                        Configuration::updateValue('AV_RATE_SITE', $ex_datas[1], false, null, $this->context->shop->getContextShopID());
                    } else {
                        Configuration::updateValue('AV_HORODATE_LASTGET', time());
                        Configuration::updateValue('AV_AVIS_SITE', $ex_datas[0]);
                        Configuration::updateValue('AV_RATE_SITE', $ex_datas[1]);
                    }

                    $nb_site = $ex_datas[0];
                    $rate_site = $ex_datas[1];
                }

                $this->context->smarty->assign(array(
                    'av_site_rating_avis'   => $nb_site,
                    'av_site_rating_rate'   => $rate_site,
                    'name_site'             => $name_site,
                    'url_platform'          => $url_platform[2]


                ));

                $tpl = 'footer_av_site';
                return $this->displayTemplate($tpl);

            } else {
                return '';
            }
        } else {
            if (Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUIT') == "1") {
                $o_av = new NetReviewsModel();
                $stats_product = (!isset($this->stats_product) || empty($this->stats_product)) ?
                                    $o_av->getStatsProduct($id_product, $this->group_name, $this->context->shop->getContextShopID()) :
                                    $this->stats_product;
                if ($stats_product['nb_reviews'] == 0) {
                    return '';
                }
                $lang_id = (int)$this->context->language->id;
                if (empty($lang_id)) {
                    $lang_id = 1;
                }
                $product = new Product((int)$id_product);
                $link = new LinkCore();
                $a_image = Image::getCover($id_product);
                $this->context->smarty->assign(array(
                    'count_reviews' => $stats_product['nb_reviews'],
                    'average_rate' => round($stats_product['rate'], 1),
                    'average_rate_percent' => $stats_product['rate'] * 20,
                    'product_name' => $this->getProductName($id_product, $lang_id),
                    'product_description' => $product->description_short[$lang_id],
                    'product_price' => $product->getPrice(true, null, 2),
                    'product_quantity' => $product->quantity,
                    'url_image' =>  !empty($a_image)? $link->getImageLink($product->link_rewrite[(int)Configuration::get('PS_LANG_DEFAULT')], $id_product.'-'.$a_image['id_image']): '',
                ));
                $tpl = 'footer_av';
                return $this->displayTemplate($tpl);

            } else {
                return '';
            }
        }
    }

    public function hookProductTab()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $display_prod_reviews = Configuration::get('AV_DISPLAYPRODREVIEWS'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        $o_av = new NetReviewsModel();
        $this->stats_product = $o_av->getStatsProduct((int)Tools::getValue('id_product'), $this->group_name, $this->context->shop->getContextShopID());
        if ($this->stats_product['nb_reviews'] < 1 || $display_prod_reviews != 'yes') {
            return ''; //Si Aucun avis, on retourne vide
        }
        $this->context->smarty->assign(
            array(
                'count_reviews' => $this->stats_product['nb_reviews'],
                'styleH3' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEH3'.$this->group_name, null, null, $this->context->shop->getContextShopID()), // style additionnel contenu dans le paramétrage visuel avancé
                'styleA' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEA'.$this->group_name, null, null, $this->context->shop->getContextShopID())   // style additionnel contenu dans le paramétrage visuel avancé
            )
        );

        $tpl = "avisverifies-tab";

        return $this->displayTemplate($tpl);
    }

    /* WARNING : Modifications below need to be copy in ajax-load.php*/
    public function hookProductTabContent()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $display_prod_reviews = configuration::get('AV_DISPLAYPRODREVIEWS'.$this->group_name, null, null, $this->context->shop->getContextShopID());
        $url_certificat = Configuration::get('AV_URLCERTIFICAT'.$this->group_name, null, null, $this->context->shop->getContextShopID());
        $avisverifies_nb_reviews = Configuration::get('AV_NBOFREVIEWS'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        $shop_name = Configuration::get('PS_SHOP_NAME');
        $id_product = (int)Tools::getValue('id_product');
        $o_av = new NetReviewsModel();
        $stats_product = (!isset($this->stats_product) || empty($this->stats_product)) ?
                            $o_av->getStatsProduct($id_product, $this->group_name, $this->context->shop->getContextShopID())
                            : $this->stats_product;
        if ($stats_product['nb_reviews'] < 1 || $display_prod_reviews != 'yes') {
            return ''; /* if no reviews, return empty */
        }
        $reviews = $o_av->getProductReviews($id_product, $this->group_name, $this->context->shop->getContextShopID(), false, 0);
        $reviews_list = array(); //Create array with all reviews data
        $my_review = array(); //Create array with each reviews data
        foreach ($reviews as $review) {

            //Create variable for template engine
            $my_review['ref_produit'] = $review['ref_product'];
            $my_review['id_product_av'] = $review['id_product_av'];
            $my_review['rate'] = $review['rate'];
            $my_review['avis'] = html_entity_decode(urldecode($review['review']));
            if (Tools::strlen($review['horodate'])=='10') {
                $date = new DateTime();
                $date->setTimestamp($review['horodate']);
                $my_review['horodate'] = $date->format('d/m/Y') ;
            } else {
                $my_review['horodate'] = date('d/m/Y', strtotime($review['horodate']));
            }
            $my_review['customer_name'] = urldecode($review['customer_name']);
            $my_review['discussion'] = '';
            $unserialized_discussion = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($review['discussion']), true);
            if ($unserialized_discussion) {
                foreach ($unserialized_discussion as $k_discussion => $each_discussion) {
                    if (Tools::strlen($each_discussion['horodate'])=='10') {
                        $date = new DateTime();
                        $date->setTimestamp($each_discussion['horodate']);
                        $my_review['discussion'][$k_discussion]['horodate'] = $date->format('d/m/Y') ;
                    } else {
                        $my_review['discussion'][$k_discussion]['horodate'] = date('d/m/Y', strtotime($each_discussion['horodate']));
                    }
                    $my_review['discussion'][$k_discussion]['commentaire'] = $each_discussion['commentaire'];
                    if ($each_discussion['origine'] == 'ecommercant') {
                        $my_review['discussion'][$k_discussion]['origine'] = $shop_name;
                    } else if ($each_discussion['origine'] == 'internaute') {
                        $my_review['discussion'][$k_discussion]['origine'] = $my_review['customer_name'];
                    } else {
                        $my_review['discussion'][$k_discussion]['origine'] = $this->l('Moderator');
                    }
                }
            }
            array_push($reviews_list, $my_review);
        }

        $this->context->controller->pagination((int)$stats_product['nb_reviews']);
        $this->context->smarty->assign(array(
            'current_url' =>  $_SERVER['REQUEST_URI'],
            'id_shop' => $this->context->shop->getContextShopID(),
            'nom_group' => (!empty($this->group_name))?"'".$this->group_name."'":null,
            'reviews' => $reviews_list,
            'count_reviews' => $stats_product['nb_reviews'],
            'average_rate' => round($stats_product['rate'], 1),
            'average_rate_percent' => $stats_product['rate'] * 20,
            'is_https' => (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on' ? 1 : 0),
            'url_certificat' => $url_certificat,
            'avisverifies_nb_reviews' => ($avisverifies_nb_reviews)? $avisverifies_nb_reviews : "",
            'styleHeaderAV' => Configuration::get('AV_HOOKPRODUCTTAB_STYLEHEADERAV'.$this->group_name, null, null, $this->context->shop->getContextShopID()), // style additionnel contenu dans le paramétrage visuel avancé

        ));


        $tpl = "avisverifies-tab-content";

        return $this->displayTemplate($tpl);
    }

    public function hookActionValidateOrder($params)
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

            //$process_init = Configuration::get('AV_PROCESSINIT'.$this->group_name);
            $id_website = configuration::get('AV_IDWEBSITE'.$this->group_name, null, null, $this->context->shop->getContextShopID());
            $secret_key = configuration::get('AV_CLESECRETE'.$this->group_name, null, null, $this->context->shop->getContextShopID());
            $code_lang = configuration::get('AV_CODE_LANG'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        if (empty($id_website) || empty($secret_key)) {
            return;
        }
        $code_lang = (!empty($code_lang)) ? $code_lang : 'undef';
        $o_order = $params['order'];
        $id_order = $o_order->id;
        if (!empty($o_order) && !empty($id_order)) {
            $o_av = new NetReviewsModel();
            $o_av->id_order = (int)$id_order;
            if (!empty($o_order->id_shop)) {
                $o_av->id_shop = $o_order->id_shop;
            }
            $o_av->iso_lang = pSQL(Language::getIsoById($o_order->id_lang));
            $o_av->saveOrderToRequest();
            $order_total = ($o_order->total_paid) ? (100 * $o_order->total_paid) : 0;
            return "<img height='1' hspace='0'
            src='//www.netreviews.eu/index.php?action=act_order&idWebsite=$id_website&langue=$code_lang&refCommande=$id_order&montant=$order_total' />";
        }
    }

    public function hookRightColumn()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

            $av_scriptfixe_allowed = Configuration::get('AV_SCRIPTFIXE_ALLOWED'.$this->group_name, null, null, $this->context->shop->getContextShopID());
            $av_scriptfixe_position = Configuration::get('AV_SCRIPTFIXE_POSITION'.$this->group_name, null, null, $this->context->shop->getContextShopID());
            $av_scriptfixe = Configuration::get('AV_SCRIPTFIXE'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        if ($av_scriptfixe_allowed != 'yes' || $av_scriptfixe_position != 'right') {
            return;
        }
        if ($av_scriptfixe) {
            return "\n\n<div align='center'>".Tools::stripslashes(html_entity_decode($av_scriptfixe))."</div><br clear='left'/><br />";
        }
    }

    public function hookLeftColumn()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $av_scriptfixe_allowed = Configuration::get('AV_SCRIPTFIXE_ALLOWED'.$this->group_name, null, null, $this->context->shop->getContextShopID());
        $av_scriptfixe_position = Configuration::get('AV_SCRIPTFIXE_POSITION'.$this->group_name, null, null, $this->context->shop->getContextShopID());
        $av_scriptfixe = Configuration::get('AV_SCRIPTFIXE'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        if ($av_scriptfixe_allowed != 'yes' || $av_scriptfixe_position != 'left') {
            return;
        }
        if ($av_scriptfixe) {
            return "\n\n<div align='center'>".Tools::stripslashes(html_entity_decode($av_scriptfixe))."</div><br clear='left'/><br />";
        }
    }

    public function hookExtraRight()
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }

        $display_prod_reviews = configuration::get('AV_DISPLAYPRODREVIEWS'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        $id_product = (int)Tools::getValue('id_product');
        $o = new NetReviewsModel();
        $reviews = $o->getStatsProduct($id_product, $this->group_name, $this->context->shop->getContextShopID());
        if ($reviews['nb_reviews'] < 1 || $display_prod_reviews != 'yes') {
            return ''; //Si Aucun avis, on retourne vide
        }

        $lang_id = (int)$this->context->language->id;
        if (empty($lang_id)) {
            $lang_id = 1;
        }

        $percent = round($reviews['rate'] * 20) ;
        $this->context->smarty->assign(array(
            'av_nb_reviews' => $reviews['nb_reviews'],
            'av_rate' =>  $reviews['rate'],
            'av_rate_percent' =>  ($percent) ? $percent : 100,
            'average_rate' => round($reviews['rate'], 1),
            'product_name' => $this->getProductName($id_product, $lang_id),
        ));

        if (Configuration::get('AV_LIGHTWIDGET') == 'checked' && Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUIT')== "2") {
            $tpl = 'avisverifies-extraright-light-snippets';
        } elseif (Configuration::get('AV_LIGHTWIDGET') == 'checked') {
            $tpl = 'avisverifies-extraright-light';
        } elseif (Configuration::get('AV_DISPLAYGOOGLESNIPPETPRODUIT')== "2") {
            $tpl = 'avisverifies-extraright-snippets';
        } else {
            $tpl = 'avisverifies-extraright';
        }

        return $this->displayTemplate($tpl);
    }

    public function hookDisplayProductListReviews($params)
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked') {
            $this->id_lang = $this->context->language->id;
            $this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
            $this->group_name = $this->getIdConfigurationGroup($this->iso_lang);
        }
        $id_product = (int)$params['product']['id_product'];

        $avisverifies_display_stars = Configuration::get('AV_DISPLAYSTARPLIST'.$this->group_name, null, null, $this->context->shop->getContextShopID());

        if (!isset($id_product) || empty($id_product) || !isset($avisverifies_display_stars) || empty($avisverifies_display_stars)) {
            return '';
        }

        $o_av = new NetReviewsModel();

        $stats_product = (!isset($this->stats_product) || empty($this->stats_product)) ? $o_av->getStatsProduct($id_product, $this->group_name, $this->context->shop->getContextShopID()) : $this->stats_product;

        if ($stats_product['nb_reviews'] == 0) {
            return '';
        }

        $percent = round($stats_product['rate'] * 20) ;

        $lang_id = (int)$this->context->language->id;
        if (empty($lang_id)) {
            $lang_id = 1;
        }

        $this->context->smarty->assign(array(
            'av_nb_reviews' => $stats_product['nb_reviews'],
            'av_rate' =>  $stats_product['rate'],
            'av_rate_percent' =>  ($percent) ? $percent : 100,
            'link_product' => $params['product']['link'],
            'average_rate' => round($stats_product['rate'], 1),
            'product_name' => $this->getProductName($id_product, $lang_id)
        ));

        $tpl = 'avisverifies-categorystars';

        return $this->displayTemplate($tpl);
    }

    private function displayTemplate($tpl)
    {
        $specifique = file_exists(_PS_MODULE_DIR_."netreviews/views/templates/hook/$tpl-specifique.tpl");
        if ($specifique) {
            return  ($this->display(__FILE__, "/views/templates/hook/$tpl-specifique.tpl"));
        } else {
            return  ($this->display(__FILE__, "/views/templates/hook/$tpl.tpl"));
        }
    }

    /**
     * initContext for the retrocompatibility from previous versions of PS
     */
    private function initContext()
    {
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        } else {
            global $smarty, $cookie;
            $this->context = new StdClass();
            $this->context->smarty = $smarty;
            $this->context->cookie = $cookie;
        }
    }

    private function getProductName($id_product, $id_lang)
    {
        // creates the query
        $query = 'SELECT DISTINCT pl.name as name
                    FROM '._DB_PREFIX_.'product_lang pl
                    WHERE pl.id_product = '.(int)$id_product.'
                    AND pl.id_lang = '.(int)$id_lang.'
                    And id_shop = '.$this->context->shop->getContextShopID();

        return Db::getInstance()->getValue($query);
    }

    private function getIdConfigurationGroup($lang_iso)
    {
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration where name like 'AV_GROUP_CONF_%' And id_shop = '".$this->context->shop->getContextShopID()."'";
        } else {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration where name like 'AV_GROUP_CONF_%'";
        }
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                    $vconf = unserialize(Configuration::get($row['name'], null, null, $this->context->shop->getContextShopID()));
                } else {
                    $vconf = unserialize(Configuration::get($row['name']));
                }
                if ($vconf && in_array($lang_iso, $vconf)) {
                    return '_'.Tools::substr($row['name'], 14);
                }
            }
        }
    }

    private function setIdConfigurationGroup($languages = null, $i = 0)
    {
        if (empty($languages)) {
            return;
        }
        reset($languages);
        $id_langue_curent = key($languages);
        $lang = $languages[$id_langue_curent];
        $id_website_current = Tools::getValue('avisverifies_idwebsite_'.$lang['iso_code']);
        $cle_secrete_current = Tools::getValue('avisverifies_clesecrete_'.$lang['iso_code']);
        if (empty($id_website_current) || empty($cle_secrete_current)) {
            unset($languages[$id_langue_curent]);
            return $this->setIdConfigurationGroup($languages, $i);
        } else {
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {
                $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSql($id_website_current)."'
                AND name like 'AV_IDWEBSITE_%' And id_shop = '".$this->context->shop->getContextShopID()."'";
                if ($row = Db::getInstance()->getRow($sql)) {
                    if (Configuration::get('AV_CLESECRETE_'.Tools::substr($row['name'], 13), null, null, $this->context->shop->getContextShopID()) != $cle_secrete_current) {
                        $this->context->controller->errors[] = sprintf($this->l('PARAM ERROR: please check your multilingual configuration for the id_website "%s" at language "%s"'), $id_website_current, $lang['name']);
                        unset($languages[$id_langue_curent]);
                        return $this->setIdConfigurationGroup($languages, $i);
                    }
                }
            } else {
                $sql = 'SELECT name FROM '._DB_PREFIX_."configuration WHERE value = '".pSql($id_website_current)."' AND name like 'AV_IDWEBSITE_%'";
                if ($row = Db::getInstance()->getRow($sql)) {
                    if (Configuration::get('AV_CLESECRETE_'.Tools::substr($row['name'], 13)) != $cle_secrete_current) {
                        $this->context->controller->errors[] = sprintf($this->l('PARAM ERROR: please check your multilingual configuration for the id_website "%s" at language "%s"'), $id_website_current, $lang['name']);
                        unset($languages[$id_langue_curent]);
                        return $this->setIdConfigurationGroup($languages, $i);
                    }
                }
            }
            $group = array();
            array_push($group, $lang['iso_code']);
            unset($languages[$id_langue_curent]);
            foreach ($languages as $id1 => $lang1) {
                if ($id_website_current == Tools::getValue('avisverifies_idwebsite_'.$lang1['iso_code'])
                && $cle_secrete_current == Tools::getValue('avisverifies_clesecrete_'.$lang1['iso_code'])) {
                    array_push($group, $lang1['iso_code']);
                    unset($languages[$id1]);
                }
            }
            // Create PS configuration variable
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1) {

                if (!Configuration::get('AV_IDWEBSITE_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_IDWEBSITE_'.$i, Tools::getValue('avisverifies_idwebsite_'.$lang['iso_code']), false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_CLESECRETE_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_CLESECRETE_'.$i, Tools::getValue('avisverifies_clesecrete_'.$lang['iso_code']), false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_GROUP_CONF_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_GROUP_CONF_'.$i, serialize($group), false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_LIGHTWIDGET_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_LIGHTWIDGET_'.$i, '0', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_PROCESSINIT_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_PROCESSINIT_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_ORDERSTATESCHOOSEN_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_ORDERSTATESCHOOSEN_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_DELAY_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_DELAY_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_DELAY_PRODUIT_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_DELAY_PRODUIT_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_GETPRODREVIEWS_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_GETPRODREVIEWS_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_DISPLAYPRODREVIEWS_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_DISPLAYPRODREVIEWS_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_SCRIPTFLOAT_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_SCRIPTFLOAT_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_SCRIPTFLOAT_ALLOWED_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_SCRIPTFIXE_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_SCRIPTFIXE_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_SCRIPTFIXE_ALLOWED_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_URLCERTIFICAT_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_URLCERTIFICAT_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_FORBIDDEN_EMAIL_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_FORBIDDEN_EMAIL_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

                if (!Configuration::get('AV_CODE_LANG_'.$i, null, null, $this->context->shop->getContextShopID())) {
                    Configuration::updateValue('AV_CODE_LANG_'.$i, '', false, null, $this->context->shop->getContextShopID());
                }

            } else {

                if (!Configuration::get('AV_IDWEBSITE_'.$i)) {
                    Configuration::updateValue('AV_IDWEBSITE_'.$i, Tools::getValue('avisverifies_idwebsite_'.$lang['iso_code']));
                }

                if (!Configuration::get('AV_CLESECRETE_'.$i)) {
                    Configuration::updateValue('AV_CLESECRETE_'.$i, Tools::getValue('avisverifies_clesecrete_'.$lang['iso_code']));
                }

                if (!Configuration::get('AV_GROUP_CONF_'.$i)) {
                    Configuration::updateValue('AV_GROUP_CONF_'.$i, serialize($group));
                }

                if (!Configuration::get('AV_LIGHTWIDGET_'.$i)) {
                    Configuration::updateValue('AV_LIGHTWIDGET_'.$i, '0');
                }

                if (!Configuration::get('AV_PROCESSINIT_'.$i)) {
                    Configuration::updateValue('AV_PROCESSINIT_'.$i, '');
                }

                if (!Configuration::get('AV_ORDERSTATESCHOOSEN_'.$i)) {
                    Configuration::updateValue('AV_ORDERSTATESCHOOSEN_'.$i, '');
                }

                if (!Configuration::get('AV_DELAY_'.$i)) {
                    Configuration::updateValue('AV_DELAY_'.$i, '');
                }

                if (!Configuration::get('AV_DELAY_PRODUIT_'.$i)) {
                    Configuration::updateValue('AV_DELAY_PRODUIT_'.$i, '');
                }

                if (!Configuration::get('AV_GETPRODREVIEWS_'.$i)) {
                    Configuration::updateValue('AV_GETPRODREVIEWS_'.$i, '');
                }

                if (!Configuration::get('AV_DISPLAYPRODREVIEWS_'.$i)) {
                    Configuration::updateValue('AV_DISPLAYPRODREVIEWS_'.$i, '');
                }

                if (!Configuration::get('AV_SCRIPTFLOAT_'.$i)) {
                    Configuration::updateValue('AV_SCRIPTFLOAT_'.$i, '');
                }

                if (!Configuration::get('AV_SCRIPTFLOAT_ALLOWED_'.$i)) {
                    Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED_'.$i, '');
                }

                if (!Configuration::get('AV_SCRIPTFIXE_'.$i)) {
                    Configuration::updateValue('AV_SCRIPTFIXE_'.$i, '');
                }

                if (!Configuration::get('AV_SCRIPTFIXE_ALLOWED_'.$i)) {
                    Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED_'.$i, '');
                }

                if (!Configuration::get('AV_URLCERTIFICAT_'.$i)) {
                    Configuration::updateValue('AV_URLCERTIFICAT_'.$i, '');
                }

                if (!Configuration::get('AV_FORBIDDEN_EMAIL_'.$i)) {
                    Configuration::updateValue('AV_FORBIDDEN_EMAIL_'.$i, '');
                }

                if (!Configuration::get('AV_CODE_LANG_'.$i)) {
                    Configuration::updateValue('AV_CODE_LANG_'.$i, '');
                }
            }
            $i++;
            return $this->setIdConfigurationGroup($languages, $i);
        }
    }
}
