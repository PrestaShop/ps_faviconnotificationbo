<?php
/**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class APIFAQ
{
    public function getData($module_key, $version)
    {
        if (function_exists('curl_init') == false) {
            return false;
        }
        $context = Context::getContext();
        $iso_code = Language::getIsoById($context->language->id);
        $url = 'http://api.addons.prestashop.com/request/faq/'.$module_key.'/'.$version.'/'.$iso_code;
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false
        );
        $CURL = curl_init();
        curl_setopt_array($CURL, $options);
        $content = curl_exec($CURL);
        curl_close($CURL);
        if (!$content) {
            return false;
        }
        $content = Tools::jsonDecode($content);
        if (!$content || empty($content->categories)) {
            return false;
        }
        return $content->categories;
    }
}
