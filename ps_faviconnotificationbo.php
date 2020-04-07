<?php
/**
* 2007-2018 PrestaShop
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
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_faviconnotificationbo extends Module
{
    public $adminControllers = array(
        'adminAjax' => 'AdminAjaxFaviconBO',
    );

    protected static $conf_fields = array(
    'BACKGROUND_COLOR_FAVICONBO',
    'TEXT_COLOR_FAVICONBO',
    'CHECKBOX_ORDER',
    'CHECKBOX_CUSTOMER',
    'CHECKBOX_MESSAGE'
    );

    public function __construct()
    {
        $this->name = 'ps_faviconnotificationbo';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'PrestaShop';
        $this->bootstrap = true;

        $this->module_key = '91315ca88851b6c2852ee4be0c59b7b1';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';

        parent::__construct();

        $this->displayName = $this->trans('Order Notifications on the Favicon', array(), 'Modules.Faviconnotificationbo.Admin');
        $this->description = $this->trans('Be notified of each new order, client or message directly in the browser tab of your back office, even when working on another page', array(), 'Modules.Faviconnotificationbo.Admin');

        // Settings paths
        $this->js_path = $this->_path.'views/js/';
        $this->css_path = $this->_path.'views/css/';
        $this->img_path = $this->_path.'views/img/';
        $this->docs_path = $this->_path.'docs/';
        $this->logo_path = $this->_path.'logo.png';
        $this->module_path = $this->_path;
        $this->ps_version = (bool)version_compare(_PS_VERSION_, '1.7', '>=');

        // Confirm uninstall
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', array(), 'Admin.Modules.Notification');
        $this->ps_versions_compliancy = array('min' => '1.7.6.0', 'max' => _PS_VERSION_);
    }

    /**
     *
     *
     * @param none
     * @return bool
     */
    public function install()
    {

        Configuration::updateValue('CHECKBOX_ORDER', '1');
        Configuration::updateValue('CHECKBOX_CUSTOMER', '1');
        Configuration::updateValue('CHECKBOX_MESSAGE', '1');
        Configuration::updateValue('BACKGROUND_COLOR_FAVICONBO', '#DF0067');
        Configuration::updateValue('TEXT_COLOR_FAVICONBO', '#ffffff');
        return (parent::install() && $this->registerHook('BackOfficeHeader') && $this->installTab());
    }

    public function uninstall()
    {
        foreach (Ps_faviconnotificationbo::$conf_fields as $field) {
            Configuration::deleteByName($field);
        }
        if (parent::uninstall() &&
            $this->uninstallTab()) {
            return true;
        } else {
            $this->_errors[] = $this->trans('There was an error during the uninstallation.', array(), 'Admin.Modules.Notification');
            return false;
        }
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @return bool
     */
    public function installTab()
    {
        $result = true;

        foreach ($this->adminControllers as $controller_name) {
            $tab = new Tab();
            $tab->class_name = $controller_name;
            $tab->module = $this->name;
            $tab->active = true;
            $tab->id_parent = -1;
            $tab->name = array_fill_keys(
                Language::getIDs(false),
                $this->displayName
            );
            $result = $result && $tab->add();
        }

        return $result;
    }

    /**
     * uninstall tab
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $result = true;

        foreach ($this->adminControllers as $controller_name) {
            $id_tab = (int) Tab::getIdFromClassName($controller_name);
            $tab = new Tab($id_tab);

            if (Validate::isLoadedObject($tab)) {
                $result = $result && $tab->delete();
            }
        }

        return $result;
    }

    /**
     * load dependencies in the configuration of the module
     */
    public function loadAsset()
    {
        // Load CSS
        $css = array(
            $this->css_path.'faq.css',
            $this->css_path.'menu.css',
            $this->css_path.'back.css',
            $this->css_path.'fontawesome-all.min',
        );

        $this->context->controller->addCSS($css, 'all');

        // Load JS
        $jss = array(
            $this->js_path.'vue.min.js',
            $this->js_path.'faq.js',
            $this->js_path.'menu.js',
        );
        $this->context->controller->addJqueryPlugin('colorpicker');
        $this->context->controller->addJS($jss);
    }

    /**
     * FAQ API
     */
    public function loadFaq()
    {
        include_once('classes/APIFAQClass.php');
        $api = new APIFAQ();
        $faq = $api->getData($this->module_key, $this->version);

        return $faq;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $faq = $this->loadFaq(); // load faq from addons api
        $this->loadAsset(); // load js and css

        $id_lang = $this->context->language->id;
        $iso_lang = Language::getIsoById($id_lang);
        // get readme
        switch ($iso_lang) {
            case 'fr':
                $doc = $this->docs_path.'readme_fr.pdf';
                break;
            default:
                $doc = $this->docs_path.'readme_en.pdf';
                break;
        }

        // get current page
        $currentPage = 'faviconConfiguration';
        $page = Tools::getValue('page');
        if (!empty($page)) {
            $currentPage = Tools::getValue('page');
        }
        // assign var to smarty
        $this->context->smarty->assign(array(
            'module_name' => $this->name,
            'module_version' => $this->version,
            'moduleAdminLink' => $this->context->link->getAdminLink('AdminModules', true, false, array('configure' => $this->name)),
            'module_display' => $this->displayName,
            'apifaq' => $faq,
            'doc' => $doc,
            'logo_path' => $this->logo_path,
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'currentPage' => $currentPage,
            'ps_base_dir' => Tools::getHttpHost(true),
            'ps_version' => _PS_VERSION_,
            'isPs17' => $this->ps_version,
        ));

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/menu.tpl');
    }

    /**
     * return parameters saved during the favicon configuration
     * @return array parameters saved
     */
    public function getParams()
    {
        $params = array('BACKGROUND_COLOR_FAVICONBO', 'TEXT_COLOR_FAVICONBO', 'CHECKBOX_ORDER', 'CHECKBOX_CUSTOMER', 'CHECKBOX_MESSAGE');
        return Configuration::getMultiple($params);
    }

    /**
     * load the javascript who is used in all the backoffice
     */
    public function loadGlobalAsset()
    {
        $jss = array(
            $this->js_path.'favico.js',
        );
        $this->context->controller->addJS($jss);
    }

    public function hookBackOfficeHeader($params)
    {
        if (!$this->active) {
            return;
        }
        $this->loadGlobalAsset();
        if (Tools::isSubmit('submitFavIconConf')) {
            $this->saveForm();
        }
        $params = $this->getParams();
        // controller url
        $adminController = $this->context->link->getAdminLink('AdminAjaxFaviconBO');
        $this->context->smarty->assign(array(
            'bofavicon_params' => $params,
            'adminController' => $adminController,
        ));
        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/faviconbo.tpl');
    }

    public function saveForm()
    {
        Configuration::updateValue('BACKGROUND_COLOR_FAVICONBO', Tools::getValue('BACKGROUND_COLOR_FAVICONBO'));
        Configuration::updateValue('TEXT_COLOR_FAVICONBO', Tools::getValue('TEXT_COLOR_FAVICONBO'));
        Configuration::updateValue('CHECKBOX_ORDER', Tools::getValue('CHECKBOX_ORDER'));
        Configuration::updateValue('CHECKBOX_CUSTOMER', Tools::getValue('CHECKBOX_CUSTOMER'));
        Configuration::updateValue('CHECKBOX_MESSAGE', Tools::getValue('CHECKBOX_MESSAGE'));
    }
}
