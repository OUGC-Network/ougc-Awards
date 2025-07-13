<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/ougc/Awards/hooks/shared.php)
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

namespace ougc\Awards\Hooks\Shared;

use UserDataHandler;

use const ougc\Awards\ROOT;

function datahandler_user_insert(UserDataHandler &$dataHandler): UserDataHandler
{
    $dataHandler->user_insert_data['ougc_awards'] = '';

    return $dataHandler;
}

function datahandler_user_delete_end(UserDataHandler &$dataHandler): UserDataHandler
{
    global $db;

    $db->delete_query('ougc_awards_users', "uid IN ({$dataHandler->delete_uids})");

    $db->delete_query('ougc_awards_category_owners', "userID IN ({$dataHandler->delete_uids})");

    $db->delete_query('ougc_awards_owners', "uid IN ({$dataHandler->delete_uids})");

    $db->delete_query('ougc_awards_requests', "uid IN ({$dataHandler->delete_uids})");

    $db->delete_query('ougc_awards_tasks_logs', "uid IN ({$dataHandler->delete_uids})");

    $db->delete_query('ougc_awards_presets', "uid IN ({$dataHandler->delete_uids})");

    return $dataHandler;
}

function ougc_theme_file_templates_get(array &$hookArguments): array
{
    static $templates = null;

    $templates !== null || $templates = array_map(function (string $template_name): string {
        return 'ougcawards_' . $template_name;
    }, [
        'awardImage',
        'awardImageClass',
        'awardWrapper',
        'checkBoxField',
        'controlPanel',
        'controlPanelButtons',
        'controlPanelCategoryOwners',
        'controlPanelConfirmation',
        'controlPanelConfirmationDeleteAward',
        'controlPanelConfirmationDeleteCategory',
        'controlPanelConfirmationDeleteOwner',
        'controlPanelContents',
        'controlPanelEmpty',
        'controlPanelGrantEdit',
        'controlPanelList',
        'controlPanelListButtonUpdateCategory',
        'controlPanelListCategoryLinks',
        'controlPanelListCategoryLinksModerator',
        'controlPanelListColumnDisplayOrder',
        'controlPanelListColumnEnabled',
        'controlPanelListColumnOptions',
        'controlPanelListColumnRequest',
        'controlPanelListRow',
        'controlPanelListRowDisplayOrder',
        'controlPanelListRowEmpty',
        'controlPanelListRowEnabled',
        'controlPanelListRowOptions',
        'controlPanelListRowRequest',
        'controlPanelListRowRequestButton',
        'controlPanelLogs',
        'controlPanelLogsEmpty',
        'controlPanelLogsPagination',
        'controlPanelLogsRow',
        'controlPanelMyAwards',
        'controlPanelMyAwardsEmpty',
        'controlPanelMyAwardsHeaderDisplayOrder',
        'controlPanelMyAwardsPagination',
        'controlPanelMyAwardsRow',
        'controlPanelMyAwardsRowDisplayOrder',
        'controlPanelMyAwardsRowLink',
        'controlPanelNewEditAwardForm',
        'controlPanelNewEditAwardFormUpload',
        'controlPanelNewEditCategoryForm',
        'controlPanelNewEditTaskForm',
        'controlPanelNewEditTaskFormRequirementRow',
        'controlPanelOwners',
        'controlPanelOwnersEmpty',
        'controlPanelOwnersRow',
        'controlPanelPresets',
        'controlPanelPresetsAward',
        'controlPanelPresetsDefault',
        'controlPanelPresetsForm',
        'controlPanelPresetsRow',
        'controlPanelPresetsSelect',
        'controlPanelRequests',
        'controlPanelRequestsEmpty',
        'controlPanelRequestsRow',
        'controlPanelTasks',
        'controlPanelTasksEmpty',
        'controlPanelTasksRow',
        'controlPanelTasksRowOptions',
        'controlPanelTasksRowRequirement',
        'controlPanelTasksThead',
        'controlPanelUsers',
        'controlPanelUsersColumnOptions',
        'controlPanelUsersEmpty',
        'controlPanelUsersForm',
        'controlPanelUsersFormGrant',
        'controlPanelUsersFormRevoke',
        'controlPanelUsersRow',
        'controlPanelUsersRowLink',
        'controlPanelUsersRowOptions',
        'controlPanelUsersRowSelect',
        'css',
        'global_menu',
        'globalNotification',
        'globalPagination',
        'inputField',
        'js',
        'modcp_requests_buttons',
        'page',
        'pageRequest',
        'pageRequestButton',
        'pageRequestError',
        'pageRequestForm',
        'pageRequestSuccess',
        'postBit',
        'postBitContent',
        'postBitEmpty',
        'postBitPagination',
        'postBitPreset',
        'postBitPresets',
        'postBitPresetsRow',
        'postBitPresetsRowLink',
        'postBitRow',
        'postBitRowLink',
        'postBitRowTotalCount',
        'postBitViewAll',
        'postBitViewAllSection',
        'profile',
        'profileContent',
        'profileEmpty',
        'profilePagination',
        'profilePresets',
        'profilePresetsRow',
        'profilePresetsRowLink',
        'profileRow',
        'profileRowLink',
        'profileRowTotalCount',
        'profileViewAll',
        'profileViewAllSection',
        'radioField',
        'selectField',
        'selectFieldOption',
        'stats',
        'stats_empty',
        'statsUserRow',
        'streamItem',
        'textAreaField',
        'viewAll',
        'viewAllSection',
        'viewUser',
        'viewUserEmpty',
        'viewUserError',
        'viewUserRow'
    ]);

    if (!in_array($hookArguments['title'], $templates)) {
        return $hookArguments;
    }

    $filePath = ROOT . '/templates/' . str_replace('ougcawards_', '', $hookArguments['title']) . '.html';

    if ($filePath) {
        $hookArguments['filePath'] = $filePath;
    }

    return $hookArguments;
}