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
* avisverifiesApi.php file used to execute query from AvisVerifies plateform
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2015 NetReviews SAS
*  @version   Release: $Revision: 7.1.31
*  @license   NetReviews
*  @date      13/02/2015
*  @category  api
*  International Registered Trademark & Property of NetReviews SAS
*/

require('../../config/config.inc.php');
require('../../init.php');
include('netreviews.php');
$post_data = $_POST;
/*Check data received - Exit if no data received*/
if (!isset($post_data) || empty($post_data))
{
    $reponse = array();
    $reponse['debug'] = 'No POST DATA received';
    $reponse['return'] = 2;
    echo NetReviewsModel::acEncodeBase64(Tools::jsonEncode($reponse));
    exit;
}
/*Check module state | EXIT if error returned*/
$is_active_var = isActiveModule($post_data);
if ($is_active_var['return'] != 1)
{
    echo NetReviewsModel::acEncodeBase64(Tools::jsonEncode($is_active_var));
    exit;
}
/*Check module customer identification | EXIT if error returned*/
$check_security_var = checkSecurityData($post_data);
if ($check_security_var['return'] != 1)
{
    echo NetReviewsModel::acEncodeBase64(Tools::jsonEncode($check_security_var));
    exit;
}
/*############ START ############*/
/*Switch between each query allowed and sent by NetReviews*/
$to_reply = '';
switch ($post_data['query'])
{
    case 'isActiveModule':
        $to_reply = isActiveModule($post_data);
        break;
    case 'setModuleConfiguration' :
        $to_reply = setModuleConfiguration($post_data);
        break;
    case 'getModuleAndSiteConfiguration' :
        $to_reply = getModuleAndSiteConfiguration($post_data);
        break;
    case 'getOrders' :
        $to_reply = getOrders($post_data);
        break;
    case 'setProductsReviews' :
        $to_reply = setProductsReviews($post_data);
        break;
    case 'truncateTables' :
        $to_reply = truncateTables($post_data);
        break;
    case 'getUrlProducts' :
        $to_reply = getUrlProducts($post_data);
        break;
    case 'getOrderHistoryOn' :
        $to_reply = getOrderHistoryOn($post_data);
        break;
    case 'getCountOrder' :
        $to_reply = getCountOrder($post_data);
        break;
    default:
        break;
}
/*Displaying functions returns to NetReviews */
echo NetReviewsModel::acEncodeBase64(Tools::jsonEncode($to_reply));
/**
 * Check ID Api Customer
 * Every sent query depends on the return result of this function
 * @param $post_data
 * @return $reponse : error code + error
 */
