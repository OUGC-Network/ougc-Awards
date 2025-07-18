<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/ougc/Awards/admin.php)
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

namespace ougc\Awards\Admin;

use MyBB;
use DirectoryIterator;
use MybbStuff_MyAlerts_AlertTypeManager;
use MybbStuff_MyAlerts_Entity_AlertType;
use PluginLibrary;
use stdClass;

use function ougc\Awards\Core\allowImports;
use function ougc\Awards\Core\awardsCacheGet;
use function ougc\Awards\Core\cacheUpdate;
use function ougc\Awards\Core\categoryInsert;
use function ougc\Awards\Core\getSetting;
use function ougc\Awards\Core\grantCount;
use function ougc\Awards\Core\grantGet;
use function ougc\Awards\Core\grantUpdate;
use function ougc\Awards\Core\loadLanguage;

use const MYBB_ROOT;
use const ougc\Awards\ROOT;
use const ougc\Awards\Core\ADMIN_PERMISSION_DELETE;
use const ougc\Awards\Core\PLUGIN_VERSION;
use const ougc\Awards\Core\PLUGIN_VERSION_CODE;
use const ougc\Awards\Core\REQUEST_STATUS_REJECTED;
use const ougc\Awards\Core\TABLES_DATA;
use const ougc\Awards\Core\FIELDS_DATA;
use const ougc\Awards\Core\TASK_TYPE_REVOKE;
use const ougc\Awards\Core\TASK_STATUS_DISABLED;

const TASK_ENABLE = 1;

const TASK_DEACTIVATE = 0;

const TASK_DELETE = -1;

