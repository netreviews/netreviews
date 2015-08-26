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
*  @version   Release: $Revision: 7.1.4
*  @license   NetReviews
*  @date      25/08/2015
*  International Registered Trademark & Property of NetReviews SAS
*/

if (!defined('_PS_VERSION_'))
	exit;

/**
 * Function used to update your module from previous versions to the version 7.1.4,
 * Don't forget to create one file per version.
 */
function upgrade_module_7_1_4($module)
{
	return upgradePsConfiguration_7_1_4($module) //Upgrade PS configuration from previous versions to the version 7.1.4
	&& upgradeHook_7_1_4($module) //Upgrade hook from previous versions to the version 7.1.4
	&& upgradeDatabase_7_1_4($module); //Upgrade database from previous versions to the version 7.1.4
}

/**
 * Function used to update your PS configuration from previous versions to the version 7.1.4,
 */
function upgradePsConfiguration_7_1_4()
{
	return Configuration::updateValue('AV_MULTILINGUE', '0');
}

/**
 * Function used to update your hook from previous versions to the version 7.1.4,
 */
function upgradeHook_7_1_4($module)
{
	return true;
}

/**
 * Function used to update your database from previous versions to the version 7.1.4,
 */
function upgradeDatabase_7_1_4($module)
{
	return true;
}