function checkSecurityData(&$post_data)
{
    $reponse = array();
    /*get($key, $id_lang = null, $id_shop_group = null, $id_shop = null)*/
    $uns_msg = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    if (empty($uns_msg))
    {
        $reponse['debug'] = 'empty message';
        $reponse['return'] = 2;
        $reponse['query'] = 'checkSecurityData';
        /* Set query name because this query is called locally */
        return $reponse;
    }
    if (version_compare(_PS_VERSION_, '1.5', '>=') && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1)
    {
        if (!isset($uns_msg['id_shop']) || empty($uns_msg['id_shop']))
        {
            $reponse['debug'] = $uns_msg;
            $reponse['return'] = 2;
            $reponse['query'] = 'checkSecurityData';
            /* Set query name because this query is called locally */
            return $reponse;
        }
    }
    if (!empty($uns_msg['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
        {
            $sql = 'SELECT name
            FROM '._DB_PREFIX_."configuration
            WHERE value = '".pSQL($uns_msg['idWebsite'])."'
            AND name like 'AV_IDWEBSITE_%'
            AND id_shop = ".(int)$uns_msg['id_shop'];
            $row = Db::getInstance()->getRow($sql);
            $group_name = '_'.Tools::substr($row['name'], 13);
            $local_id_website = Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $uns_msg['id_shop']);
            $local_secure_key = Configuration::get('AV_CLESECRETE'.$group_name, null, null, $uns_msg['id_shop']);
        }
        else
        {
            $local_id_website = Configuration::get('AV_IDWEBSITE', null, null, $uns_msg['id_shop']);
            $local_secure_key = Configuration::get('AV_CLESECRETE', null, null, $uns_msg['id_shop']);
        }
        /*Check if ID clustomer are set locally*/
        $reponse['query'] = 'checkSecurityData';
        if (!$local_id_website || !$local_secure_key)
        {
            $reponse['debug'] = 'Identifiants clients non renseignés sur le module';
            $reponse['message'] = 'Identifiants clients non renseignés sur le module';
            $reponse['return'] = 3;

            /* Set query name because this query is called locally */
            return $reponse;
        }
        //Check if sent Idwebsite if the same as local
        elseif ($uns_msg['idWebsite'] != $local_id_website)
        {
            $reponse['message'] = 'Clé Website incorrecte';
            $reponse['debug'] = 'Clé Website incorrecte';
            $reponse['return'] = 4;
            return $reponse;
        }
        //Check if sent sign if the same as local
        elseif (SHA1($post_data['query'].$local_id_website.$local_secure_key) != $uns_msg['sign'])
        {
            $reponse['message'] = 'La signature est incorrecte';
            $reponse['debug'] = 'La signature est incorrecte';
            $reponse['return'] = 5;
            return $reponse;
        }
        else
        {
            $reponse['message'] = 'Identifiants Client Ok';
            $reponse['debug'] = 'Identifiants Client Ok';
            $reponse['return'] = 1;
            $reponse['sign'] = SHA1($post_data['query'].$local_id_website.$local_secure_key);
            return $reponse;
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
            FROM '._DB_PREFIX_."configuration
            WHERE value = '".pSQL($uns_msg['idWebsite'])."'
            AND name like 'AV_IDWEBSITE_%'
            AND id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $local_id_website = Configuration::get('AV_IDWEBSITE'.$group_name);
            $local_secure_key = Configuration::get('AV_CLESECRETE'.$group_name);
        }
        else
        {
            $local_id_website = Configuration::get('AV_IDWEBSITE');
            $local_secure_key = Configuration::get('AV_CLESECRETE');
        }
        /*Check if ID clustomer are set locally*/
        if (!$local_id_website || !$local_secure_key)
        {
            $reponse['debug'] = 'Identifiants clients non renseignés sur le module';
            $reponse['message'] = 'Identifiants clients non renseignés sur le module';
            $reponse['return'] = 3;
            $reponse['query'] = 'checkSecurityData';
            /* Set query name because this query is called locally */
            return $reponse;
        }
        //Check if sent Idwebsite if the same as local
        elseif ($uns_msg['idWebsite'] != $local_id_website)
        {
            $reponse['message'] = 'Clé Website incorrecte';
            $reponse['debug'] = 'Clé Website incorrecte';
            $reponse['return'] = 4;
            $reponse['query'] = 'checkSecurityData';
            return $reponse;
        }
        //Check if sent sign if the same as local
        elseif (SHA1($post_data['query'].$local_id_website.$local_secure_key) != $uns_msg['sign'])
        {
            $reponse['message'] = 'La signature est incorrecte';
            $reponse['debug'] = 'La signature est incorrecte';
            $reponse['return'] = 5;
            $reponse['query'] = 'checkSecurityData';
            return $reponse;
        }
        $reponse['message'] = 'Identifiants Client Ok';
        $reponse['debug'] = 'Identifiants Client Ok';
        $reponse['return'] = 1;
        $reponse['sign'] = SHA1($post_data['query'].$local_id_website.$local_secure_key);
        $reponse['query'] = 'checkSecurityData';
        return $reponse;
    }
}
/* ############ END ############*/
/**############ FUNCTION ############ **/
/**
 * Website configuration update
 *
 * @param $post_data
 * Config Prestashop mis à jour :
 * AV_PROCESSINIT : (varchar) onorder or onorderstatuschange | Event which initiate the review request to customer
 * AV_ORDERSTATESCHOOSEN : (array) Array of choosen status to get orders
 * AV_GETPRODREVIEWS : (varchar) yes or no | Get products reviews
 * AV_DISPLAYPRODREVIEWS : (varchar) yes or no | Display products reviews
 * AV_SCRIPTFIXE_ALLOWED : (varchar) yes or non | Display fix widget
 * AV_SCRIPTFLOAT_ALLOWED: (varchar) yes or non | Display float widget
 * AV_SCRIPTFIXE : (varchar) script Js | JS for fix widget
 * AV_SCRIPTFIXE_POSITION : (varchar) left or right | Fix widget position
 * AV_SCRIPTFLOAT : (varchar) script Js | JS for float widget
 * AV_FORBIDDEN_EMAIL : (array) Domain name on emails for which we can't request reviews to customer
 * @return $reponse : error code + error
 */
function setModuleConfiguration(&$post_data)
{
    //Multisite structure: updateValue($key, $values, $html = false, $id_shop_group = null, $id_shop = null)
    $reponse = array();
    $uns_msg = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    if (!empty($uns_msg))
    {
        if (!empty($uns_msg['id_shop']))
        {
            if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
            {
                $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop = ".(int)$uns_msg['id_shop'];
                $row = Db::getInstance()->getRow($sql);
                $group_name = '_'.Tools::substr($row['name'], 13);
                Configuration::updateValue('AV_PROCESSINIT'.$group_name, $uns_msg['init_reviews_process'], false, null, $uns_msg['id_shop']);
                // Implode if more than one element so is_array
                $orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
                                        implode(';', $uns_msg['id_order_status_choosen']) :
                                        $uns_msg['id_order_status_choosen'];
                Configuration::updateValue('AV_ORDERSTATESCHOOSEN'.$group_name, $orderstatechoosen, false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_DELAY'.$group_name, $uns_msg['delay'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_GETPRODREVIEWS'.$group_name, $uns_msg['get_product_reviews'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_DISPLAYPRODREVIEWS'.$group_name, $uns_msg['display_product_reviews'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED'.$group_name, $uns_msg['display_fixe_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE_POSITION'.$group_name, $uns_msg['position_fixe_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED'.$group_name, $uns_msg['display_float_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_URLCERTIFICAT'.$group_name, $uns_msg['url_certificat'], false, null, $uns_msg['id_shop']);
                // Implode if more than one element so is_array
                $forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
                                    implode(';', $uns_msg['forbidden_mail_extension']) :
                                    $uns_msg['forbidden_mail_extension'];
                Configuration::updateValue('AV_FORBIDDEN_EMAIL'.$group_name, $forbiddenemail, false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE'.$group_name,
                                            htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_fixe_widget'])),
                                            true,
                                            null,
                                            $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFLOAT'.$group_name,
                                            htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_float_widget'])),
                                            true,
                                            null,
                                            $uns_msg['id_shop']);
                Configuration::updateValue('AV_CODE_LANG'.$group_name, $uns_msg['code_lang'], false, null, $uns_msg['id_shop']);
                $reponse['sign'] = SHA1($post_data['query'].
                                    Configuration::get('AV_IDWEBSITE'.$group_name, false, null, $uns_msg['id_shop']).
                                    Configuration::get('AV_CLESECRETE'.$group_name, false, null, $uns_msg['id_shop']));
                $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop'], $group_name);
            }
            else
            {
                Configuration::updateValue('AV_PROCESSINIT', $uns_msg['init_reviews_process'], false, null, $uns_msg['id_shop']);
                // Implode if more than one element so is_array
                $orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
                                        implode(';', $uns_msg['id_order_status_choosen']) :
                                        $uns_msg['id_order_status_choosen'];
                Configuration::updateValue('AV_ORDERSTATESCHOOSEN', $orderstatechoosen, false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_DELAY', $uns_msg['delay'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_GETPRODREVIEWS', $uns_msg['get_product_reviews'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_DISPLAYPRODREVIEWS', $uns_msg['display_product_reviews'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED', $uns_msg['display_fixe_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE_POSITION', $uns_msg['position_fixe_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED', $uns_msg['display_float_widget'], false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_URLCERTIFICAT', $uns_msg['url_certificat'], false, null, $uns_msg['id_shop']);
                // Implode if more than one element so is_array
                $forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
                                    implode(';', $uns_msg['forbidden_mail_extension']) :
                                    $uns_msg['forbidden_mail_extension'];
                Configuration::updateValue('AV_FORBIDDEN_EMAIL', $forbiddenemail, false, null, $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFIXE',
                                            htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_fixe_widget'])),
                                            true,
                                            null,
                                            $uns_msg['id_shop']);
                Configuration::updateValue('AV_SCRIPTFLOAT',
                                            htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_float_widget'])),
                                            true,
                                            null,
                                            $uns_msg['id_shop']);
                Configuration::updateValue('AV_CODE_LANG', $uns_msg['code_lang'], false, null, $uns_msg['id_shop']);
                $reponse['sign'] = SHA1($post_data['query'].
                                    Configuration::get('AV_IDWEBSITE', false, null, $uns_msg['id_shop']).
                                    Configuration::get('AV_CLESECRETE', false, null, $uns_msg['id_shop']));
                $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop']);
            }
        }
        else
        {
            if (Configuration::get('AV_MULTILINGUE') == 'checked')
            {
                $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop is null";
                if ($row = Db::getInstance()->getRow($sql))
                    $group_name = '_'.Tools::substr($row['name'], 13);
                Configuration::updateValue('AV_PROCESSINIT'.$group_name, $uns_msg['init_reviews_process']);
                // Implode if more than one element so is_array
                $orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
                                        implode(';', $uns_msg['id_order_status_choosen']) :
                                        $uns_msg['id_order_status_choosen'];
                Configuration::updateValue('AV_ORDERSTATESCHOOSEN'.$group_name, $orderstatechoosen);
                Configuration::updateValue('AV_DELAY'.$group_name, $uns_msg['delay']);
                Configuration::updateValue('AV_GETPRODREVIEWS'.$group_name, $uns_msg['get_product_reviews']);
                Configuration::updateValue('AV_DISPLAYPRODREVIEWS'.$group_name, $uns_msg['display_product_reviews']);
                Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED'.$group_name, $uns_msg['display_fixe_widget']);
                Configuration::updateValue('AV_SCRIPTFIXE_POSITION'.$group_name, $uns_msg['position_fixe_widget']);
                Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED'.$group_name, $uns_msg['display_float_widget']);
                Configuration::updateValue('AV_URLCERTIFICAT'.$group_name, $uns_msg['url_certificat']);
                // Implode if more than one element so is_array
                $forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
                                    implode(';', $uns_msg['forbidden_mail_extension']) :
                                    $uns_msg['forbidden_mail_extension'];
                Configuration::updateValue('AV_FORBIDDEN_EMAIL'.$group_name, $forbiddenemail);
                Configuration::updateValue('AV_SCRIPTFIXE'.$group_name, htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_fixe_widget'])), true);
                Configuration::updateValue('AV_SCRIPTFLOAT'.$group_name, htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_float_widget'])), true);
                Configuration::updateValue('AV_CODE_LANG'.$group_name, $uns_msg['code_lang']);
                $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
                $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop'], $group_name);
            }
            else
            {
                Configuration::updateValue('AV_PROCESSINIT', $uns_msg['init_reviews_process']);
                // Implode if more than one element so is_array
                $orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
                                        implode(';', $uns_msg['id_order_status_choosen']) :
                                        $uns_msg['id_order_status_choosen'];
                Configuration::updateValue('AV_ORDERSTATESCHOOSEN', $orderstatechoosen);
                Configuration::updateValue('AV_DELAY', $uns_msg['delay']);
                Configuration::updateValue('AV_GETPRODREVIEWS', $uns_msg['get_product_reviews']);
                Configuration::updateValue('AV_DISPLAYPRODREVIEWS', $uns_msg['display_product_reviews']);
                Configuration::updateValue('AV_SCRIPTFIXE_ALLOWED', $uns_msg['display_fixe_widget']);
                Configuration::updateValue('AV_SCRIPTFIXE_POSITION', $uns_msg['position_fixe_widget']);
                Configuration::updateValue('AV_SCRIPTFLOAT_ALLOWED', $uns_msg['display_float_widget']);
                Configuration::updateValue('AV_URLCERTIFICAT', $uns_msg['url_certificat']);
                // Implode if more than one element so is_array
                $forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
                                    implode(';', $uns_msg['forbidden_mail_extension']) :
                                    $uns_msg['forbidden_mail_extension'];
                Configuration::updateValue('AV_FORBIDDEN_EMAIL', $forbiddenemail);
                Configuration::updateValue('AV_SCRIPTFIXE', htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_fixe_widget'])), true);
                Configuration::updateValue('AV_SCRIPTFLOAT', htmlentities(str_replace(array("\r\n", "\n"), '', $uns_msg['script_float_widget'])), true);
                Configuration::updateValue('AV_CODE_LANG', $uns_msg['code_lang']);
                $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
                $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop']);
            }
        }
        $reponse['debug'] = 'La configuration du site a été mise à jour';
        $reponse['return'] = 1;
        $reponse['query'] = $post_data['query'];
        Configuration::updateValue('NETREVIEWS_CONFIGURATION_OK', true);
    }
    else
    {
        $reponse['debug'] = "Aucune données reçues par le site dans $_POST[message]";
        $reponse['message'] = "Aucune données reçues par le site dans $_POST[message]";
        $reponse['return'] = 2;
        if (!empty($uns_msg['id_shop']))
        {
            if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
            {
                $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop = ".(int)$uns_msg['id_shop'];
                $row = Db::getInstance()->getRow($sql);
                $group_name = '_'.Tools::substr($row['name'], 13);
                $reponse['sign'] = SHA1($post_data['query'].
                Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $uns_msg['id_shop']).
                Configuration::get('AV_CLESECRETE'.$group_name, null, null, $uns_msg['id_shop']));
            }
            else
            {
                $reponse['sign'] = SHA1($post_data['query'].
                Configuration::get('AV_IDWEBSITE', null, null, $uns_msg['id_shop']).
                Configuration::get('AV_CLESECRETE', null, null, $uns_msg['id_shop']));
            }
        }
        else
        {
            if (Configuration::get('AV_MULTILINGUE') == 'checked')
            {
                $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop is null";
                if ($row = Db::getInstance()->getRow($sql))
                    $group_name = '_'.Tools::substr($row['name'], 13);
                $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
            }
            else
                $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
        }
        $reponse['query'] = $post_data['query'];
    }
    return $reponse;
}
/**
 * truncate content on tables av_products_reviews et av_products_average
 *
 * @param $post_data : sent parameters
 * @return $reponse : array to debug info
 */
function truncateTables(&$post_data)
{
    $reponse = array();
    $uns_msg = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    $query = array();
    if (!empty($uns_msg['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
        {
            $sql = 'SELECT name
            FROM '._DB_PREFIX_."configuration
            WHERE value = '".pSQL($uns_msg['idWebsite'])."'
            AND name like 'AV_IDWEBSITE_%'
            AND id_shop = ".(int)$uns_msg['id_shop'];
            $row = Db::getInstance()->getRow($sql);
            $group_name = '_'.Tools::substr($row['name'], 13);
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_reviews
            WHERE id_shop = '.(int)$uns_msg['id_shop'].'
            AND iso_lang in ("'.implode ( '","', unserialize(Configuration::get('AV_GROUP_CONF'.$group_name, null, null, $uns_msg['id_shop']))).'" );';
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_average
            WHERE id_shop = '.(int)$uns_msg['id_shop'].'
            AND iso_lang in ("'.implode ( '","', unserialize(Configuration::get('AV_GROUP_CONF'.$group_name, null, null, $uns_msg['id_shop']))).'" );';
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $uns_msg['id_shop']).
            Configuration::get('AV_CLESECRETE'.$group_name, null, null, $uns_msg['id_shop']));
        }
        else
        {
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_reviews WHERE id_shop = '.(int)$uns_msg['id_shop'].';';
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_average WHERE id_shop = '.(int)$uns_msg['id_shop'].';';
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE', null, null, $uns_msg['id_shop']).
            Configuration::get('AV_CLESECRETE', null, null, $uns_msg['id_shop']));
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
            FROM '._DB_PREFIX_."configuration
            WHERE value = '".pSQL($uns_msg['idWebsite'])."'
            AND name like 'AV_IDWEBSITE_%'
            AND id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_reviews
            WHERE id_shop is null
            AND iso_lang in ("'.implode ( '","', unserialize(Configuration::get('AV_GROUP_CONF'.$group_name))).'");';
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_average
            WHERE id_shop is null
            AND iso_lang in ("'.implode('","', unserialize(Configuration::get('AV_GROUP_CONF'.$group_name))).'");';
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE'.$group_name).
            Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
        {
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_reviews;';
            $query[] = 'DELETE FROM '._DB_PREFIX_.'av_products_average;';
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
        }
    }
    $reponse['return'] = 1;
    $reponse['debug'] = 'Tables truncated';
    $reponse['message'] = 'Tables truncated';
    foreach ($query as $sql)
    {
        if (!Db::getInstance()->Execute($sql))
        {
            $reponse['return'] = 2;
            $reponse['debug'] = 'Tables not truncated';
            $reponse['message'] = 'Tables not truncated';
        }
    }
    $reponse['query'] = $uns_msg['query'];
    return $reponse;
}
/**
 * Check if module is installed and enabled
 *
 * @param $post_data : sent parameters
 * @return state
 */
function isActiveModule(&$post_data)
{
    $reponse = array();
    $active = false;
    $uns_msg = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    if (!empty($uns_msg['id_shop']))
    {
        $id_module = Db::getInstance()->getValue('SELECT id_module FROM '._DB_PREFIX_.'module WHERE name = \'netreviews\'');
        if (Db::getInstance()->getValue('SELECT id_module
                                            FROM '._DB_PREFIX_.'module_shop
                                            WHERE id_module = '.(int)$id_module.'
                                            AND id_shop = '.(int)$uns_msg['id_shop']))
            $active = true;
    }
    else
        if (Db::getInstance()->getValue('SELECT active FROM '._DB_PREFIX_.'module WHERE name LIKE \'netreviews\''))
            $active = true;
    if (!$active)
    {
        $reponse['debug'] = 'Module disabled';
        $reponse['return'] = 2; //Module disabled
        $reponse['query'] = 'isActiveModule';
        return $reponse;
    }
    $reponse['debug'] = 'Module installed and enabled';
    if (!empty($uns_msg['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop = ".(int)$uns_msg['id_shop'];
            $row = Db::getInstance()->getRow($sql);
            $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $uns_msg['id_shop']).
            Configuration::get('AV_CLESECRETE'.$group_name, null, null, $uns_msg['id_shop']));
        }
        else
        {
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE', null, null, $uns_msg['id_shop']).
            Configuration::get('AV_CLESECRETE', null, null, $uns_msg['id_shop']));
        }

    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop is null ";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));

    }

    $reponse['return'] = 1; //Module OK
    $reponse['query'] = $post_data['query'];
    return $reponse;
}
/**
 * Get module and site configuration
 *
 * @param $post_data : sent parameters
 * @return $reponse : array to debug info
 */
function getModuleAndSiteConfiguration(&$post_data)
{
    $reponse = array();
    $uns_msg = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    if (!empty($uns_msg['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $uns_msg['id_shop']) == 'checked')
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop = ".(int)$uns_msg['id_shop'];
            $row = Db::getInstance()->getRow($sql);
            $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop'], $group_name);
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $uns_msg['id_shop']).
                                Configuration::get('AV_CLESECRETE'.$group_name, null, null, $uns_msg['id_shop']));
        }
        else
        {
            $reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop']);
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE', null, null, $uns_msg['id_shop']).
                                Configuration::get('AV_CLESECRETE', null, null, $uns_msg['id_shop']));
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($uns_msg['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop is null ";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['message'] = getModuleAndSiteInfos(null, $group_name);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
        {
            $reponse['message'] = getModuleAndSiteInfos();
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
        }
    }
    $reponse['query'] = $uns_msg['query'];
    if (empty($reponse['message']))
        $reponse['return'] = 2;
    else
        $reponse['return'] = 1;
    return $reponse;
}
/**
 * Get orders
 *
 * @param $query : $post_data
 * @return orders (array)
 */
function getOrders(&$post_data)
{
    $reponse = array();
    $post_message = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    if (!empty($post_message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($post_message['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop = ".(int)$post_message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $allowed_products = Configuration::get('AV_GETPRODREVIEWS'.$group_name, null, null, $post_message['id_shop']);
            $process_choosen = Configuration::get('AV_PROCESSINIT'.$group_name, null, null, $post_message['id_shop']);
            $order_status_choosen = Configuration::get('AV_ORDERSTATESCHOOSEN'.$group_name, null, null, $post_message['id_shop']);
            $forbidden_mail_extensions = explode(';', Configuration::get('AV_FORBIDDEN_EMAIL'.$group_name, null, null, $post_message['id_shop']));
        }
        else
        {
            $allowed_products = Configuration::get('AV_GETPRODREVIEWS', null, null, $post_message['id_shop']);
            $process_choosen = Configuration::get('AV_PROCESSINIT', null, null, $post_message['id_shop']);
            $order_status_choosen = Configuration::get('AV_ORDERSTATESCHOOSEN', null, null, $post_message['id_shop']);
            $forbidden_mail_extensions = explode(';', Configuration::get('AV_FORBIDDEN_EMAIL', null, null, $post_message['id_shop']));
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    WHERE value = '".pSQL($post_message['idWebsite'])."'
                    AND name like 'AV_IDWEBSITE_%'
                    AND id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $allowed_products = Configuration::get('AV_GETPRODREVIEWS'.$group_name);
            $process_choosen = Configuration::get('AV_PROCESSINIT'.$group_name);
            $order_status_choosen = Configuration::get('AV_ORDERSTATESCHOOSEN'.$group_name);
            $forbidden_mail_extensions = explode(';', Configuration::get('AV_FORBIDDEN_EMAIL'.$group_name));
        }
        else
        {
            $allowed_products = Configuration::get('AV_GETPRODREVIEWS');
            $process_choosen = Configuration::get('AV_PROCESSINIT');
            $order_status_choosen = Configuration::get('AV_ORDERSTATESCHOOSEN');
            $forbidden_mail_extensions = explode(';', Configuration::get('AV_FORBIDDEN_EMAIL'));
        }
    }
    $query_iso_lang = '';
    $query_id_shop = '';
    $query_status = '';
    if ($process_choosen == 'onorderstatuschange' && !empty($order_status_choosen))
    {
        $order_status_choosen = str_replace(';', ',', $order_status_choosen);
        $query_status = ' AND oh.id_order_state IN ('.pSQL($order_status_choosen).')';
    }
    if (isset($post_message['iso_lang']))
    {
        $o_lang = new Language;
        $id_lang = $o_lang->getIdByIso(Tools::strtolower($post_message['iso_lang']));
        $query_iso_lang .= ' AND o.id_lang = '.(int)$id_lang;
    }
    if (!empty($post_message['id_shop']) && Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
    {
        $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($post_message['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop = ".(int)$post_message['id_shop'];
        if ($row = Db::getInstance()->getRow($sql))
            $group_name = '_'.Tools::substr($row['name'], 13);
        $sql2 = 'SELECT value FROM '._DB_PREFIX_."configuration
                    WHERE name = 'AV_GROUP_CONF".pSQL($group_name)."'
                    AND id_shop like ".(int)$post_message['id_shop'];
        if ($row = Db::getInstance()->getRow($sql2))
            $list_iso_lang_multilingue = unserialize($row['value']);
        $ids_lang = '(';
        foreach ($list_iso_lang_multilingue as $code_iso)
        {
            $o_lang = new Language;
            $id_lang = $o_lang->getIdByIso(Tools::strtolower($code_iso));
            $ids_lang .= (int)$id_lang.',';
        }
        $ids_lang = Tools::substr($ids_lang, 0, -1).')';
        $query_iso_lang .= ' AND o.id_lang in '.pSQL($ids_lang);
    }
    else if (Configuration::get('AV_MULTILINGUE') == 'checked')
    {
        $sql = 'SELECT name
                FROM '._DB_PREFIX_."configuration
                WHERE value = '".pSQL($post_message['idWebsite'])."'
                AND name like 'AV_IDWEBSITE_%'
                AND id_shop is null ";
        if ($row = Db::getInstance()->getRow($sql))
            $group_name = '_'.Tools::substr($row['name'], 13);
        $sql2 = 'SELECT value FROM '._DB_PREFIX_."configuration where name = 'AV_GROUP_CONF".pSQL($group_name)."' and id_shop is null";
        if ($row = Db::getInstance()->getRow($sql2))
            $list_iso_lang_multilingue = unserialize($row['value']);
        $ids_lang = '(';
        foreach ($list_iso_lang_multilingue as $code_iso)
        {
            $o_lang = new Language;
            $id_lang = $o_lang->getIdByIso(Tools::strtolower($code_iso));
            $ids_lang .= (int)$id_lang.',';
        }
        $ids_lang = Tools::substr($ids_lang, 0, -1).')';
        $query_iso_lang .= ' AND o.id_lang in '.pSQL($ids_lang);
    }
    if (!empty($post_message['id_shop']))
        $query_id_shop = ' AND oav.id_shop = '.(int)$post_message['id_shop'];
    $query = '  SELECT oav.id_order, o.date_add as date_order,o.id_customer,o.total_paid,o.id_lang,o.id_shop
                FROM '._DB_PREFIX_.'av_orders oav
                LEFT JOIN '._DB_PREFIX_.'orders o
                ON oav.id_order = o.id_order
                LEFT JOIN '._DB_PREFIX_.'order_history oh
                ON oh.id_order = o.id_order
                WHERE (oav.flag_get IS NULL OR oav.flag_get = 0)'
                .$query_status.$query_id_shop.$query_iso_lang;
    $orders_list = Db::getInstance()->ExecuteS($query);
    $reponse['debug'][] = $query;
    $reponse['debug']['mode'] = '['.$process_choosen.'] '.Db::getInstance()->numRows().' commandes récupérées';
    $orders_list_toreturn = array();
    foreach ($orders_list as $order)
    {
        // Test if customer email domain is forbidden (marketplaces case)
        $o_customer = new Customer($order['id_customer']);
        $customer_email_extension = explode('@', $o_customer->email);
        if (!in_array($customer_email_extension[1], $forbidden_mail_extensions))
        {
            $array_order = array(
                'id_order' => $order['id_order'],
                'id_lang' => $order['id_lang'],
                'iso_lang' => pSQL(Language::getIsoById($order['id_lang'])),
                'id_shop' => $order['id_shop'],
                'total_paid' => $order['total_paid'],
                'id_customer' => $order['id_customer'],
                'date_order' => strtotime($order['date_order']), // date timestamp in orders table
                'date_order_formatted' => $order['date_order'], // date in orders table formatted
                'firstname_customer' => $o_customer->firstname,
                'lastname_customer' => $o_customer->lastname,
                'email_customer' => $o_customer->email,
                'products' => array()
                );
            //  Add products to array
            if (!empty($allowed_products) && $allowed_products == 'yes')
            {
                $o_order = new Order($order['id_order']);
                $products_in_order = $o_order->getProducts();
                $array_products = array();
                foreach ($products_in_order as $element)
                {
                    $array_url = NetReviewsModel::getUrlsProduct($element['product_id']);
                    $product = array(
                        'id_product' => $element['product_id'],
                        'name_product' => $element['product_name'],
                        'url_image' => $array_url['url_image_product'],
                        'url' => $array_url['url_product']
                    );
                    $array_products[] = $product;
                    unset($product);
                }
                $array_order['products'] = $array_products;
                unset($array_products);
            }
            $orders_list_toreturn[$order['id_order']] = $array_order;
        }
        else
            $reponse['message']['Emails_Interdits'][] = 'Commande n°'.$order['id_order'].' Email:'.$o_customer->email;
        // Set orders as getted but do not if it's a test request
        if (!isset($post_message['no_flag']) || $post_message['no_flag'] == 0)
            Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'av_orders
                                            SET horodate_get = "'.time().'", flag_get = 1
                                            WHERE id_order = '.(int)$order['id_order']);
    }
    // Purge Table
    $nb_orders_purge = Db::getInstance()->getValue('SELECT count(id_order)
                                                    FROM '._DB_PREFIX_.'av_orders
                                                    WHERE horodate_now < DATE_SUB(NOW(), INTERVAL 6 MONTH)');
    $reponse['debug']['purge'] = '[purge] '.$nb_orders_purge.' commandes purgées';
    Db::getinstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_orders WHERE horodate_now < DATE_SUB(NOW(), INTERVAL 6 MONTH)');
    $reponse['return'] = 1;
    $reponse['query'] = $post_message['query'];
    $reponse['message']['nb_orders'] = count($orders_list_toreturn);
    $reponse['message']['list_orders'] = $orders_list_toreturn;
    $reponse['debug']['force'] = $post_message['force'];
    $reponse['debug']['no_flag'] = $post_message['no_flag'];
    if (!empty($post_message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$post_message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['message']['delay'] = Configuration::get('AV_DELAY'.$group_name, null, null, $post_message['id_shop']);
            $reponse['sign'] = SHA1($post_message['query'].
            Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $post_message['id_shop']).
            Configuration::get('AV_CLESECRETE'.$group_name, null, null, $post_message['id_shop']));
        }
        else
        {
            $reponse['message']['delay'] = Configuration::get('AV_DELAY', null, null, $post_message['id_shop']);
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE', null, null, $post_message['id_shop']).
            Configuration::get('AV_CLESECRETE', null, null, $post_message['id_shop']));
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null ";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['message']['delay'] = Configuration::get('AV_DELAY'.$group_name);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
        {
            $reponse['message']['delay'] = Configuration::get('AV_DELAY');
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
        }
    }
    return $reponse;
}
/**
 * Product reviews update
 *
 * @param $post_data : sent parameters
 * @return
 */
function setProductsReviews(&$post_data)
{
    $reponse = array();
    $microtime_deb = microtime();
    $message = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    $reviews = (!empty($message['data'])) ? $message['data'] : null;
    if (!empty($message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $message['id_shop']) == 'checked')
        {
            $id_shop = (int)$message['id_shop'];
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$id_shop;
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $sql2 = 'SELECT value FROM '._DB_PREFIX_."configuration where name = 'AV_GROUP_CONF".pSQL($group_name)."' and id_shop = ".(int)$id_shop;
            if ($row = Db::getInstance()->getRow($sql2))
                $list_iso_lang_multilingue = unserialize($row['value']);
            $iso_lang = '"'.pSQL($list_iso_lang_multilingue[0]).'"';
        }
        else
        {
            $id_shop = (int)$message['id_shop'];
            $iso_lang = '0';
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $id_shop = 0;
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $sql2 = 'SELECT value FROM '._DB_PREFIX_."configuration where name = 'AV_GROUP_CONF".pSQL($group_name)."' and id_shop is null";
            if ($row = Db::getInstance()->getRow($sql2))
                $list_iso_lang_multilingue = unserialize($row['value']);
            $iso_lang = '"'.pSQL($list_iso_lang_multilingue[0]).'"';
        }
        else
        {
            $id_shop = 0;
            $iso_lang = '0';
        }
    }
    $arra_line_reviews = (!empty($reviews)) ? explode("\n", $reviews) : array(); // Line array (separator \n)
    $count_line_reviews = count($arra_line_reviews);
    $count_update_new = 0;
    $count_delete = 0;
    $count_error = 0;
    foreach ($arra_line_reviews as $line_review)
    {
        $arra_column = explode("\t", $line_review); // Get column in each line to save in an array (separator \t = tab)
        $count_column = count($arra_column);
        // Check if NEW or UPDATE ou DELETE exist
        if (!empty($arra_column[0]))
        {
            if ($arra_column[0] == 'NEW' || $arra_column[0] == 'UPDATE')
            {
                if (isset($arra_column[11]) && $arra_column[11] > 0) //Check if there is a discussion on this reviews (in 11)
                {
                    if (($arra_column[11] * 3 + 12) == $count_column) //3 data by message in discussion
                    {
                        for ($i = 0; $i < $arra_column[11]; $i++)
                        {
                            $arra_column['discussion'][] = array(
                                'horodate' => $arra_column[11 + ($i * 3) + 1],
                                'origine' => $arra_column[11 + ($i * 3) + 2],
                                'commentaire' => $arra_column[11 + ($i * 3) + 3],
                            );
                        }
                        Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_reviews
                                                    (id_product_av, ref_product, rate, review, horodate, customer_name, discussion,iso_lang,id_shop)
                                                    VALUES (\''.pSQL($arra_column[2]).'\',
                                                        \''.(int)$arra_column[4].'\',
                                                        \''.(float)$arra_column[7].'\',
                                                        \''.pSQL($arra_column[6]).'\',
                                                        \''.pSQL($arra_column[5]).'\',
                                                        \''.pSQL(Tools::ucfirst($arra_column[8][0]).'. '.Tools::ucfirst($arra_column[9])).'\',
                                                        \''.pSQL(NetReviewsModel::acEncodeBase64(Tools::jsonEncode($arra_column['discussion']))).'\',
                                                        '.$iso_lang.',
                                                        '.(int)$id_shop.'
                                                    )');
                        $count_update_new++;
                    }
                    else
                    {
                        $reponse['debug'][$arra_column[2]] = 'Incorrect number of parameters in the line (Number of messages : '.
                                                                $arra_column[11].')  : '.$count_column;
                        $count_error++;
                    }
                }
                elseif ((!isset($arra_column[11]) || empty($arra_column[11]) || $arra_column[11] == 0))  // No discussion
                {
                    if (($arra_column[11] * 3 + 12) == count($arra_column))
                    {
                        Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_reviews
                                                    (id_product_av, ref_product, rate, review, horodate, customer_name, discussion,iso_lang,id_shop)
                                                    VALUES (\''.pSQL($arra_column[2]).'\',
                                                        \''.(int)$arra_column[4].'\',
                                                        \''.(float)$arra_column[7].'\',
                                                        \''.pSQL($arra_column[6]).'\',
                                                        \''.pSQL($arra_column[5]).'\',
                                                        \''.pSQL(urlencode(Tools::ucfirst($arra_column[8][0]).'. '.Tools::ucfirst($arra_column[9]))).'\',
                                                        null,
                                                        '.$iso_lang.',
                                                        '.(int)$id_shop.'
                                                    )');
                        $count_update_new++;
                    }
                    else
                    {
                        $reponse['debug'][$arra_column[2]] = 'Incorrect number of parameters in the line (Number of messages : '.
                                                                $arra_column[11].')  : '.$count_column;
                        $count_error++;
                    }
                }
            }
            elseif ($arra_column[0] == 'DELETE')
            {
                if (!empty($message['id_shop']))
                    if (Configuration::get('AV_MULTILINGUE', null, null, $message['id_shop']) == 'checked')
                        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_products_reviews
                                                    WHERE id_product_av = \''.pSQL($arra_column[2]).'\'
                                                    AND ref_product = \''.(int)$arra_column[4].'\'
                                                    AND iso_lang = '.$iso_lang.'
                                                    AND id_shop = '.(int)$id_shop);
                    else
                        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_products_reviews
                                                    WHERE id_product_av = \''.pSQL($arra_column[2]).'\'
                                                    AND ref_product = \''.(int)$arra_column[4].'\'
                                                    AND id_shop = '.(int)$id_shop);
                else
                    if (Configuration::get('AV_MULTILINGUE') == 'checked')
                        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_products_reviews
                                                    WHERE id_product_av = \''.pSQL($arra_column[2]).'\'
                                                    AND ref_product = \''.(int)$arra_column[4].'\'
                                                    AND iso_lang = '.$iso_lang);
                    else
                        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_products_reviews
                                                    WHERE id_product_av = \''.pSQL($arra_column[2]).'\'
                                                    AND ref_product = \''.(int)$arra_column[4].'\'');
                $count_delete++;
            }
            elseif ($arra_column[0] == 'AVG') // AVG id_product_av ref_product rate nb_reviews
            {
                Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_average
                                            (id_product_av, ref_product, rate, nb_reviews, horodate_update,iso_lang,id_shop)
                                            VALUES (\''.pSQL($arra_column[1]).'\',
                                                    \''.pSQL($arra_column[2]).'\',
                                                    \''.(int)$arra_column[3].'\',
                                                    \''.(int)$arra_column[4].'\',
                                                    \''.time().'\',
                                                    '.$iso_lang.',
                                                    '.(int)$id_shop.'
                                                )
                                            ');
                $count_update_new++;
            }
            else
            {
                $reponse['debug'][$arra_column[2]] = 'No action (NEW, UPDATE, DELETE) sent : ['.$arra_column[0].']';
                $count_error++;
            }
        }
    }
    $microtime_fin = microtime();
    $reponse['return'] = 1;
    if (!empty($message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $message['id_shop']) == 'checked')
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $message['id_shop']).
                                Configuration::get('AV_CLESECRETE'.$group_name, null, null, $message['id_shop']));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE', null, null, $message['id_shop']).
                                Configuration::get('AV_CLESECRETE', null, null, $message['id_shop']));
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE'))
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));

    }
    $reponse['query'] = $post_data['query'];
    $reponse['message']['lignes_recues'] = $arra_line_reviews;
    $reponse['message']['nb_update_new'] = $count_update_new;
    $reponse['message']['nb_delete'] = $count_delete;
    $reponse['message']['nb_errors'] = $count_error;
    $reponse['message']['microtime'] = $microtime_fin - $microtime_deb;
    if ($count_line_reviews != ($count_update_new + $count_delete + $count_error))
        $reponse['debug'][] = 'An error occured. Numbers of line received is not the same as line saved in DB';
    return $reponse;
}
/**
 * Return of Product URL (image and link)
 *
 * @param $post_data : sent parameters
 * @return array with info data
 */
function getUrlProducts(&$post_data)
{
    $reponse = array();
    $array_url = array();
    $post_message = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    $ids_product = $post_message['list_produits'];
    foreach ($ids_product as $id_product)
    {
        $urls = NetReviewsModel::getUrlsProduct($id_product);
        if ($urls) //return urls only if product exist
            $array_url[$id_product] = $urls;
    }
    $reponse['return'] = 1;
    $reponse['query'] = 1;
    $reponse['list_produits'] = $array_url;
    return $array_url;
}
/**
 * Get module and site infos
 * Private function, do not use it. This function is called in setModuleConfiguration and getModuleConfiguration
 * @param $post_data
 * @return array with info data
 */
function getModuleAndSiteInfos($id_shop = null, $group_name = null)
{
    $module_version = new NetReviews;
    $module_version = $module_version->version;
    $order_statut_list = OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'));
    $perms = fileperms(_PS_MODULE_DIR_.'netreviews');
    if (($perms & 0xC000) == 0xC000)    // Socket
        $info = 's';
    elseif (($perms & 0xA000) == 0xA000) // Symbolic link
        $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) // Regular
        $info = '-';
    elseif (($perms & 0x6000) == 0x6000) // Block special
        $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) // Repository
        $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) // Special characters
        $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) // pipe FIFO
        $info = 'p';
    else // Unknow
        $info = 'u';
    // Others
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
    // All
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
    if (!empty($id_shop))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $id_shop) == 'checked')
        {
            $explode_secret_key = explode('-', Configuration::get('AV_CLESECRETE'.$group_name, null, null, $id_shop));
            $return = array(
                'Version_PS' => _PS_VERSION_,
                'Version_Module' => $module_version,
                'idWebsite' => Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $id_shop),
                'Nb_Multiboutique' => '',
                'Mode_multilingue' => '1',
                'list_iso_lang_multilingue' => '',
                'Websites' => '',
                'Id_Website_encours' => '',
                'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
                'Delay' => Configuration::get('AV_DELAY'.$group_name, null, null, $id_shop),
                'Initialisation_du_Processus' => Configuration::get('AV_PROCESSINIT'.$group_name, null, null, $id_shop),
                'Statut_choisi' => Configuration::get('AV_ORDERSTATESCHOOSEN'.$group_name, null, null, $id_shop),
                'Recuperation_Avis_Produits' => Configuration::get('AV_GETPRODREVIEWS'.$group_name, null, null, $id_shop),
                'Affiche_Avis_Produits' => Configuration::get('AV_DISPLAYPRODREVIEWS'.$group_name, null, null, $id_shop),
                'Affichage_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT_ALLOWED'.$group_name, null, null, $id_shop),
                'Script_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT'.$group_name, null, null, $id_shop),
                'Affichage_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_ALLOWED'.$group_name, null, null, $id_shop),
                'Position_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_POSITION'.$group_name, null, null, $id_shop),
                'Script_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE'.$group_name, null, null, $id_shop),
                'Emails_Interdits' => Configuration::get('AV_FORBIDDEN_EMAIL'.$group_name, null, null, $id_shop),
                'Liste_des_statuts' => $order_statut_list,
                'Droit_du_dossier_AV' => $info,
                'Date_Recuperation_Config' => date('Y-m-d H:i:s')
            );
            $sql = 'SELECT value FROM '._DB_PREFIX_."configuration where name = 'AV_GROUP_CONF".pSQL($group_name)."' and id_shop like '(int)$id_shop' ";
            if ($row = Db::getInstance()->getRow($sql))
                $return['list_iso_lang_multilingue'] = unserialize($row['value']);
        }
        else
        {
            $explode_secret_key = explode('-', Configuration::get('AV_CLESECRETE', null, null, $id_shop));
            $return = array(
                'Version_PS' => _PS_VERSION_,
                'Version_Module' => $module_version,
                'idWebsite' => Configuration::get('AV_IDWEBSITE', null, null, $id_shop),
                'Nb_Multiboutique' => '',
                'Mode_multilingue' => '',
                'list_iso_lang_multilingue' => '',
                'Websites' => '',
                'Id_Website_encours' => '',
                'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
                'Delay' => Configuration::get('AV_DELAY', null, null, $id_shop),
                'Initialisation_du_Processus' => Configuration::get('AV_PROCESSINIT', null, null, $id_shop),
                'Statut_choisi' => Configuration::get('AV_ORDERSTATESCHOOSEN', null, null, $id_shop),
                'Recuperation_Avis_Produits' => Configuration::get('AV_GETPRODREVIEWS', null, null, $id_shop),
                'Affiche_Avis_Produits' => Configuration::get('AV_DISPLAYPRODREVIEWS', null, null, $id_shop),
                'Affichage_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT_ALLOWED', null, null, $id_shop),
                'Script_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT', null, null, $id_shop),
                'Affichage_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_ALLOWED', null, null, $id_shop),
                'Position_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_POSITION', null, null, $id_shop),
                'Script_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE', null, null, $id_shop),
                'Emails_Interdits' => Configuration::get('AV_FORBIDDEN_EMAIL', null, null, $id_shop),
                'Liste_des_statuts' => $order_statut_list,
                'Droit_du_dossier_AV' => $info,
                'Date_Recuperation_Config' => date('Y-m-d H:i:s')
            );
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $explode_secret_key = explode('-', Configuration::get('AV_CLESECRETE'.$group_name));
            $return = array(
                'Version_PS' => _PS_VERSION_,
                'Version_Module' => $module_version,
                'idWebsite' => Configuration::get('AV_IDWEBSITE'.$group_name),
                'Nb_Multiboutique' => '',
                'Mode_multilingue' => '1',
                'list_iso_lang_multilingue' => '',
                'Websites' => '',
                'Id_Website_encours' => '',
                'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
                'Delay' => Configuration::get('AV_DELAY'.$group_name),
                'Initialisation_du_Processus' => Configuration::get('AV_PROCESSINIT'.$group_name),
                'Statut_choisi' => Configuration::get('AV_ORDERSTATESCHOOSEN'.$group_name),
                'Recuperation_Avis_Produits' => Configuration::get('AV_GETPRODREVIEWS'.$group_name),
                'Affiche_Avis_Produits' => Configuration::get('AV_DISPLAYPRODREVIEWS'.$group_name),
                'Affichage_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT_ALLOWED'.$group_name),
                'Script_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT'.$group_name),
                'Affichage_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_ALLOWED'.$group_name),
                'Position_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_POSITION'.$group_name),
                'Script_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE'.$group_name),
                'Emails_Interdits' => Configuration::get('AV_FORBIDDEN_EMAIL'.$group_name),
                'Liste_des_statuts' => $order_statut_list,
                'Droit_du_dossier_AV' => $info,
                'Date_Recuperation_Config' => date('Y-m-d H:i:s')
            );
            $sql = 'SELECT value FROM '._DB_PREFIX_."configuration where name = 'AV_GROUP_CONF".pSQL($group_name)."' and id_shop is NULL";
            if ($row = Db::getInstance()->getRow($sql))
                $return['list_iso_lang_multilingue'] = unserialize($row['value']);
        }
        else
        {
            $explode_secret_key = explode('-', Configuration::get('AV_CLESECRETE'));
            $return = array(
                'Version_PS' => _PS_VERSION_,
                'Version_Module' => $module_version,
                'idWebsite' => Configuration::get('AV_IDWEBSITE'),
                'Nb_Multiboutique' => '',
                'Websites' => '',
                'Id_Website_encours' => '',
                'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
                'Delay' => Configuration::get('AV_DELAY'),
                'Initialisation_du_Processus' => Configuration::get('AV_PROCESSINIT'),
                'Statut_choisi' => Configuration::get('AV_ORDERSTATESCHOOSEN'),
                'Recuperation_Avis_Produits' => Configuration::get('AV_GETPRODREVIEWS'),
                'Affiche_Avis_Produits' => Configuration::get('AV_DISPLAYPRODREVIEWS'),
                'Affichage_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT_ALLOWED'),
                'Script_Widget_Flottant' => Configuration::get('AV_SCRIPTFLOAT'),
                'Affichage_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_ALLOWED'),
                'Position_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE_POSITION'),
                'Script_Widget_Fixe' => Configuration::get('AV_SCRIPTFIXE'),
                'Emails_Interdits' => Configuration::get('AV_FORBIDDEN_EMAIL'),
                'Liste_des_statuts' => $order_statut_list,
                'Droit_du_dossier_AV' => $info,
                'Date_Recuperation_Config' => date('Y-m-d H:i:s')
            );
        }
    }
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1)
    {
        $return['Nb_Multiboutique'] = Shop::getTotalShops();
        $return['Websites'] = Shop::getShops();
    }
    return $return;
}
/**
 * Return history of one commande
 *
 * @param $post_data : sent parameters
 * @return array with info data
 */