function pluginInfo(): array
{
    global $lang;

    loadLanguage();

    $descriptionText = $lang->ougcAwardsDescription;

    if (allowImports()) {
        $descriptionText .= $lang->ougcAwardsImportDescription;
    }

    return [
        'name' => 'ougc Awards',
        'description' => $descriptionText,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => PLUGIN_VERSION,
        'versioncode' => PLUGIN_VERSION_CODE,
        'compatibility' => '18*',
        'myalerts' => getSetting('myAlertsVersion'),
        'codename' => 'ougc_awards',
        'newpoints' => '3.1.0',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function pluginActivate(): bool
{
    global $PL, $cache, $lang;

    loadLanguage();

    $pluginInfo = pluginInfo();

    loadPluginLibrary();

    $settingsContents = file_get_contents(ROOT . '/settings.json');

    $settingsData = json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougc_awards_{$settingKey}"})) {
            continue;
        }

        if ($settingData['optionscode'] == 'select' || $settingData['optionscode'] == 'checkbox') {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougc_awards_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougc_awards_{$settingKey}"};

        $settingData['description'] = $lang->{"setting_ougc_awards_{$settingKey}_desc"};
    }

    $PL->settings(
        'ougc_awards',
        $lang->setting_group_ougc_awards,
        $lang->setting_group_ougc_awards_desc,
        $settingsData
    );

    $templates = [];

    if (file_exists($templateDirectory = ROOT . '/templates')) {
        $templatesDirIterator = new DirectoryIterator($templateDirectory);

        foreach ($templatesDirIterator as $template) {
            if (!$template->isFile()) {
                continue;
            }

            $pathName = $template->getPathname();

            $pathInfo = pathinfo($pathName);

            if ($pathInfo['extension'] === 'html') {
                $templates[$pathInfo['filename']] = file_get_contents($pathName);
            }
        }
    }

    if ($templates) {
        $PL->templates('ougcawards', 'ougc Awards', $templates);
    }

    global $db, $mybb;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        global $alertTypeManager;

        isset($alertTypeManager) || $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance(
            $db,
            $mybb->cache
        );

        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();

        $alertType->setCode('ougc_awards');

        $alertType->setEnabled();

        $alertType->setCanBeUserDisabled();

        $alertTypeManager->add($alertType);
    }

    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    if (!isset($plugins['awards'])) {
        $plugins['awards'] = $pluginInfo['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    if ($db->table_exists('ougc_awards_tasks')) {
        if ($db->field_exists('ougc_customrepids_r', 'ougc_awards_tasks')) {
            $db->drop_column('ougc_awards_tasks', 'ougc_customrepids_r');
        }
    }

    if ($db->table_exists('ougc_awards_tasks')) {
        if ($db->field_exists('ougc_customrepids_g', 'ougc_awards_tasks')) {
            $db->drop_column('ougc_awards_tasks', 'ougc_customrepids_g');
        }

        if ($db->field_exists('revoke', 'ougc_awards_tasks') &&
            !$db->field_exists('revokeAwardID', 'ougc_awards_tasks')) {
            $db->rename_column(
                'ougc_awards_tasks',
                'revoke',
                'revokeAwardID',
                dbBuildFieldDefinition(TABLES_DATA['ougc_awards_tasks']['revokeAwardID'])
            );
        }
    }

    if ($db->table_exists('ougc_awards_tasks')) {
        if ($db->index_exists('ougc_awards_users', 'uidaid')) {
            $db->drop_index('ougc_awards_users', 'uidaid');
        }

        if ($db->index_exists('ougc_awards_users', 'aiduid')) {
            $db->drop_index('ougc_awards_users', 'uidaid');
        }
    }

    if ($plugins['awards'] <= 1834) {
        $db->update_query('ougc_awards_tasks', ['active' => TASK_STATUS_DISABLED], "give LIKE '%,%'");

        $db->update_query('ougc_awards_tasks', ['active' => TASK_STATUS_DISABLED], "`revokeAwardID` LIKE '%,%'");

        $db->update_query('ougc_awards_tasks', ['taskType' => TASK_TYPE_REVOKE], "give='' AND `revokeAwardID`!=''");

        $db->update_query('ougc_awards_requests ', ['status' => REQUEST_STATUS_REJECTED], 'status="-1"');
    }

    if ($plugins['awards'] <= 1803) {
        $dbQuery = $db->simple_select('ougc_awards', 'aid');

        if ($db->num_rows($dbQuery)) {
            $categoryID = categoryInsert([
                'name' => 'Default',
                'description' => 'Default category created after an update.',
                'disporder' => 1
            ]);

            $db->update_query('ougc_awards', ['cid' => $categoryID]);
        }
    }

    if ($plugins['awards'] <= 1800) {
        $tmpls = [
            'modcp_ougc_awards' => 'ougcawards_modcp',
            'modcp_ougc_awards_manage' => 'ougcawards_modcp_manage',
            'modcp_ougc_awards_nav' => 'ougcawards_modcp_nav',
            'modcp_ougc_awards_list' => 'ougcawards_modcp_list',
            'modcp_ougc_awards_list_empty' => 'ougcawards_modcp_list_empty',
            'modcp_ougc_awards_list_award' => 'ougcawards_modcp_list_award',
            'modcp_ougc_awards_manage_reason' => 'ougcawards_modcp_manage_reason',
            'postbit_ougc_awards' => 'ougcawards_postbit',
            'member_profile_ougc_awards_row_empty' => 'ougcawards_profile_row_empty',
            'member_profile_ougc_awards_row' => 'ougcawards_profile_row',
            'member_profile_ougc_awards' => 'ougcawards_profile',
            'ougc_awards_page' => 'ougcawards_page',
            'ougc_awards_page_list' => 'ougcawards_page_list',
            'ougc_awards_page_list_award' => 'ougcawards_page_list_award',
            'ougc_awards_page_list_empty' => 'ougcawards_page_list_empty',
            'ougc_awards_page_user' => 'ougcawards_page_user',
            'ougc_awards_page_user_award' => 'ougcawards_page_user_award',
            'ougc_awards_page_user_empty' => 'ougcawards_page_user_empty',
            'ougc_awards_page_view' => 'ougcawards_page_view',
            'ougc_awards_page_view_empty' => 'ougcawards_page_view_empty',
            'ougc_awards_page_view_row' => 'ougcawards_page_view_row',
        ];

        $templateNames = implode("','", $tmpls);

        // Try to update old templates
        $query = $db->simple_select('templates', 'template, title, template', "title IN ('{$templateNames}')");
        while ($tmpl = $db->fetch_array($query)) {
            check_template($tmpl['template']) || $tmplcache[$tmpl['title']] = $tmpl;
        }

        foreach ($tmpls as $oldtitle => $newtitle) {
            $db->update_query('templates', [
                'title' => $db->escape_string($newtitle),
                'version' => 1,
                'dateline' => TIME_NOW
            ], 'title=\'' . $db->escape_string($oldtitle) . '\' AND sid=\'-2\'');
        }

        // Rebuild templates
        static $done = false;
        if (!$done) {
            $done = true;
            $funct = __FUNCTION__;
            $funct();
        }

        // Delete old templates if not updated
        $tmpls['ougc_awards_image'] = '';
        $db->delete_query(
            'templates',
            'title IN(\'' . implode(
                '\', \'',
                array_keys(array_map([$db, 'escape_string'], $tmpls))
            ) . '\') AND sid=\'-2\''
        );

        // Delete old template group
        $db->delete_query('templategroups', 'prefix=\'ougc_awards\'');
    }

    /*~*~* RUN UPDATES END *~*~*/

    dbVerifyTables();

    dbVerifyColumns();

    enableTask();

    cacheUpdate();

    change_admin_permission('tools', 'ougc_awards', ADMIN_PERMISSION_DELETE);

    $plugins['awards'] = $pluginInfo['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    return true;
}

function pluginDeactivate(): bool
{
    disableTask();

    return true;
}

function pluginIsInstalled(): bool
{
    static $isInstalled = null;

    if ($isInstalled === null) {
        global $db;

        $isInstalledEach = true;

        foreach (TABLES_DATA as $tableName => $tableColumns) {
            $isInstalledEach = $db->table_exists($tableName) && $isInstalledEach;
        }

        $isInstalled = $isInstalledEach;
    }

    return $isInstalled;
}

function pluginUninstall(): bool
{
    global $db, $PL, $cache;

    loadPluginLibrary();

    foreach (TABLES_DATA as $tableName => $tableData) {
        if ($db->table_exists($tableName)) {
            $db->drop_table($tableName);
        }
    }

    foreach (FIELDS_DATA as $tableName => $tableColumns) {
        if ($db->table_exists($tableName)) {
            foreach ($tableColumns as $fieldName => $fieldDefinition) {
                if ($db->field_exists($fieldName, $tableName)) {
                    $db->drop_column($tableName, $fieldName);
                }
            }
        }
    }

    $PL->settings_delete('ougc_awards');

    $PL->templates_delete('ougcawards');

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        global $alertTypeManager;

        isset($alertTypeManager) || $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance(
            $db,
            $cache
        );

        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        $alertTypeManager->deleteByCode('ougc_awards');
    }

    deleteTask();

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['awards'])) {
        unset($plugins['awards']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }

    return true;
}

