<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/ougc/Awards/class_alerts.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Manage a powerful awards system for your community.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\Awards\Core;

use MybbStuff_MyAlerts_Entity_Alert;
use MybbStuff_MyAlerts_Formatter_AbstractFormatter;

class MyAlertsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
{
    public function init(): bool
    {
        loadLanguage();

        return true;
    }

    /**
     * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
     *
     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
     *
     * @return string The formatted alert string.
     */
    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert): string
    {
        $Details = $alert->toArray();

        $ExtraDetails = $alert->getExtraDetails();

        $awardData = awardGet((int)$Details['object_id']);

        $awardName = htmlspecialchars_uni($awardData['name']);

        $awardImage = $awardClass = awardGetIcon((int)$Details['object_id']);

        $awardImage = eval(
        getTemplate(
            $awardData['template'] === AWARD_TEMPLATE_TYPE_CLASS ? 'awardImageClass' : 'awardImage'
        )
        );

        return $this->lang->sprintf(
            $this->lang->ougcAwardsMyAlerts,
            htmlspecialchars_uni($this->mybb->user['username']),
            $outputAlert['from_user'],
            $awardName,
            $awardImage
        );
    }

    /**
     * Build a link to an alert's content so that the system can redirect to it.
     *
     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
     *
     * @return string The built alert, preferably an absolute link.
     */
    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert): string
    {
        global $settings;

        return $settings['bburl'] . '/' . urlHandlerBuild([
                'action' => 'myAwards'
            ]);
    }
}