function getOrderHistoryOn(&$post_data)
{
    $reponse = array();
    $array_history = array();
    $post_message = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    $ref_vente = $post_message['orderRef'];
    if (!empty($ref_vente))
    {
        $o_lang = new Language;
        $id_lang = $o_lang->getIdByIso(Tools::strtolower('fr'));
        $sql = 'SELECT oh.id_order, oh.id_order_state, os.name, oh.date_add
                FROM  '._DB_PREFIX_."order_history oh
                LEFT JOIN "._DB_PREFIX_."order_state_lang os ON os.id_order_state = oh.id_order_state
                WHERE  `id_order` = ".(int)$ref_vente."
                AND id_lang = ".(int)$id_lang."
                ORDER BY  `date_add` DESC";
        if (!$array_history = Db::getInstance()->ExecuteS($sql))
            $reponse['return'] = 2;
    }

    $reponse['return'] = 1;
    $reponse['message']['count_states'] = count($array_history);
    $reponse['message']['list_states'] = $array_history;

    if (!empty($post_message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$post_message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $post_message['id_shop']).
                                Configuration::get('AV_CLESECRETE'.$group_name, null, null, $post_message['id_shop']));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].
                                Configuration::get('AV_IDWEBSITE', null, null, $post_message['id_shop']).
                                Configuration::get('AV_CLESECRETE', null, null, $post_message['id_shop']));
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE'))
        {
            $sql = 'SELECT name FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
    }
    return $reponse;
}
/**
 * Return day count orders
 *
 * @param $post_data : sent parameters
 * @return array with info data
 */
