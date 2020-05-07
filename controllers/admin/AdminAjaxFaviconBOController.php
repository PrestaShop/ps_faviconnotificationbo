<?php
/**
* 2007-2018 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Domain\Notification\Query\GetNotificationLastElements;
use PrestaShop\PrestaShop\Core\Domain\Notification\QueryResult\NotificationsResults;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class AdminAjaxFaviconBOController extends ModuleAdminController
{
    public function ajaxProcessGetNotifications()
    {
        /** @var NotificationsResults $elements */
        $elements = SymfonyContainer::getInstance()->get('prestashop.core.query_bus')->handle(
            new GetNotificationLastElements(Context::getContext()->employee->id)
        );
        header('Content-Type: application/json');
        $this->ajaxRender(json_encode($elements->getNotificationsResultsForJS()));
    }
}