function pluginLibraryRequirements(): stdClass
{
    return (object)pluginInfo()['pl'];
}

function loadPluginLibrary(): bool
{
    global $PL, $lang;

    loadLanguage();

    $fileExists = file_exists(PLUGINLIBRARY);

    if ($fileExists && !($PL instanceof PluginLibrary)) {
        require_once PLUGINLIBRARY;
    }

    if (!$fileExists || $PL->version < pluginLibraryRequirements()->version) {
        flash_message(
            $lang->sprintf(
                $lang->ougcAwardsPluginLibrary,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function enableTask(int $action = TASK_ENABLE): bool
{
    global $db, $lang;

    loadLanguage();

    if ($action === TASK_DELETE) {
        $db->delete_query('tasks', "file='ougc_awards'");

        return true;
    }

    $query = $db->simple_select('tasks', '*', "file='ougc_awards'", ['limit' => 1]);

    $task = $db->fetch_array($query);

    if ($task) {
        $db->update_query('tasks', ['enabled' => $action], "file='ougc_awards'");
    } else {
        include_once MYBB_ROOT . 'inc/functions_task.php';

        $_ = $db->escape_string('*');

        $new_task = [
            'title' => $db->escape_string($lang->setting_group_ougc_awards),
            'description' => $db->escape_string($lang->setting_group_ougc_awards_desc),
            'file' => $db->escape_string('ougc_awards'),
            'minute' => 0,
            'hour' => $_,
            'day' => $_,
            'weekday' => $_,
            'month' => $_,
            'enabled' => 1,
            'logging' => 1
        ];

        $new_task['nextrun'] = fetch_next_run($new_task);

        $db->insert_query('tasks', $new_task);
    }

    return true;
}

function disableTask(): bool
{
    enableTask(TASK_DEACTIVATE);

    return true;
}

function deleteTask(): bool
{
    enableTask(TASK_DELETE);

    return true;
}

function dbTables(): array
{
    $tables_data = [];

    foreach (TABLES_DATA as $tableName => $tableColumns) {
        foreach ($tableColumns as $fieldName => $fieldData) {
            if (!isset($fieldData['type'])) {
                continue;
            }

            $tables_data[$tableName][$fieldName] = dbBuildFieldDefinition($fieldData);
        }

        foreach ($tableColumns as $fieldName => $fieldData) {
            if (isset($fieldData['primary_key'])) {
                $tables_data[$tableName]['primary_key'] = $fieldName;
            }

            if ($fieldName === 'unique_key') {
                $tables_data[$tableName]['unique_key'] = $fieldData;
            }
        }
    }

    return $tables_data;
}

function dbVerifyTables(): bool
{
    global $db;

    $collation = $db->build_create_table_collation();

    foreach (dbTables() as $tableName => $tableColumns) {
        if ($db->table_exists($tableName)) {
            foreach ($tableColumns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key' || $fieldName == 'unique_key') {
                    continue;
                }

                if ($db->field_exists($fieldName, $tableName)) {
                    $db->modify_column($tableName, "`{$fieldName}`", $fieldData);
                } else {
                    $db->add_column($tableName, $fieldName, $fieldData);
                }
            }
        } else {
            $query_string = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$tableName}` (";

            foreach ($tableColumns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key') {
                    $query_string .= "PRIMARY KEY (`{$fieldData}`)";
                } elseif ($fieldName != 'unique_key') {
                    $query_string .= "`{$fieldName}` {$fieldData},";
                }
            }

            $query_string .= ") ENGINE=MyISAM{$collation};";

            $db->write_query($query_string);
        }
    }

    dbVerifyIndexes();

    return true;
}

function dbVerifyIndexes(): bool
{
    global $db;

    foreach (dbTables() as $tableName => $tableColumns) {
        if (!$db->table_exists($tableName)) {
            continue;
        }

        if (isset($tableColumns['unique_key'])) {
            foreach ($tableColumns['unique_key'] as $key_name => $key_value) {
                if ($db->index_exists($tableName, $key_name)) {
                    continue;
                }

                $db->write_query(
                    "ALTER TABLE {$db->table_prefix}{$tableName} ADD UNIQUE KEY {$key_name} ({$key_value})"
                );
            }
        }
    }

    return true;
}

function dbVerifyColumns(): bool
{
    global $db;

    foreach (FIELDS_DATA as $tableName => $tableColumns) {
        if (!$db->table_exists($tableName)) {
            continue;
        }

        foreach ($tableColumns as $fieldName => $fieldData) {
            if (!isset($fieldData['type'])) {
                continue;
            }

            if ($db->field_exists($fieldName, $tableName)) {
                $db->modify_column($tableName, "`{$fieldName}`", dbBuildFieldDefinition($fieldData));
            } else {
                $db->add_column($tableName, $fieldName, dbBuildFieldDefinition($fieldData));
            }
        }
    }

    return true;
}

function dbBuildFieldDefinition(array $fieldData): string
{
    $field_definition = '';

    $field_definition .= $fieldData['type'];

    if (isset($fieldData['size'])) {
        $field_definition .= "({$fieldData['size']})";
    }

    if (isset($fieldData['unsigned'])) {
        if ($fieldData['unsigned'] === true) {
            $field_definition .= ' UNSIGNED';
        } else {
            $field_definition .= ' SIGNED';
        }
    }

    if (!isset($fieldData['null'])) {
        $field_definition .= ' NOT';
    }

    $field_definition .= ' NULL';

    if (isset($fieldData['auto_increment'])) {
        $field_definition .= ' AUTO_INCREMENT';
    }

    if (isset($fieldData['default'])) {
        $field_definition .= " DEFAULT '{$fieldData['default']}'";
    }

    return $field_definition;
}

function recountRebuildAwardGrantsDisplayOrder(): void
{
    global $mybb, $lang;

    loadLanguage();

    $totalGrants = grantCount();

    $currentPage = $mybb->get_input('page', MyBB::INPUT_INT);

    $perPage = 50;

    $start = ($currentPage - 1) * $perPage;

    $end = $start + $perPage;

    $awardsCache = awardsCacheGet()['awards'];

    foreach (grantGet([], ['aid'], ['limit_start' => $start, 'limit' => $perPage]) as $grantID => $grantData) {
        grantUpdate(
            ['disporder' => $awardsCache[$grantData['aid']]['disporder'] ?? 0],
            $grantID
        );
    }

    check_proceed(
        $totalGrants['total_grants'] ?? 0,
        $end,
        ++$currentPage,
        $perPage,
        'ougc_awards_rebuild_grants_display_order',
        'do_rebuild_ougc_awards_grants_display_order',
        $lang->ougcAwardsRecountRebuildGrantsDisplayOrderSuccess
    );
}