function getCountOrder(&$post_data)
{
    $reponse = array();
    $post_message = Tools::jsonDecode(NetReviewsModel::acDecodeBase64($post_data['message']), true);
    $sql_id_shop = '';
    $sql_iso_lang = '';
    $ids_lang = array();
    if (!empty($post_message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
        {
            $sql_id_shop .= ' and id_shop = '.(int)$post_message['id_shop'];
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$post_message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $av_group_conf = unserialize(Configuration::get('AV_GROUP_CONF'.$group_name, null, null, $post_message['id_shop']));
            $o_lang = new Language;
            foreach ($av_group_conf as $isolang)
                $ids_lang[] = $o_lang->getIdByIso(Tools::strtolower($isolang));

            $sql_iso_lang .= ' and id_lang in ("'.implode('","', $ids_lang).'")';
        }
        else
            $sql_id_shop .= ' and id_shop = '.(int)$post_message['id_shop'];
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null ";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $av_group_conf = unserialize(Configuration::get('AV_GROUP_CONF'.$group_name));
            $o_lang = new Language;
            foreach ($av_group_conf as $isolang)
                $ids_lang[] = $o_lang->getIdByIso(Tools::strtolower($isolang));

            $sql_iso_lang .= ' and id_lang in ("'.implode('","', $ids_lang).'")';
        }
    }

    $sql = 'SELECT COUNT( * )
            FROM '._DB_PREFIX_.'orders
            WHERE (
            date_add
            BETWEEN DATE_SUB( NOW( ) , INTERVAL 1 DAY )
            AND NOW( )
            )'
            .$sql_iso_lang.$sql_id_shop;

    $reponse['message']['count_orders_day'] = Db::getInstance()->getValue($sql);
    $reponse['return'] = 1;

    if (!empty($post_message['id_shop']))
    {
        if (Configuration::get('AV_MULTILINGUE', null, null, $post_message['id_shop']) == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop = ".(int)$post_message['id_shop'];
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_message['query'].
            Configuration::get('AV_IDWEBSITE'.$group_name, null, null, $post_message['id_shop']).
            Configuration::get('AV_CLESECRETE'.$group_name, null, null, $post_message['id_shop']));
        }
        else
        {
            $reponse['sign'] = SHA1($post_data['query'].
            Configuration::get('AV_IDWEBSITE', null, null, $post_message['id_shop']).
            Configuration::get('AV_CLESECRETE', null, null, $post_message['id_shop']));
        }
    }
    else
    {
        if (Configuration::get('AV_MULTILINGUE') == 'checked')
        {
            $sql = 'SELECT name
                    FROM '._DB_PREFIX_."configuration
                    where value = '".pSQL($post_message['idWebsite'])."'
                    and name like 'AV_IDWEBSITE_%'
                    and id_shop is null ";
            if ($row = Db::getInstance()->getRow($sql))
                $group_name = '_'.Tools::substr($row['name'], 13);
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE'.$group_name).Configuration::get('AV_CLESECRETE'.$group_name));
        }
        else
            $reponse['sign'] = SHA1($post_data['query'].Configuration::get('AV_IDWEBSITE').Configuration::get('AV_CLESECRETE'));
    }

    return $reponse;
}
?>