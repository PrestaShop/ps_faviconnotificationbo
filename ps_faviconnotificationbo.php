<?php
/**
 * 2007-2020 PrestaShop and Contributors
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_faviconnotificationbo extends Module
{
    /**
     * @var string
     */
    public $confirmUninstall;

    /**
     * @var bool
     */
    public $bootstrap;

    public $adminControllers = [
        'adminConfigure' => 'AdminConfigureFaviconBo',
    ];

    public $hooks = [
        'displayBackOfficeHeader',
    ];

    public function __construct()
    {
        $this->name = 'ps_faviconnotificationbo';
        $this->tab = 'administration';
        $this->version = '2.1.0';
        $this->author = 'PrestaShop';
        $this->bootstrap = true;
        $this->module_key = '91315ca88851b6c2852ee4be0c59b7b1';

        parent::__construct();

        $this->displayName = $this->trans('Order Notifications on the Favicon', [], 'Modules.Faviconnotificationbo.Admin');
        $this->description = $this->trans('Be notified of each new order, client or message directly in the browser tab of your back office, even when working on another page', [], 'Modules.Faviconnotificationbo.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Admin.Modules.Notification');
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook($this->hooks)
            && $this->installConfiguration()
            && $this->installTabs();
    }

    /**
     * @return bool
     */
    public function installConfiguration()
    {
        return (bool) Configuration::updateValue('CHECKBOX_ORDER', '1')
            && (bool) Configuration::updateValue('CHECKBOX_CUSTOMER', '1')
            && (bool) Configuration::updateValue('CHECKBOX_MESSAGE', '1')
            && (bool) Configuration::updateValue('BACKGROUND_COLOR_FAVICONBO', '#DF0067')
            && (bool) Configuration::updateValue('TEXT_COLOR_FAVICONBO', '#FFFFFF');
    }

    /**
     * @return bool
     */
    public function installTabs()
    {
        $result = true;

        foreach ($this->adminControllers as $controller_name) {
            if (Tab::getIdFromClassName($controller_name)) {
                continue;
            }

            $tab = new Tab();
            $tab->class_name = $controller_name;
            $tab->module = $this->name;
            $tab->active = true;
            $tab->id_parent = -1;
            $tab->name = array_fill_keys(
                Language::getIDs(false),
                $this->displayName
            );
            $result = $result && (bool) $tab->add();
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallConfiguration()
            && $this->uninstallTabs();
    }

    /**
     * @return bool
     */
    public function uninstallConfiguration()
    {
        return (bool) Configuration::deleteByName('CHECKBOX_ORDER')
            && (bool) Configuration::deleteByName('CHECKBOX_CUSTOMER')
            && (bool) Configuration::deleteByName('CHECKBOX_MESSAGE')
            && (bool) Configuration::deleteByName('BACKGROUND_COLOR_FAVICONBO')
            && (bool) Configuration::deleteByName('TEXT_COLOR_FAVICONBO');
    }

    /**
     * @return bool
     */
    public function uninstallTabs()
    {
        $result = true;

        foreach (Tab::getCollectionFromModule($this->name) as $tab) {
            /** @var Tab $tab */
            $result = $result && (bool) $tab->delete();
        }

        return $result;
    }

    /**
     * Redirect to our ModuleAdminController when click on Configure button
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink($this->adminControllers['adminConfigure']));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayBackOfficeHeader(array $params)
    {
        $this->context->controller->addJS([
            $this->getPathUri() . 'views/js/favico.js',
            $this->getPathUri() . 'views/js/ps_faviconnotificationbo.js',
        ]);

        $this->context->smarty->assign([
            'bofaviconBgColor' => Configuration::get('BACKGROUND_COLOR_FAVICONBO'),
            'bofaviconTxtColor' => Configuration::get('TEXT_COLOR_FAVICONBO'),
            'bofaviconOrder' => Configuration::get('CHECKBOX_ORDER'),
            'bofaviconCustomer' => Configuration::get('CHECKBOX_CUSTOMER'),
            'bofaviconMsg' => Configuration::get('CHECKBOX_MESSAGE'),
            'bofaviconUrl' => $this->context->link->getAdminLink('AdminCommon'),
        ]);

        return $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/displayBackOfficeHeader.tpl');
    }
}
