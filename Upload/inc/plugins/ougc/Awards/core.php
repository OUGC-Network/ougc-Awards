<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/ougc/Awards/core.php)
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

use Exception;
use JetBrains\PhpStorm\Deprecated;
use MyBB;
use pluginSystem;
use postParser;
use MybbStuff_MyAlerts_AlertManager;
use MybbStuff_MyAlerts_AlertTypeManager;
use MybbStuff_MyAlerts_Entity_Alert;

use function ougc\Awards\Hooks\Forum\myalerts_register_client_alert_formatters;

use const TIME_NOW;
use const ougc\Awards\ROOT;

const PLUGIN_VERSION = '2.2.0';

const PLUGIN_VERSION_CODE = 2200;

const URL = 'awards.php';

const ADMIN_PERMISSION_DELETE = -1;

const AWARD_TEMPLATE_TYPE_IMAGE = 0;

const AWARD_TEMPLATE_TYPE_CLASS = 1;

const AWARD_TEMPLATE_TYPE_CUSTOM = 2;

const AWARD_ALLOW_REQUESTS = 1;

const AWARD_STATUS_DISABLED = 0;

const AWARD_STATUS_ENABLED = 1;

const TASK_TYPE_GRANT = 1;

const TASK_TYPE_REVOKE = 2;

const TASK_STATUS_DISABLED = 0;

const TASK_STATUS_ENABLED = 1;

const TASK_ALLOW_MULTIPLE = 1;

const GRANT_STATUS_EVERYWHERE = 0;

const GRANT_STATUS_PROFILE = 1;

const GRANT_STATUS_POSTS = 2;

const GRANT_STATUS_NOT_VISIBLE = 0;

const GRANT_STATUS_VISIBLE = 1;

const REQUEST_STATUS_REJECTED = 2;

const REQUEST_STATUS_ACCEPTED = 0;

const REQUEST_STATUS_PENDING = 1;

const FILE_UPLOAD_ERROR_FAILED = 1;

const FILE_UPLOAD_ERROR_INVALID_TYPE = 2;

const FILE_UPLOAD_ERROR_UPLOAD_SIZE = 3;

const FILE_UPLOAD_ERROR_RESIZE = 4;

const AWARDS_SECTION_NONE = 0;

const TABLES_DATA = [
    'ougc_awards_categories' => [
        'cid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'name' => [
            'type' => 'VARCHAR',
            'size' => 100,
            'default' => ''
        ],
        'description' => [
            'type' => 'VARCHAR',
            'size' => 255,
            'default' => ''
        ],
        'disporder' => [
            'type' => 'SMALLINT',
            'unsigned' => true,
            'default' => 0
        ],
        'allowrequests' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'visible' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'outputInCustomSection' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'hideInMainPage' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'ougc_awards' => [
        'aid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'cid' => [
            'type' => 'INT',
            'unsigned' => true
        ],
        'name' => [
            'type' => 'VARCHAR',
            'size' => 100,
            'default' => ''
        ],
        'description' => [
            'type' => 'VARCHAR',
            'size' => 255,
            'default' => ''
        ],
        'award_file' => [
            'type' => 'VARCHAR',
            'size' => 255,
            'default' => ''
        ],
        'image' => [
            'type' => 'VARCHAR',
            'size' => 255,
            'default' => ''
        ],
        'template' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'disporder' => [
            'type' => 'SMALLINT',
            'unsigned' => true,
            'default' => 0
        ],
        'allowrequests' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'visible' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'pm' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'type' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ]
    ],
    'ougc_awards_users' => [
        'gid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'oid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'aid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'rid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'tid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'thread' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'reason' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'pm' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'date' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'disporder' => [
            'type' => 'SMALLINT',
            'unsigned' => true,
            'default' => 0
        ],
        'visible' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        //'visible' => ['uidaid' => 'uid,aid', 'aiduid' => 'aid,uid']
    ],
    'ougc_awards_category_owners' => [
        'ownerID' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'userID' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'categoryID' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'ownerDate' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'ougc_awards_owners' => [
        'oid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'aid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'date' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'ougc_awards_requests' => [
        'rid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'aid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'muid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'message' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'status' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
    ],
    'ougc_awards_tasks' => [
        'tid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'name' => [
            'type' => 'VARCHAR',
            'size' => 100,
            'default' => ''
        ],
        'description' => [
            'type' => 'VARCHAR',
            'size' => 255,
            'default' => ''
        ],
        'active' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'taskType' => [
            'type' => 'TINYINT',
            'size' => 1,
            'default' => 1
        ],
        'logging' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'requirements' => [
            'type' => 'VARCHAR',
            'size' => 400,
            'default' => ''
        ],
        'give' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'reason' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'thread' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        /*'allowmultiple' => [
            'type' => 'SMALLINT',
            'unsigned' => true,
            'default' => 0
        ],*/
        'revokeAwardID' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'disporder' => [
            'type' => 'SMALLINT',
            'unsigned' => true,
            'default' => 0
        ],
        'usergroups' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'additionalgroups' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'threads' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'threadstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'posts' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'poststype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'fthreads' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'fthreadstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'fthreadsforums' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'fposts' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'fpoststype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'fpostsforums' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'registered' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'registeredtype' => [
            'type' => 'VARCHAR',
            'size' => 5,
            'default' => ''
        ],
        'online' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'onlinetype' => [
            'type' => 'VARCHAR',
            'size' => 10,
            'default' => ''
        ],
        'reputation' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'reputationtype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'referrals' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'referralstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'warnings' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'warningstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'previousawards' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'profilefields' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        /*'mydownloads' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'mydownloadstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'myarcadechampions' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'myarcadechampionstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'myarcadescores' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'myarcadescorestype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],*/
        'ruleScripts' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'newpoints' => [
            'type' => 'FLOAT',
            'unsigned' => true,
            'default' => 0
        ],
        'newpointstype' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        /*
        'ougc_customrep_r' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_customreptype_r' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'ougc_customrepids_r' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'ougc_customrep_g' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_customreptype_g' => [
            'type' => 'VARCHAR',
            'size' => 2,
            'default' => ''
        ],
        'ougc_customrepids_g' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        */
    ],
    'ougc_awards_tasks_logs' => [
        'lid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'tid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'gave' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'revoked' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'date' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'unique_key' => ['task_user_id' => 'tid,uid']
    ],
    'ougc_awards_presets' => [
        'pid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'name' => [
            'type' => 'VARCHAR',
            'size' => 100,
            'default' => ''
        ],
        'hidden' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'visible' => [
            'type' => 'TEXT',
            'null' => true,
        ],
    ]
];

const FIELDS_DATA = [
    'users' => [
        'ougc_awards' => [
            'type' => 'TEXT',
            'null' => true
        ],
        'ougc_awards_category_owner' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_awards_owner' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_awards_preset' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ]
    ]
];

const TASK_REQUIREMENT_TYPE_GROUPS = 'usergroups';

const TASK_REQUIREMENT_TYPE_THREADS = 'threads';

const TASK_REQUIREMENT_TYPE_POSTS = 'posts';

const TASK_REQUIREMENT_TYPE_THREADS_FORUM = 'fthreads';

const TASK_REQUIREMENT_TYPE_POSTS_FORUM = 'fposts';

const TASK_REQUIREMENT_TYPE_REGISTRATION = 'registered';

const TASK_REQUIREMENT_TYPE_ONLINE = 'online';

const TASK_REQUIREMENT_TYPE_REPUTATION = 'reputation';

const TASK_REQUIREMENT_TYPE_REFERRALS = 'referrals';

const TASK_REQUIREMENT_TYPE_WARNINGS = 'warnings';

const TASK_REQUIREMENT_TYPE_AWARDS_GRANTED = 'previousawards';

const TASK_REQUIREMENT_TYPE_FILLED_PROFILE_FIELDS = 'profilefields';

const TASK_REQUIREMENT_TYPE_JSON_SCRIPT = 'ruleScripts';

const TASK_REQUIREMENT_TYPE_NEWPOINTS = 'newpoints';

const TASK_REQUIREMENT_TIME_TYPE_HOURS = 'hours';

const TASK_REQUIREMENT_TIME_TYPE_DAYS = 'days';

const TASK_REQUIREMENT_TIME_TYPE_WEEKS = 'weeks';

const TASK_REQUIREMENT_TIME_TYPE_MONTHS = 'months';

const TASK_REQUIREMENT_TIME_TYPE_YEARS = 'years';

const COMPARISON_TYPE_GREATER_THAN = '>';

const COMPARISON_TYPE_GREATER_THAN_OR_EQUAL = '>=';

const COMPARISON_TYPE_EQUAL = '=';

const COMPARISON_TYPE_NOT_EQUAL = '!=';

const COMPARISON_TYPE_LESS_THAN_OR_EQUAL = '<=';

const COMPARISON_TYPE_LESS_THAN = '<';

function addHooks(string $namespace): void
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            $isNegative = substr($hookName, -3, 1) === '_';

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            if ($isNegative) {
                $plugins->add_hook($hookName, $callable, -$priority);
            } else {
                $plugins->add_hook($hookName, $callable, $priority);
            }
        }
    }
}

function runHooks(string $hookName, array &$hookArguments = []): array
{
    if (getSetting('disablePlugins') !== false) {
        return $hookArguments;
    }

    global $plugins;

    if ($plugins instanceof pluginSystem) {
        $hookArguments = $plugins->run_hooks('ougc_awards_' . $hookName, $hookArguments);
    }

    return (array)$hookArguments;
}

function loadLanguage(bool $isDataHandler = false): bool
{
    global $lang;

    if ($isDataHandler && !isset($lang->ougcAwards) || !$isDataHandler && !isset($lang->ougcAwardsDescription)) {
        if (!$isDataHandler && defined('IN_ADMINCP')) {
            $lang->load('user_ougc_awards', $isDataHandler);
        } else {
            $lang->load('ougc_awards', $isDataHandler);
        }
    }

    return true;
}

function urlHandler(string $newUrl = ''): string
{
    static $setUrl = URL;

    if ($newUrl = trim($newUrl)) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlHandlerSet(string $newUrl): void
{
    urlHandler($newUrl);
}

function urlHandlerGet(): string
{
    return urlHandler();
}

function urlHandlerBuild(array $urlAppend = [], bool $fetchImportUrl = false, bool $encode = true): string
{
    global $PL;

    if (!is_object($PL)) {
        $PL or require_once PLUGINLIBRARY;
    }

    if ($fetchImportUrl === false) {
        if ($urlAppend && !is_array($urlAppend)) {
            $urlAppend = explode('=', $urlAppend);
            $urlAppend = [$urlAppend[0] => $urlAppend[1]];
        }
    }

    return $PL->url_append(urlHandlerGet(), $urlAppend, '&amp;', $encode);
}

function getTemplateName(string $templateName = ''): string
{
    $templatePrefix = '';

    if ($templateName) {
        $templatePrefix = '_';
    }

    return "ougcawards{$templatePrefix}{$templateName}";
}

function getTemplate(string $templateName = '', bool $enableHTMLComments = true): string
{
    global $templates;

    if (DEBUG) {
        $filePath = ROOT . "/templates/{$templateName}.html";

        $templateContents = file_get_contents($filePath);

        $templates->cache[getTemplateName($templateName)] = $templateContents;
    } elseif (my_strpos($templateName, '/') !== false) {
        $templateName = substr($templateName, strpos($templateName, '/') + 1);
    }

    return $templates->render(getTemplateName($templateName), true, $enableHTMLComments);
}

function getSetting(string $settingKey = '')
{
    global $mybb;

    return SETTINGS[$settingKey] ?? (
        $mybb->settings['ougc_awards_' . $settingKey] ?? false
    );
}

function executeTask(
    #[Deprecated]
    array $awardTaskData = []
): bool {
    global $db;

    loadLanguage(true);

    $tableQueryOptions = [];

    $tableQueryFields = ['u.uid', 'u.username'];

    $requirementCriteria = [
        TASK_REQUIREMENT_TYPE_GROUPS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            global $db;

            $groupIDs = array_map('intval', explode(',', $taskData[$requirementType]));

            $whereClause = ["u.usergroup IN ('" . implode("','", $groupIDs) . "')"];

            if (!empty($taskData['additionalgroups'])) {
                foreach ($groupIDs as $groupID) {
                    switch ($db->type) {
                        case 'pgsql':
                        case 'sqlite':
                            $whereClause[] = "','||u.additionalgroups||',' LIKE '%,{$groupID},%'";
                            break;
                        default:
                            $whereClause[] = "CONCAT(',',u.additionalgroups,',') LIKE '%,{$groupID},%'";
                            break;
                    }
                }
            }

            $userWhereClauses[] = '(' . implode(' OR ', $whereClause) . ')';

            return true;
        },
        TASK_REQUIREMENT_TYPE_THREADS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $userThreads = (int)$taskData[$requirementType];

                $userWhereClauses[] = "u.threadnum{$taskData[$requirementType.'type']}'{$userThreads}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_POSTS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $userThreads = (int)$taskData[$requirementType];

                $userWhereClauses[] = "u.postnum{$taskData[$requirementType.'type']}'{$userThreads}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_THREADS_FORUM => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $forumThreads = (int)$taskData[$requirementType];

                $forumIDs = implode(
                    "','",
                    array_map('intval', explode(',', $taskData[$requirementType . 'forums']))
                );

                global $db;

                $tableLeftJoins[] = "(
				SELECT uid, COUNT(tid) AS {$requirementType}
				FROM {$db->table_prefix}threads
				WHERE fid IN ('{$forumIDs}') AND visible > 0 AND closed NOT LIKE 'moved|%'
				GROUP BY uid, tid
			) t ON (t.uid=u.uid)";

                $whereClauses[] = "t.{$requirementType}{$taskData[$requirementType.'type']}'{$forumThreads}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_POSTS_FORUM => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $forumPosts = (int)$taskData[$requirementType];

                $forumIDs = implode(
                    "','",
                    array_map('intval', explode(',', $taskData[$requirementType . 'forums']))
                );

                global $db;

                $tableLeftJoins[] = "(
				SELECT p.uid, COUNT(p.pid) AS {$requirementType}
				FROM {$db->table_prefix}posts p
				LEFT JOIN {$db->table_prefix}threads t ON (t.tid=p.tid)
				WHERE p.fid IN ('{$forumIDs}') AND t.visible > 0 AND p.visible > 0
				GROUP BY p.uid, p.pid
			) p ON (p.uid=u.uid)";

                $whereClauses[] = "p.{$requirementType}{$taskData[$requirementType.'type']}'{$forumPosts}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_REGISTRATION => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            $registeredSeconds = (int)$taskData[$requirementType];

            switch ($taskData[$requirementType . 'type']) {
                case TASK_REQUIREMENT_TIME_TYPE_HOURS:
                    $registeredSeconds *= 60 * 60;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_DAYS:
                    $registeredSeconds *= 60 * 60 * 24;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_WEEKS:
                    $registeredSeconds *= 60 * 60 * 24 * 7;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_MONTHS:
                    $registeredSeconds *= 60 * 60 * 24 * 30;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_YEARS:
                    $registeredSeconds *= 60 * 60 * 24 * 365;
                    break;
            }

            $registeredSeconds = TIME_NOW - $registeredSeconds;

            if ($registeredSeconds > 0) {
                $userWhereClauses[] = "u.regdate<='{$registeredSeconds}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_ONLINE => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            $onlineSeconds = (int)$taskData[$requirementType];

            switch ($taskData[$requirementType . 'type']) {
                case TASK_REQUIREMENT_TIME_TYPE_HOURS:
                    $onlineSeconds *= 60 * 60;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_DAYS:
                    $onlineSeconds *= 60 * 60 * 24;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_WEEKS:
                    $onlineSeconds *= 60 * 60 * 24 * 7;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_MONTHS:
                    $onlineSeconds *= 60 * 60 * 24 * 30;
                    break;
                case TASK_REQUIREMENT_TIME_TYPE_YEARS:
                    $onlineSeconds *= 60 * 60 * 24 * 365;
                    break;
            }

            if ($onlineSeconds > 0) {
                $userWhereClauses[] = "u.timeonline>='{$onlineSeconds}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_REPUTATION => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $userReputation = (int)$taskData[$requirementType];

                $userWhereClauses[] = "u.{$requirementType}{$taskData[$requirementType.'type']}'{$userReputation}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_REFERRALS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $userReferrals = (int)$taskData[$requirementType];

                $userWhereClauses[] = "u.{$requirementType}{$taskData[$requirementType.'type']}'{$userReferrals}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_WARNINGS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (in_array($taskData[$requirementType . 'type'], array_keys(getComparisonTypes()))) {
                $userWarningPoints = (int)$taskData[$requirementType];

                $userWhereClauses[] = "u.warningpoints{$taskData[$requirementType.'type']}'{$userWarningPoints}'";

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_AWARDS_GRANTED => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (!empty($taskData[$requirementType])) {
                global $db;

                $awardIDs = implode("','", array_keys(awardsCacheGet()['awards']));

                foreach (array_map('intval', explode(',', $taskData[$requirementType])) as $previousAwardID) {
                    $tableLeftJoins[] = "(
                            SELECT g.uid, g.aid, COUNT(g.gid) AS {$requirementType}{$previousAwardID}
                            FROM {$db->table_prefix}ougc_awards_users g
                            WHERE g.aid='{$previousAwardID}' AND g.aid IN ('{$awardIDs}')
                            GROUP BY g.uid, g.aid, g.gid
                        ) aw{$previousAwardID} ON (aw{$previousAwardID}.uid=u.uid)";

                    $whereClauses[] = "aw{$previousAwardID}.{$requirementType}{$previousAwardID}>='1'";
                }

                return true;
            }

            return false;
        },
        TASK_REQUIREMENT_TYPE_FILLED_PROFILE_FIELDS => function (
            array $taskData,
            string $requirementType,
            array &$whereClauses,
            array &$tableLeftJoins,
            array &$userWhereClauses
        ): bool {
            if (!empty($taskData[$requirementType])) {
                global $db;

                $tableLeftJoins[] = "{$db->table_prefix}userfields uf ON (uf.ufid=u.uid)";

                foreach (array_map('intval', explode(',', $taskData[$requirementType])) as $fieldID) {
                    $whereClauses[] = "uf.fid{$fieldID}!=''";
                }

                return true;
            }

            return false;
        },
        /*
        TASK_REQUIREMENT_TYPE_NEWPOINTS => function (array $taskData, string $requirementType) use (
            &$tableLeftJoins,
            &$whereClauses
        ): bool {
            $userPoints = (float)$taskData[$requirementType];

            if ($userPoints >= 0 && !empty($taskData[$requirementType . 'type'])) {
                $userWhereClauses[] = "u.{$requirementType}{$taskData[$requirementType.'type']}'{$userPoints}'";
            }


            return true;
        },
        */
    ];

    $queryFields = array_keys(TABLES_DATA['ougc_awards_tasks']);

    $hookArguments = [
        //'taskData' => &$awardTaskData,
        'tableQueryOptions' => &$tableQueryOptions,
        'tableQueryFields' => &$tableQueryFields,
        'requirementTypes' => &$requirementCriteria,
        'queryFields' => &$queryFields
    ];

    // TODO mydownloads
    // TODO myarcadechampions
    // TODO myarcadescores
    // TODO ougc_customrep_r
    // TODO ougc_customrep_g

    $hookArguments = runHooks('task_initiate', $hookArguments);

    foreach (taskGet(["active='1'"], $queryFields, ['order_by' => 'disporder']) as $taskID => $awardTaskData) {
        $taskType = (int)$awardTaskData['taskType'];

        $hookArguments['awardTaskData'] = &$awardTaskData;

        $whereClauses = [];

        $hookArguments['whereClauses'] = &$whereClauses;

        $tableLeftJoins = ['users u'];

        $taskGrantAwardID = (int)$awardTaskData['give'];

        $taskRevokeAwardID = (int)$awardTaskData['revokeAwardID'];

        if ($taskType === TASK_TYPE_GRANT && $taskGrantAwardID) {
            // this is giving issues and admins can no longer edit this setting, so lest drop the logic for good
            /*if (empty($awardTaskData['allowmultiple'])) {
                $tableQueryFields[] = 'a.totalUserGrants';

                $tableLeftJoins[] = "(
                    SELECT uid, COUNT(aid) AS totalUserGrants
                    FROM {$db->table_prefix}ougc_awards_users
                    WHERE aid IN ('{$taskGrantAwardID}')
                    GROUP BY uid, aid
                ) a ON (u.uid=a.uid)";

                $whereClauses[] = "(a.totalUserGrants<'1' || a.totalUserGrants IS NULL)";
            }*/

            $taskRevokeAwardID = 0;
        } elseif ($taskType === TASK_TYPE_REVOKE && $taskRevokeAwardID) {
            /*$tableQueryFields[] = 'a.totalUserGrants';

            // if user has no awards from this task, skip
            $tableLeftJoins[] = "(
                    SELECT uid, COUNT(aid) AS totalUserGrants
                    FROM {$db->table_prefix}ougc_awards_users
                    WHERE aid='{$taskRevokeAwardID}'
                    GROUP BY uid, aid
                ) a ON (u.uid=a.uid)";*/

            $taskGrantAwardID = 0;
        } else {
            continue;
        }

        // if log exists for user, skip
        /*
        $tableQueryFields[] = 'l.totalUserLogs';

        $tableLeftJoins[] = "(
					SELECT uid, COUNT(lid) AS totalUserLogs
					FROM {$db->table_prefix}ougc_awards_tasks_logs
					WHERE tid='{$taskID}'
					GROUP BY uid, lid
				) l ON (u.uid=l.uid)";

        $whereClauses[] = "l.totalUserLogs<'1' OR l.totalUserLogs IS NULL";
        */

        $hookArguments['tableLeftJoins'] = &$tableLeftJoins;

        $hookArguments = runHooks('task_start', $hookArguments);

        $taskThreadID = (int)$awardTaskData['thread'];

        $userWhereClauses = [];

        $taskRequirements = explode(',', $awardTaskData['requirements']);

        $executedRequirements = [];

        $hookArguments['executedRequirements'] = &$executedRequirements;

        foreach ($requirementCriteria as $requirementType => $callback) {
            if (in_array($requirementType, $taskRequirements)) {
                $callbackResult = $callback(
                    $awardTaskData,
                    $requirementType,
                    $whereClauses,
                    $tableLeftJoins,
                    $userWhereClauses
                );

                if ($callbackResult) {
                    $executedRequirements[$requirementType] = $requirementType;
                }
            }
        }

        // if not all requirements were ran then skip this task, as it may be misconfigured or if a third party plugin then it may be disabled
        if (count($taskRequirements) !== count($executedRequirements)) {
            continue;
        }

        $whereClauses = array_merge($whereClauses, $userWhereClauses);

        // no task should lack where clauses, because that would make the task to apply to all users
        if (empty($whereClauses)) {
            continue;
        }

        $exemptUsersIDs = [];

        $hookArguments['exemptUsersIDs'] = &$exemptUsersIDs;

        $queryLogs = $db->simple_select('ougc_awards_tasks_logs', 'uid', "tid='{$taskID}'");

        while ($logData = $db->fetch_array($queryLogs)) {
            $exemptUsersIDs[] = (int)$logData['uid'];
        }

        $taskLogObjects = [];

        $hookArguments = runHooks('task_intermediate', $hookArguments);

        if ($exemptUsersIDs) {
            $exemptUsersIDs = implode("','", array_filter($exemptUsersIDs));

            $whereClauses[] = "u.uid NOT IN ('{$exemptUsersIDs}')";
        }

        $queryUsers = $db->simple_select(
            implode(' LEFT JOIN ', $tableLeftJoins),
            implode(',', $tableQueryFields),
            implode(' AND ', $whereClauses)
        );

        while ($userData = $db->fetch_array($queryUsers)) {
            $userID = (int)$userData['uid'];

            $logTaskGrant = false;

            $userGrantedAwardIDs = $userRevokeAwardIDs = $grandIDs = $revokeAwardIDs = [];

            if ($taskType === TASK_TYPE_GRANT) {
                global $awardsCustomThreadPerUserObjects;

                isset($awardsCustomThreadPerUserObjects) || $awardsCustomThreadPerUserObjects = [];

                if (!empty($awardsCustomThreadPerUserObjects[$taskID][$userID])) {
                    $taskThreadID = (int)$awardsCustomThreadPerUserObjects[$taskID][$userID];
                }

                if (grantInsert(
                    $taskGrantAwardID,
                    $userID,
                    (string)$awardTaskData['reason'],
                    $taskThreadID,
                    $taskID
                )) {
                    $grandIDs[] = $taskGrantAwardID;

                    $logTaskGrant = true;
                }
            } else {
                $queryGrants = $db->simple_select(
                    'ougc_awards_users',
                    'gid',
                    "uid='{$userID}' AND aid='{$taskRevokeAwardID}'"
                );

                while ($grandData = $db->fetch_array($queryGrants)) {
                    grantDelete((int)$grandData['gid']);

                    $logTaskGrant = true;
                }
            }

            if ($logTaskGrant) {
                $taskLogObjects[] = [
                    'tid' => $taskID,
                    'uid' => $userID,
                    'gave' => $taskGrantAwardID,
                    'revoked' => $taskRevokeAwardID,
                    'date' => TIME_NOW
                ];
            }
        }

        $hookArguments = runHooks('task_end', $hookArguments);

        if (count($taskLogObjects) > 0) {
            $db->insert_query_multiple('ougc_awards_tasks_logs', $taskLogObjects);
        }
    }

    cacheUpdate();

    return true;
}

function allowImports(): bool
{
    return getSetting('allowImports') && pluginIsInstalled();
}

function getUser(int $userID, array $queryFields = []): array
{
    global $db;

    $queryFields[] = 'uid';

    $dbQuery = $db->simple_select('users', implode(',', $queryFields), "uid='{$userID}'");

    if ($db->num_rows($dbQuery)) {
        return (array)$db->fetch_array($dbQuery);
    }

    return [];
}

function getUserByUserName(string $userName): array
{
    global $db;

    $dbQuery = $db->simple_select(
        'users',
        'uid, username',
        "LOWER(username)='{$db->escape_string(my_strtolower($userName))}'",
        ['limit' => 1]
    );

    if ($db->num_rows($dbQuery)) {
        return (array)$db->fetch_array($dbQuery);
    }

    return [];
}

function presetInsert(array $insertData, int $presetID = 0, bool $updatePreset = false): int
{
    global $db;

    if ($updatePreset) {
        return (int)$db->update_query('ougc_awards_presets', $insertData, "pid='{$presetID}'");
    }

    return (int)$db->insert_query('ougc_awards_presets', $insertData);
}

function presetUpdate(array $updateData, int $presetID): int
{
    return presetInsert($updateData, $presetID, true);
}

function presetGet(
    array $whereClauses = [],
    array $queryFields = ['uid', 'name', 'hidden', 'visible'],
    array $queryOptions = []
): array {
    $queryFields[] = 'pid';

    global $db;

    $cacheObjects = [];

    $dbQuery = $db->simple_select(
        'ougc_awards_presets',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            $cacheObjects = $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                $cacheObjects[] = $userData;
            }
        }
    }

    return $cacheObjects;
}

function presetDelete(int $presetID): bool
{
    global $db;

    $db->delete_query('ougc_awards_presets', "pid='{$presetID}'");

    return true;
}

function ownerInsert(int $awardID, int $userID): bool
{
    global $db;

    $hookArguments = [
        'awardID' => &$awardID,
        'userID' => &$userID
    ];

    $hookArguments = runHooks('assign_award_owner', $hookArguments);

    $insertData = [
        'aid' => $awardID,
        'uid' => $userID,
        'date' => TIME_NOW
    ];

    $db->insert_query('ougc_awards_owners', $insertData);

    $db->update_query('users', ['ougc_awards_owner' => 1], "uid='{$userID}'");

    return true;
}

function ownerDelete(int $ownerID): bool
{
    global $db;

    $hookArguments = [
        'ownerID' => &$ownerID
    ];

    $hookArguments = runHooks('revoke_award_owner', $hookArguments);

    $db->delete_query('ougc_awards_owners', "oid='{$ownerID}'");

    rebuildOwners();

    return true;
}

function rebuildOwners(): bool
{
    global $db;

    $userIDs = [];

    $dbQuery = $db->simple_select('ougc_awards_owners', 'uid');

    while ($userID = $db->fetch_field($dbQuery, 'uid')) {
        $userIDs[] = (int)$userID;
    }

    $userIDs = implode("','", array_filter($userIDs));

    $db->update_query('users', ['ougc_awards_owner' => 0], "uid NOT IN ('{$userIDs}')");

    $db->update_query('users', ['ougc_awards_owner' => 1], "uid IN ('{$userIDs}')");

    return true;
}

function ownerGetSingle(
    array $whereClauses = [],
    array $queryFields = ['uid', 'aid', 'date'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'oid';

    $dbQuery = $db->simple_select(
        'ougc_awards_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        return $db->fetch_array($dbQuery);
    }

    return [];
}

function ownerGetUser(
    array $whereClauses = [],
    array $queryFields = ['uid', 'aid', 'date'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'oid';

    $usersData = [];

    if (isset($queryOptions['limit'])) {
        $queryOptions['limit'] = (int)$queryOptions['limit'];
    }

    $dbQuery = $db->simple_select(
        'ougc_awards_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            return $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                $usersData[] = $userData;
            }
        }
    }

    return $usersData;
}

function ownerFind(int $awardID, int $userID, array $queryFields = ['uid', 'aid', 'date']): array
{
    global $db;

    $queryFields[] = 'oid';

    $query = $db->simple_select(
        'ougc_awards_owners',
        implode(',', $queryFields),
        "aid='{$awardID}' AND uid='{$userID}'"
    );

    if ($db->num_rows($query)) {
        return $db->fetch_array($query);
    }

    return [];
}

function ownerCount(
    array $whereClauses = [],
    array $queryOptions = ['limit' => 1],
    array $queryFields = []
): array {
    global $db;

    $queryFields[] = 'COUNT(oid) AS total_owners';

    $dbQuery = $db->simple_select(
        'ougc_awards_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $ownerObjects = [];

    while ($ownerData = $db->fetch_array($dbQuery)) {
        if (isset($queryOptions['group_by']) && isset($ownerData[$queryOptions['group_by']])) {
            $ownerObjects[(int)$ownerData[$queryOptions['group_by']]] = $ownerData;
        } else {
            $ownerObjects[] = $ownerData;
        }
    }

    return $ownerObjects;
}

function ownerCategoryInsert(int $categoryID, int $userID): bool
{
    global $db;

    $hookArguments = [
        'categoryID' => &$categoryID,
        'userID' => &$userID
    ];

    $hookArguments = runHooks('assign_category_owner', $hookArguments);

    $insertData = [
        'categoryID' => $categoryID,
        'userID' => $userID,
        'ownerDate' => TIME_NOW
    ];

    $db->insert_query('ougc_awards_category_owners', $insertData);

    $db->update_query('users', ['ougc_awards_category_owner' => 1], "uid='{$userID}'");

    return true;
}

function ownerCategoryDelete(int $ownerID): bool
{
    global $db;

    $hookArguments = [
        'ownerID' => &$ownerID
    ];

    $hookArguments = runHooks('revoke_category_owner', $hookArguments);

    $db->delete_query('ougc_awards_category_owners', "ownerID='{$ownerID}'");

    rebuildOwnersCategories();

    return true;
}

function rebuildOwnersCategories(): bool
{
    global $db;

    $userIDs = [0];

    foreach (ownerCategoryGetUser([], ['userID']) as $ownerID => $ownerData) {
        $userIDs[] = (int)$ownerData['userID'];
    }

    $userIDs = implode("','", $userIDs);

    $db->update_query('users', ['ougc_awards_category_owner' => 0], "uid NOT IN ('{$userIDs}')");

    $db->update_query('users', ['ougc_awards_category_owner' => 1], "uid IN ('{$userIDs}')");

    return true;
}

function ownerCategoryGetSingle(
    array $whereClauses = [],
    array $queryFields = ['userID', 'categoryID', 'ownerDate'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'ownerID';

    $dbQuery = $db->simple_select(
        'ougc_awards_category_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        return $db->fetch_array($dbQuery);
    }

    return [];
}

function ownerCategoryGetUser(
    array $whereClauses = [],
    array $queryFields = ['userID', 'categoryID', 'ownerDate'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'ownerID';

    $usersData = [];

    if (isset($queryOptions['limit'])) {
        $queryOptions['limit'] = (int)$queryOptions['limit'];
    }

    $dbQuery = $db->simple_select(
        'ougc_awards_category_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            return $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                $usersData[(int)$userData['ownerID']] = $userData;
            }
        }
    }

    return $usersData;
}

function ownerCategoryFind(
    int $categoryID,
    int $userID,
    array $queryFields = ['userID', 'categoryID', 'ownerDate']
): array {
    global $db;

    $queryFields[] = 'ownerID';

    $query = $db->simple_select(
        'ougc_awards_category_owners',
        implode(',', $queryFields),
        "categoryID='{$categoryID}' AND userID='{$userID}'"
    );

    if ($db->num_rows($query)) {
        return $db->fetch_array($query);
    }

    return [];
}

function ownerCategoryCount(
    array $whereClauses = [],
    array $queryOptions = ['limit' => 1],
    array $queryFields = []
): array {
    global $db;

    $queryFields[] = 'COUNT(ownerID) AS total_category_owners';

    $dbQuery = $db->simple_select(
        'ougc_awards_category_owners',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $ownerObjects = [];

    while ($ownerData = $db->fetch_array($dbQuery)) {
        if (isset($queryOptions['group_by']) && isset($ownerData[$queryOptions['group_by']])) {
            $ownerObjects[(int)$ownerData[$queryOptions['group_by']]] = $ownerData;
        } else {
            $ownerObjects[] = $ownerData;
        }
    }

    return $ownerObjects;
}

function ownerGetUserAwards(?int $userID = null): array
{
    if ($userID === null) {
        global $mybb;

        $userID = (int)$mybb->user['uid'];

        $userData = $mybb->user;
    } else {
        $userData = get_user($userID);
    }

    $awardsCache = awardsCacheGet()['awards'];

    $ownsCategories = !empty($userData['ougc_awards_category_owner']);

    $ownsAwards = $ownsCategories || !empty($userData['ougc_awards_owner']);

    $awardIDs = [];

    if ($ownsCategories) {
        foreach (
            ownerCategoryGetUser(
                ["userID='{$userID}'"],
                ['categoryID']
            ) as $ownerID => $ownerData
        ) {
            foreach ($awardsCache as $awardID => $awardData) {
                if ((int)$awardData['cid'] === (int)$ownerData['categoryID']) {
                    $awardIDs[$awardID] = (int)$awardData['cid'];
                }
            }
        }
    } elseif ($ownsAwards) {
        foreach (
            ownerGetUser(
                ["uid='{$userID}'"],
                ['aid']
            ) as $ownerID => $ownerData
        ) {
            foreach ($awardsCache as $awardID => $awardData) {
                if ((int)$awardData['aid'] === (int)$ownerData['aid']) {
                    $awardIDs[$awardID] = (int)$awardData['cid'];
                }
            }
        }
    }

    return $awardIDs;
}

function categoryInsert(array $categoryData, int $categoryID = 0, bool $isUpdate = false): int
{
    global $db;

    $insertData = [];

    $hookArguments = [
        'categoryData' => &$categoryData,
        'insertData' => &$insertData,
        'categoryID' => $categoryID,
        'isUpdate' => $isUpdate,
    ];

    if (isset($categoryData['name'])) {
        $insertData['name'] = $db->escape_string($categoryData['name']);
    }

    if (isset($categoryData['description'])) {
        $insertData['description'] = $db->escape_string($categoryData['description']);
    }

    if (isset($categoryData['disporder'])) {
        $insertData['disporder'] = (int)$categoryData['disporder'];
    }

    if (isset($categoryData['allowrequests'])) {
        $insertData['allowrequests'] = (int)$categoryData['allowrequests'];
    }

    if (isset($categoryData['visible'])) {
        $insertData['visible'] = (int)$categoryData['visible'];
    }

    if (isset($categoryData['outputInCustomSection'])) {
        $insertData['outputInCustomSection'] = (int)$categoryData['outputInCustomSection'];
    }

    if (isset($categoryData['hideInMainPage'])) {
        $insertData['hideInMainPage'] = (int)$categoryData['hideInMainPage'];
    }

    $hookArguments = runHooks('insert_update_category_end', $hookArguments);

    if ($isUpdate) {
        $db->update_query('ougc_awards_categories', $insertData, "cid='{$categoryID}'");

        return $categoryID;
    }

    return (int)$db->insert_query('ougc_awards_categories', $insertData);
}

function categoryUpdate(array $updateData, int $categoryID): int
{
    return categoryInsert($updateData, $categoryID, true);
}

function categoryDelete(int $categoryID): bool
{
    global $db;

    $dbQuery = $db->simple_select('ougc_awards', 'aid', "cid='{$categoryID}'");

    while ($awardID = (int)$db->fetch_field($dbQuery, 'aid')) {
        awardDelete($awardID);
    }

    $db->delete_query('ougc_awards_categories', "cid='{$categoryID}'");

    return true;
}

function categoryGet(
    int $categoryID,
    array $queryFields = [
        'name',
        'description',
        'disporder',
        'allowrequests',
        'visible',
        'outputInCustomSection',
        'hideInMainPage'
    ]
): array {
    static $categoryCache = [];

    $queryFields[] = 'cid';

    if (!isset($categoryCache[$categoryID])) {
        global $db;

        $categoryCache[$categoryID] = [];

        $dbQuery = $db->simple_select('ougc_awards_categories', implode(',', $queryFields), "cid='{$categoryID}'");

        if ($db->num_rows($dbQuery)) {
            $categoryCache[$categoryID] = $db->fetch_array($dbQuery);
        }
    }

    return $categoryCache[$categoryID];
}

function categoryGetCache(
    array $whereClauses = [],
    array $queryFields = [
        'name',
        'description',
        'disporder',
        'allowrequests',
        'visible',
        'outputInCustomSection',
        'hideInMainPage'
    ],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'cid';

    $cacheObjects = [];

    if (isset($queryOptions['limit'])) {
        $queryOptions['limit'] = (int)$queryOptions['limit'];
    }

    $dbQuery = $db->simple_select(
        'ougc_awards_categories',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            $cacheObjects = $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                if (isset($userData['cid'])) {
                    $cacheObjects[(int)$userData['cid']] = $userData;
                } else {
                    $cacheObjects[] = $userData;
                }
            }
        }
    }

    return $cacheObjects;
}

function awardInsert(array $awardData, int $awardID = 0, bool $isUpdate = false): int
{
    global $db;

    $insertData = [];

    $hookArguments = [
        'awardData' => &$awardData,
        'insertData' => &$insertData,
        'awardID' => $awardID,
        'isUpdate' => $isUpdate,
    ];

    if (isset($awardData['cid'])) {
        $insertData['cid'] = (int)$awardData['cid'];
    }

    if (isset($awardData['name'])) {
        $insertData['name'] = $db->escape_string($awardData['name']);
    }

    if (isset($awardData['description'])) {
        $insertData['description'] = $db->escape_string($awardData['description']);
    }

    if (isset($awardData['award_file'])) {
        $insertData['award_file'] = $db->escape_string($awardData['award_file']);
    }

    if (isset($awardData['image'])) {
        $insertData['image'] = $db->escape_string($awardData['image']);
    }

    if (isset($awardData['template'])) {
        $insertData['template'] = (int)$awardData['template'];
    }

    if (isset($awardData['disporder'])) {
        $insertData['disporder'] = (int)$awardData['disporder'];
    }

    if (isset($awardData['allowrequests'])) {
        $insertData['allowrequests'] = (int)$awardData['allowrequests'];
    }

    if (isset($awardData['visible'])) {
        $insertData['visible'] = (int)$awardData['visible'];
    }

    if (isset($awardData['pm'])) {
        $insertData['pm'] = $db->escape_string($awardData['pm']);
    }

    if (isset($awardData['type'])) {
        $insertData['type'] = (int)$awardData['type'];
    }

    $hookArguments = runHooks('insert_update_award_end', $hookArguments);

    if ($isUpdate) {
        $db->update_query('ougc_awards', $awardData, "aid='{$awardID}'");

        return $awardID;
    }

    return (int)$db->insert_query('ougc_awards', $awardData);
}

function awardUpdate(array $updateData, int $awardID = 0): int
{
    return awardInsert($updateData, $awardID, true);
}

function awardDelete(int $awardID): bool
{
    require_once MYBB_ROOT . 'inc/functions_upload.php';

    global $db;

    $dbQuery = $db->simple_select('ougc_awards_users', 'gid', "aid='{$awardID}'");

    while ($grantID = (int)$db->fetch_field($dbQuery, 'gid')) {
        grantDelete($grantID);
    }

    $dbQuery = $db->simple_select('ougc_awards_owners', 'oid', "aid='{$awardID}'");

    while ($ownerID = (int)$db->fetch_field($dbQuery, 'oid')) {
        ownerDelete($ownerID);
    }

    $db->delete_query('ougc_awards', "aid='{$awardID}'");

    $dir = opendir(getSetting('uploadPath'));

    if ($dir) {
        while ($file = readdir($dir)) {
            if (preg_match('#award_' . $awardID . '\.#', $file) && is_file(
                    getSetting('uploadPath') . '/' . $file
                )) {
                delete_uploaded_file(getSetting('uploadPath') . '/' . $file);
            }
        }

        closedir($dir);
    }

    return true;
}

function awardGet(
    int $awardID,
    array $queryFields = [
        'cid',
        'name',
        'description',
        'award_file',
        'image',
        'template',
        'disporder',
        'allowrequests',
        'visible',
        'pm',
        'type'
    ]
): array {
    global $db;

    $queryFields[] = 'aid';

    $awardData = [];

    $dbQuery = $db->simple_select('ougc_awards', implode(',', $queryFields), "aid='{$awardID}'");

    if ($db->num_rows($dbQuery)) {
        $awardData = $db->fetch_array($dbQuery);
    }

    return $awardData;
}

function awardGetIcon(int $awardID): string
{
    global $mybb;

    $awardData = awardGet($awardID);

    $replaceObjects = [
        '{bburl}' => $mybb->settings['bburl'],
        '{forum_url}' => $mybb->settings['bburl'],
        '{homeurl}' => $mybb->settings['homeurl'],
        '{home_url}' => $mybb->settings['homeurl'],
        '{imgdir}' => $theme['imgdir'] ?? '',
        '{images_url}' => $theme['imgdir'] ?? '',
        '{aid}' => $awardID,
        '{award_id}' => $awardID,
        '{cid}' => (int)$awardData['cid'],
        '{category_id}' => (int)$awardData['cid']
    ];

    $awardImage = str_replace(array_keys($replaceObjects), array_values($replaceObjects), $awardData['image']);

    if ((int)$awardData['template'] === AWARD_TEMPLATE_TYPE_IMAGE && !my_validate_url($awardImage)) {
        $awardImage = $mybb->get_asset_url(getSetting('uploadPath') . $awardData['award_file']);
    }

    return $awardImage;
}

function awardGetUser(
    array $whereClauses = [],
    array $queryFields = ['uid', 'oid', 'aid', 'rid', 'tid', 'thread', 'reason', 'pm', 'date', 'disporder', 'visible'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'gid';

    /*if (isset($queryOptions['group_by'])) {
        $queryOptions['group_by'] .= ', gid';
    }*/

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $grantObjects = [];

    while ($userData = $db->fetch_array($dbQuery)) {
        $grantObjects[] = $userData;
    }

    return $grantObjects;
}

function awardsGetCache(
    array $whereClauses = [],
    array $queryFields = [
        'cid',
        'name',
        'description',
        'award_file',
        'image',
        'template',
        'disporder',
        'allowrequests',
        'visible',
        'pm',
        'type'
    ],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'aid';

    $cacheObjects = [];

    $dbQuery = $db->simple_select(
        'ougc_awards',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        while ($rowData = $db->fetch_array($dbQuery)) {
            $cacheObjects[(int)$rowData['aid']] = $rowData;
        }
    }

    return $cacheObjects;
}

function grantInsert(
    int $awardID,
    int $userID,
    string $reasonText,
    int $threadID = 0,
    int $taskID = 0,
    int $requestID = 0
): int {
    global $db, $mybb;

    $awardData = awardGet($awardID);

    $userData = getUser($userID, ['username']);

    $hookArguments = [
        'awardData' => &$awardData,
        'userData' => &$userData,
        'reasonText' => &$reasonText
    ];

    $hookArguments = runHooks('grant_award', $hookArguments);

    $insertData = [
        'aid' => $awardID,
        'uid' => $userID,
        'oid' => $taskID ? 0 : (int)$mybb->user['uid'],
        'tid' => $taskID,
        'thread' => $threadID,
        'rid' => $requestID,
        'reason' => $db->escape_string($reasonText),
        'date' => TIME_NOW,
        'disporder' => (int)$awardData['disporder'],
        'visible' => (int)getSetting('grantDefaultVisibleStatus')
    ];

    $grantID = $db->insert_query('ougc_awards_users', $insertData);

    global $lang;

    loadLanguage();

    sendPrivateMessage([
        'subject' => $lang->sprintf(
            $lang->ougcAwardsPrivateMessageTitle,
            $awardData['name']
        ),
        'message' => $lang->sprintf(
            str_replace(
                [
                    '{user_name}',
                    '{award_name}',
                    '{grant_reason}',
                    '{award_icon}',
                    '{forum_name}'
                ],
                [
                    $userData['username'],
                    $awardData['name'],
                    (empty($reasonText) ? $lang->ougcAwardsNoReason : $reasonText),
                    awardGetIcon($awardID),
                    $mybb->settings['bbname']
                ],
                $awardData['pm']
            ),
            $userData['username'],
            $awardData['name'],
            (empty($reasonText) ? $lang->ougcAwardsNoReason : $reasonText),
            awardGetIcon($awardID),
            $mybb->settings['bbname']
        ),
        'touid' => $userID
    ], (int)getSetting('privateMessageSenderUserID'), true);

    sendAlert($awardID, $userID);

    return $grantID;
}

function grantUpdate(array $updateData, int $grantID): bool
{
    global $db;

    $hookArguments = [
        'grantID' => &$grantID,
        'updateData' => &$updateData,
    ];

    $hookArguments = runHooks('update_gived', $hookArguments);

    $db->update_query('ougc_awards_users', $updateData, "gid='{$grantID}'");

    return true;
}

function grantGet(array $whereClauses, array $queryFields = [], array $queryOptions = []): array
{
    global $db;

    $queryFields[] = 'gid';

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $grantObjects = [];

    while ($grantData = $db->fetch_array($dbQuery)) {
        $grantObjects[(int)$grantData['gid']] = $grantData;
    }

    return $grantObjects;
}

function grantDelete(int $grantID): bool
{
    global $db;

    $hookArguments = [
        'grantID' => &$grantID
    ];

    $hookArguments = runHooks('revoke_award', $hookArguments);

    $db->delete_query('ougc_awards_users', "gid='{$grantID}'");

    return true;
}

function grantGetSingle(
    array $whereClauses = [],
    array $queryFields = ['uid', 'oid', 'aid', 'rid', 'tid', 'thread', 'reason', 'pm', 'date', 'disporder', 'visible'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'gid';

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        return $db->fetch_array($dbQuery);
    }

    return [];
}

function grantFind(
    int $awardID,
    int $userID,
    array $queryFields = ['uid', 'oid', 'aid', 'rid', 'tid', 'thread', 'reason', 'pm', 'date', 'disporder', 'visible']
): array {
    global $db;

    $queryFields[] = 'gid';

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        implode(',', $queryFields),
        "aid='{$awardID}' AND uid='{$userID}'"
    );

    if ($db->num_rows($dbQuery)) {
        return $db->fetch_array($dbQuery);
    }

    return [];
}

function grantCount(
    array $whereClauses = [],
    array $queryOptions = ['limit' => 1],
    array $queryFields = []
): array {
    global $db;

    $queryFields[] = 'COUNT(gid) AS total_grants';

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $grantObjects = [];

    while ($grantData = $db->fetch_array($dbQuery)) {
        if (isset($queryOptions['group_by']) && isset($grantData[$queryOptions['group_by']])) {
            $grantObjects[(int)$grantData[$queryOptions['group_by']]] = $grantData;
        } else {
            $grantObjects[] = $grantData;
        }
    }

    return $grantObjects;
}

function requestInsert(array $requestData, int $requestID = 0, bool $updateRequest = false): int
{
    global $db;

    if ($updateRequest) {
        return (int)$db->update_query('ougc_awards_requests', $requestData, "rid='{$requestID}'");
    }

    return (int)$db->insert_query('ougc_awards_requests', $requestData);
}

function requestUpdate(array $updateData, int $requestID): int
{
    return requestInsert($updateData, $requestID, true);
}

function requestGet(
    array $whereClauses = [],
    array $queryFields = ['aid', 'uid', 'muid', 'message', 'status'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'rid';

    $requestData = [];

    $dbQuery = $db->simple_select(
        'ougc_awards_requests',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        return $db->fetch_array($dbQuery);
    }

    return $requestData;
}

function requestsCount(
    array $whereClauses = [],
    array $queryOptions = ['limit' => 1],
    array $queryFields = []
): array {
    global $db;

    $queryFields[] = 'COUNT(rid) AS total_requests';

    $dbQuery = $db->simple_select(
        'ougc_awards_requests',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $grantObjects = [];

    while ($requestData = $db->fetch_array($dbQuery)) {
        if (isset($queryOptions['group_by']) && isset($requestData[$queryOptions['group_by']])) {
            $grantObjects[(int)$requestData[$queryOptions['group_by']]] = $requestData;
        } else {
            $grantObjects[] = $requestData;
        }
    }

    return $grantObjects;
}

function requestGetPending(
    array $whereClauses = [],
    array $queryFields = ['aid', 'uid', 'muid', 'message', 'status'],
    array $queryOptions = []
): array {
    global $db;

    $queryFields[] = 'rid';

    $requestData = [];

    if (isset($queryOptions['limit'])) {
        $queryOptions['limit'] = (int)$queryOptions['limit'];
    }

    $dbQuery = $db->simple_select(
        'ougc_awards_requests',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            $requestData = $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                $requestData[] = $userData;
            }
        }
    }

    return $requestData;
}

function requestGetPendingTotal(array $whereClauses = []): int
{
    $pendingRequestTotal = requestGetPending(
        $whereClauses,
        ['COUNT(rid) as pendingRequestTotal'],
        ['group_by' => 'rid']
    );

    if (!empty($pendingRequestTotal['pendingRequestTotal'])) {
        return (int)$pendingRequestTotal['pendingRequestTotal'];
    }

    return 0;
}

function requestReject(int $requestID): bool
{
    global $lang, $mybb;

    loadLanguage();

    $requestData = requestGet(["rid='{$requestID}'"]);

    $awardID = (int)($requestData['aid'] ?? 0);

    $userID = (int)($requestData['uid'] ?? 0);

    $awardData = awardGet($awardID);

    $userData = getUser($userID, ['username']);

    sendPrivateMessage([
        'subject' => $lang->sprintf(
            $lang->ougcAwardsPrivateMessageRequestRejectedTitle,
            $awardData['name']
        ),
        'message' => $lang->sprintf(
            $lang->ougcAwardsPrivateMessageRequestRejectedBody,
            $userData['username'],
            $awardData['name']
        ),
        'touid' => $userData['uid']
    ], (int)getSetting('privateMessageSenderUserID'), true);

    sendAlert($awardID, $userID, 'reject_request');

    requestUpdate(['status' => REQUEST_STATUS_REJECTED, 'muid' => $mybb->user['uid']], $requestID);

    return true;
}

function requestApprove(int $requestID): bool
{
    global $mybb;

    $requestData = requestGet(["rid='{$requestID}'"]);

    grantInsert(
        (int)$requestData['aid'],
        (int)$requestData['uid'],
        '',
        0,
        0,
        $requestID
    );

    requestUpdate([
        'status' => REQUEST_STATUS_ACCEPTED,
        'muid' => $mybb->user['uid']
    ], $requestID);

    return true;
}

function taskInsert(array $taskData, int $taskID = 0, bool $isUpdate = false): int
{
    global $db;

    $fieldsData = FIELDS_DATA;

    $hookArguments = [
        'taskData' => &$taskData,
        'taskID' => &$taskID,
        'isUpdate' => &$isUpdate,
        'fieldsData' => &$fieldsData,
    ];

    $inputDataFields = [
        'stringFields' => [
            'name',
            'description',
            'reason',
            'threadstype',
            'poststype',
            'fthreadstype',
            'fpoststype',
            'registeredtype',
            'onlinetype',
            'reputationtype',
            'referralstype',
            'warningstype',
            //'newpointstype',
            //'mydownloadstype',
            //'myarcadechampionstype',
            //'myarcadescorestype',
            //'ougc_customreptype_r',
            //'ougc_customrepids_r',
            //'ougc_customreptype_g',
            //'ougc_customrepids_g',
            'ruleScripts',
        ],
        'floatFields' => [
            //'newpoints',
        ],
        'integerFields' => [
            'tid',
            'active',
            'taskType',
            'logging',
            'give',
            'revokeAwardID',
            'thread',
            //'allowmultiple',
            'disporder',
            'additionalgroups',
            'threads',
            'posts',
            'fthreads',
            'fposts',
            'registered',
            'online',
            'reputation',
            'referrals',
            'warnings',
            //'mydownloads',
            //'myarcadechampions',
            //'myarcadescores',
            //'ougc_customrep_r',
            //'ougc_customrep_g',
        ],
        'arrayFields' => [
            'usergroups',
            'fthreadsforums',
            'fpostsforums',
            TASK_REQUIREMENT_TYPE_AWARDS_GRANTED,
            'profilefields'
        ],
        'comparisonFields' => [
            'poststype',
            'threadstype',
            'fpoststype',
            'fthreadstype',
            'reputationtype',
            'referralstype',
            'warningstype',
            //'newpointstype',
            //'mydownloadstype',
            //'myarcadechampionstype',
            //'myarcadescorestype',
            //'ougc_customreptype_r',
            //'ougc_customreptype_g'
        ],
        'timeFields' => [
            'registeredtype',
            'onlinetype',
        ]
    ];

    $hookArguments['inputDataFields'] = &$inputDataFields;

    $insertData = [];

    $hookArguments['insertData'] = &$insertData;

    $hookArguments = runHooks('task_insert_start', $hookArguments);

    foreach ($inputDataFields['stringFields'] as $k) {
        if (isset($taskData[$k])) {
            $insertData[$k] = $db->escape_string($taskData[$k]);
        }
    }

    foreach ($inputDataFields['floatFields'] as $k) {
        if (isset($taskData[$k])) {
            $insertData[$k] = (float)$taskData[$k];
        }
    }

    foreach ($inputDataFields['integerFields'] as $k) {
        if (isset($taskData[$k])) {
            $insertData[$k] = (int)$taskData[$k];
        }
    }

    foreach ($inputDataFields['arrayFields'] as $k) {
        if (isset($taskData[$k]) && is_array($taskData[$k])) {
            $insertData[$k] = $db->escape_string(
                implode(',', array_filter(array_unique(array_map('intval', $taskData[$k]))))
            );
        }
    }

    foreach ($inputDataFields['comparisonFields'] as $k) {
        if (isset($taskData[$k]) && in_array($taskData[$k], array_keys(getComparisonTypes()))) {
            $insertData[$k] = $db->escape_string($taskData[$k]);
        }
    }

    foreach ($inputDataFields['timeFields'] as $k) {
        if (isset($taskData[$k]) && in_array($taskData[$k], array_keys(getTimeTypes()))) {
            $insertData[$k] = $db->escape_string($taskData[$k]);
        }
    }

    !isset($taskData['requirements']) || $insertData['requirements'] = $db->escape_string(
        implode(',', array_filter(array_unique((array)$taskData['requirements'])))
    );

    /*
    if ($db->table_exists('ougc_awards_tasks')) {
        if ($db->field_exists('ougc_customrepids_g', 'ougc_awards_tasks')) {
            $db->drop_column('ougc_awards_tasks', 'ougc_customrepids_g');
        }
    }*/

    $hookArguments = runHooks('task_insert_end', $hookArguments);

    try {
        if ($isUpdate) {
            return (int)$db->update_query('ougc_awards_tasks', $insertData, "tid='{$taskID}'");
        } else {
            $taskID = (int)$db->insert_query('ougc_awards_tasks', $insertData);
        }
    } catch (Exception $e) {
        error($e->getMessage());
    }

    return $taskID;
}

function taskUpdate(array $taskData, int $taskID): int
{
    return taskInsert($taskData, $taskID, true);
}

function taskDelete(int $taskID): bool
{
    global $db;

    $db->delete_query('ougc_awards_tasks', "tid='{$taskID}'");

    logDelete(["tid='{$taskID}'"]);

    return true;
}

function taskGet(array $whereClauses = [], array $queryFields = [], array $queryOptions = []): array
{
    global $db;

    $cacheObjects = [];

    $queryFields[] = 'tid';

    $dbQuery = $db->simple_select(
        'ougc_awards_tasks',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            $cacheObjects = $db->fetch_array($dbQuery);
        } else {
            while ($userData = $db->fetch_array($dbQuery)) {
                $cacheObjects[(int)$userData['tid']] = $userData;
            }
        }
    }

    return $cacheObjects;
}

function logGet(array $whereClauses = [], array $queryFields = [], array $queryOptions = []): array
{
    global $db;

    $cacheObjects = [];

    $queryFields[] = 'lid';

    $dbQuery = $db->simple_select(
        'ougc_awards_tasks_logs',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
            $cacheObjects = $db->fetch_array($dbQuery);
        } else {
            while ($logData = $db->fetch_array($dbQuery)) {
                $cacheObjects[] = $logData;
            }
        }
    }

    return $cacheObjects;
}

function logDelete(array $whereClauses): bool
{
    global $db;

    $db->delete_query('ougc_awards_tasks_logs', implode(' AND ', $whereClauses));

    return true;
}

function logCount(
    array $whereClauses = [],
    array $queryOptions = ['limit' => 1],
    array $queryFields = []
): array {
    global $db;

    $queryFields[] = 'COUNT(lid) AS total_logs';

    $dbQuery = $db->simple_select(
        'ougc_awards_tasks_logs',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    if (isset($queryOptions['limit']) && $queryOptions['limit'] === 1) {
        return (array)$db->fetch_array($dbQuery);
    }

    $logObjects = [];

    while ($logData = $db->fetch_array($dbQuery)) {
        if (isset($queryOptions['group_by']) && isset($logData[$queryOptions['group_by']])) {
            $logObjects[(int)$logData[$queryOptions['group_by']]] = $logData;
        } else {
            $logObjects[] = $logData;
        }
    }

    return $logObjects;
}

function sendPrivateMessage(array $privateMessage, int $fromUserID = 0, bool $adminOverride = false): bool
{
    if (getSetting('notificationPrivateMessage')) {
        send_pm($privateMessage, $fromUserID, $adminOverride);
    }

    return true;
}

function sendAlert(int $awardID, int $userID, string $alertTypeKey = 'give_award'): bool
{
    global $lang, $mybb, $alertType, $db;

    loadLanguage();

    if (!class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        return false;
    }

    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ougc_awards');

    if (!$alertType) {
        return false;
    }

    $query = $db->simple_select(
        'alerts',
        'id',
        "object_id='{$awardID}' AND uid='{$userID}' AND unread=1 AND alert_type_id='{$alertType->getId()}'"
    );

    if ($db->fetch_field($query, 'id')) {
        return false;
    }

    if ($alertType !== null && $alertType->getEnabled()) {
        $alert = new MybbStuff_MyAlerts_Entity_Alert($userID, $alertType, $awardID);

        $alert->setExtraDetails([
            'type' => $alertTypeKey
        ]);

        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
    }

    return true;
}

function logAction(): bool
{
    $data = ['fid' => '', 'tid' => ''];

    if (defined('IN_ADMINCP')) {
        $data = [];
    }

    global $awardID, $userID, $grantID, $categoryID, $requestID, $taskID, $logID;

    if (!empty($awardID)) {
        $data['aid'] = (int)$awardID;
    }

    if (!empty($userID)) {
        $data['uid'] = (int)$userID;
    }

    if (!empty($grantID)) {
        $data['gid'] = (int)$grantID;
    }

    if (!empty($categoryID)) {
        $data['cid'] = (int)$categoryID;
    }

    if (!empty($requestID)) {
        $data['rid'] = (int)$requestID;
    }

    if (!empty($taskID)) {
        $data['tid'] = (int)$taskID;
    }

    if (!empty($logID)) {
        $data['logID'] = (int)$logID;
    }

    if (defined('IN_ADMINCP')) {
        log_admin_action($data);
    } else {
        log_moderator_action($data);
    }

    return true;
}

function cacheUpdate(): bool
{
    global $db, $mybb;

    $statsLimit = min($mybb->settings['statslimit'], getSetting('statsLatestGrants'));

    $cacheData = [
        'time' => TIME_NOW,
        'awards' => [],
        'categories' => [],
        'requests' => ['pending' => 0],
        'tasks' => [],
        'top' => [],
        'last' => [],
    ];

    $queryFieldsCategories = ['cid', 'name', 'description', 'allowrequests', 'outputInCustomSection', 'hideInMainPage'];

    $queryFieldsAwards = [
        'aid',
        'cid',
        'name',
        'template',
        'description',
        'image',
        'allowrequests',
        'type',
        'disporder',
        'visible'
    ];

    $hookArguments = [
        'queryFieldsCategories' => &$queryFieldsCategories,
        'queryFieldsAwards' => &$queryFieldsAwards
    ];

    $hookArguments = runHooks('cache_update', $hookArguments);

    $query = $db->simple_select(
        'ougc_awards_categories',
        implode(',', $queryFieldsCategories),
        "visible='1'",
        ['order_by' => 'disporder']
    );

    while ($categoryData = $db->fetch_array($query)) {
        $cacheData['categories'][(int)$categoryData['cid']] = $categoryData;
    }

    if ($categoryIDs = array_keys($cacheData['categories'])) {
        $whereClauses = [
            "visible='1'",
            "cid IN ('" . implode("','", $categoryIDs) . "')"
        ];

        $query = $db->simple_select(
            'ougc_awards',
            implode(',', $queryFieldsAwards),
            implode(' AND ', $whereClauses),
            ['order_by' => 'disporder']
        );

        while ($awardData = $db->fetch_array($query)) {
            $cacheData['awards'][(int)$awardData['aid']] = $awardData;
        }
    }

    if ($awardIDs = array_keys($cacheData['awards'])) {
        $requestStatusOpen = REQUEST_STATUS_PENDING;

        $awardIDs = implode("','", $awardIDs);

        $whereClauses = [
            "aid IN ('{$awardIDs}')",
            'status' => "status='{$requestStatusOpen}'"
        ];

        $totalRequestsCount = requestGetPending(
            $whereClauses,
            ['COUNT(rid) AS totalRequests'],
            ['limit' => 1, 'group_by' => 'rid']
        );

        if (!empty($totalRequestsCount['totalRequests'])) {
            $cacheData['requests'] = ['pending' => (int)$totalRequestsCount['totalRequests']];
        }

        unset($whereClauses['status']);

        $whereClauses = implode(' AND ', $whereClauses);

        $query = $db->query(
            "
				SELECT u.uid, a.awards
				FROM {$db->table_prefix}users u
				LEFT JOIN (
					SELECT g.uid, COUNT(g.aid) AS awards
					FROM {$db->table_prefix}ougc_awards_users g
					WHERE g.{$whereClauses}
					GROUP BY g.uid, g.aid
				) a ON (u.uid=a.uid)
				WHERE a.awards!=''
				ORDER BY a.awards DESC
				LIMIT 0, {$statsLimit}
			;"
        );

        while ($userData = $db->fetch_array($query)) {
            $cacheData['top'][(int)$userData['uid']] = (int)$userData['awards'];
        }

        $query = $db->simple_select(
            'ougc_awards_users',
            'uid, date',
            $whereClauses,
            ['order_by' => 'date', 'order_dir' => 'desc', 'limit' => $statsLimit]
        );

        while ($userData = $db->fetch_array($query)) {
            $cacheData['last'][(int)$userData['date']] = (int)$userData['uid'];
        }
    }

    $query = $db->simple_select('ougc_awards_tasks', 'tid, name, reason', '', ['order_by' => 'disporder']);

    while ($task = $db->fetch_array($query)) {
        $cacheData['tasks'][(int)$task['tid']] = $task;
    }

    $mybb->cache->update('ougc_awards', $cacheData);

    return true;
}

function generateSelectAwards(string $inputName, array $selectedIDs = [], array $selectOptions = []): string
{
    global $db, $mybb;

    $selectCode = "<select name=\"{$inputName}\"";

    !isset($selectOptions['multiple']) || $selectCode .= " multiple=\"multiple\"";

    !isset($selectOptions['id']) || $selectCode .= " id=\"{$selectOptions['id']}\"";

    $selectCode .= '>';

    $dbQuery = $db->simple_select(
        'ougc_awards',
        'name, aid, cid',
        '',
        ['order_by' => 'cid asc, disporder', 'order_dir' => 'asc']
    );

    $awardsCache = [];

    while ($awardData = $db->fetch_array($dbQuery)) {
        $awardsCache[(int)$awardData['cid']][(int)$awardData['aid']] = htmlspecialchars_uni($awardData['name']);
    }

    foreach ($awardsCache as $categoryID => $categoryAwards) {
        foreach ($categoryAwards as $awardID => $awardName) {
            $selectedElement = '';

            if (in_array($awardID, $selectedIDs)) {
                $selectedElement = 'selected="selected"';
            }

            $selectCode .= "<option value=\"{$awardID}\"{$selectedElement}>{$awardName}</option>";
        }
    }

    $selectCode .= '</select>';

    return $selectCode;
}

function generateSelectProfileFields(string $inputName, array $selectedIDs = [], array $selectOptions = []): string
{
    global $db, $mybb;

    $selectCode = "<select name=\"{$inputName}\"";

    !isset($selectOptions['multiple']) || $selectCode .= " multiple=\"multiple\"";

    !isset($selectOptions['id']) || $selectCode .= " id=\"id\"";

    $selectCode .= '>';

    foreach (getProfileFieldsCache() as $profileFieldData) {
        $selectedElement = '';
        if (in_array($profileFieldData['fid'], $selectedIDs)) {
            $selectedElement = 'selected="selected"';
        }

        $selectCode .= "<option value=\"{$profileFieldData['fid']}\"{$selectedElement}>{$profileFieldData['name']}</option>";
    }

    $selectCode .= '</select>';

    return $selectCode;
}

function generateSelectGrant(int $awardID, int $userID, int $selectedID): string
{
    global $db, $mybb, $lang;

    $selectCode = "<select name=\"gid\">\n";

    $dbQuery = $db->simple_select(
        'ougc_awards_users',
        'gid, rid, tid, date, reason',
        "aid='{$awardID}' AND uid='{$userID}'"
    );

    while ($grantData = $db->fetch_array($dbQuery)) {
        $grantID = (int)$grantData['gid'];

        $requestID = (int)$grantData['rid'];

        $taskID = (int)$grantData['tid'];

        $selectedElement = '';

        if ($grantData['gid'] == $selectedID) {
            $selectedElement = 'selected="selected"';
        }

        $grantDate = my_date('relative', $grantData['date']);

        $grantReason = $grantData['reason'];

        parseMessage($grantReason);

        $selectCode .= "<option value=\"{$grantData['gid']}\"{$selectedElement}>" . $grantDate . ' (' . htmlspecialchars_uni(
                $grantData['reason']
            ) . ')</option>';
    }

    $selectCode .= '</select>';

    return $selectCode;
}

function generateSelectCategory(
    int $selectedID,
    string $selectName = 'categoryID',
    bool $showAllSelect = false
): string {
    global $db, $mybb;

    $dbQuery = $db->simple_select('ougc_awards_categories', 'cid, name', '', ['order_by' => 'disporder']);

    $selectOptions = $multipleOption = '';

    if ($showAllSelect) {
        global $lang;

        $optionValue = 0;

        $selectedElement = '';

        if ($selectedID === 0) {
            $selectedElement = 'selected="selected"';
        }

        $optionName = $lang->ougcAwardsGlobalAllCategories;

        $selectOptions .= eval(getTemplate('selectFieldOption'));
    }

    while ($categoryData = $db->fetch_array($dbQuery)) {
        $selectedElement = '';

        if ((int)$categoryData['cid'] === $selectedID) {
            $selectedElement = 'selected="selected"';
        }

        $optionValue = (int)$categoryData['cid'];

        $optionName = htmlspecialchars_uni($categoryData['name']);

        $onChange = '';

        $selectOptions .= eval(getTemplate('selectFieldOption'));
    }

    return eval(getTemplate('selectField'));
}

function generateSelectCustomReputation(string $inputName, int $selectedID = 0): string
{
    global $db, $mybb;

    if (!$db->table_exists('ougc_customrep')) {
        return '';
    }

    $selectCode = "<select name=\"{$inputName}\"";

    !isset($options['multiple']) || $selectCode .= " multiple=\"multiple\"";

    $selectCode .= '>';

    $dbQuery = $db->simple_select('ougc_customrep', 'rid, name', '', ['order_by' => 'disporder']);

    while ($reputationData = $db->fetch_array($dbQuery)) {
        $selectedElement = '';

        if ($reputationData['rid'] == $selectedID) {
            $selectedElement = 'selected="selected"';
        }

        $reputationName = htmlspecialchars_uni($reputationData['name']);

        $selectCode .= "<option value=\"{$reputationData['rid']}\"{$selectedElement}>{$reputationName}</option>";
    }

    $selectCode .= '</select>';

    return $selectCode;
}

function canManageUsers(int $userID): bool
{
    global $mybb;

    $currentUserID = (int)$mybb->user['uid'];

    if (
        is_super_admin($currentUserID) ||
        !is_super_admin($userID) ||
        $mybb->usergroup['cancp']
    ) {
        return true;
    }

    $userPermissions = user_permissions($userID);

    if (!$userPermissions['cancp']) {
        return true;
    }

    if (!defined('IN_ADMINCP')) {
        if (
            $mybb->usergroup['issupermod'] ||
            !$userPermissions['issupermod'] ||
            $mybb->user['ismoderator'] ||
            !is_moderator(0, '', $userID) ||
            $currentUserID !== $userID
        ) {
            return true;
        }
    }

    return false;
}

function canRequestAwards(int $awardID = 0, int $categoryID = 0): bool
{
    global $mybb;

    if (empty($mybb->user['uid'])) {
        return false;
    }

    if (!empty($awardID)) {
        $awardData = awardGet($awardID);

        $categoryID = (int)$awardData['cid'];

        if (empty($awardData['allowrequests'])) {
            return false;
        }
    }

    if (!empty($categoryID)) {
        $categoryData = categoryGet($categoryID);

        if (empty($categoryData['allowrequests'])) {
            return false;
        }
    }

    return true;
}

function canViewMainPage(): bool
{
    global $mybb;

    return (bool)is_member(getSetting('groupsView'));
}

function pluginIsInstalled(): bool
{
    return function_exists('ougc_awards_info');
}

function parsePresets(string &$preset_options, array $presetsCache, int $selectedID): string
{
    global $templates;

    $presetOptions = '';

    if (!empty($presetsCache)) {
        foreach ($presetsCache as $preset) {
            $preset['name'] = htmlspecialchars_uni($preset['name']);

            $selected = '';

            if ($selectedID === (int)$preset['pid']) {
                $selected = ' selected="selected"';
            }

            $presetOptions .= eval(getTemplate('usercp_presets_select_option'));
        }
    }

    return $presetOptions;
}

function parseMessage(string &$messageContent): string
{
    return parserObject()->parse_message(
        $messageContent,
        [
            'allow_html' => false,
            'allow_mycode' => true,
            'allow_smilies' => true,
            'allow_imgcode' => true,
            'filter_badwords' => true,
            'nl2br' => false
        ]
    );
}

function parserObject(): postParser
{
    global $parser;

    if (!($parser instanceof postParser)) {
        require_once MYBB_ROOT . 'inc/class_parser.php';

        $parser = new postParser();
    }

    return $parser;
}

function parseUserAwards(
    string &$formattedContent,
    array $grantCacheData,
    string $templateName,
    array $grantsGroupCache = []
): string {
    $awardsCategoriesCache = awardsCacheGet()['categories'];

    global $mybb, $lang;

    loadLanguage();

    $alternativeBackground = alt_trow(true);

    static $threadsCache = [];

    foreach ($grantCacheData as $grantData) {
        $grantThreadID = (int)$grantData['thread'];

        if (!isset($threadsCache[$grantThreadID])) {
            $threadIDs = array_filter(array_map('intval', array_column($grantCacheData, 'thread')));

            if ($threadIDs) {
                global $db;

                $threadIDs = implode("','", $threadIDs);

                $dbQuery = $db->simple_select(
                    'threads',
                    'tid, subject, prefix',
                    "visible>0  AND closed NOT LIKE 'moved|%' AND tid IN ('{$threadIDs}')"
                );

                while ($threadData = $db->fetch_array($dbQuery)) {
                    $threadsCache[(int)$threadData['tid']] = $threadData;
                }
            }

            break;
        }
    }

    $awardsCache = awardsCacheGet()['awards'];

    foreach ($grantCacheData as $grantData) {
        $awardID = (int)$grantData['aid'];

        $awardData = $awardsCache[$awardID];

        $categoryID = (int)$awardData['cid'];

        $categoryData = $awardsCategoriesCache[$categoryID];

        $categoryName = htmlspecialchars_uni($categoryData['name']);

        $categoryDescription = htmlspecialchars_uni($categoryData['description']);

        $awardName = htmlspecialchars_uni($awardData['name']);

        $totalAwardGrants = 0;

        if (!empty($grantsGroupCache[$awardID]['total_grants'])) {
            $totalAwardGrants = (int)$grantsGroupCache[$awardID]['total_grants'];
        }

        if ($totalAwardGrants > 1) {
            $totalAwardGrants = my_number_format($totalAwardGrants);

            $totalAwardGrants = eval(getTemplate($templateName . 'TotalCount', false));

            $awardName .= $totalAwardGrants;
        } else {
            $totalAwardGrants = '';
        }

        $awardDescription = htmlspecialchars_uni($awardData['description']);

        $grantID = (int)$grantData['gid'];

        $requestID = (int)$grantData['rid'];

        $taskID = (int)$grantData['tid'];

        $grantReason = $grantData['reason'];

        parseMessage($grantReason);

        $threadLink = '';

        $grantThreadID = (int)$grantData['thread'];

        if (isset($threadsCache[$grantThreadID])) {
            $threadData = $threadsCache[$grantThreadID];

            $threadData['threadPrefix'] = $threadData['threadPrefixDisplay'] = '';

            if ($threadData['prefix']) {
                $prefixData = build_prefixes($threadData['prefix']);

                if (!empty($prefixData['prefix'])) {
                    $threadData['threadPrefix'] = $prefixData['prefix'] . '&nbsp;';

                    $threadData['threadPrefixDisplay'] = $prefixData['displaystyle'] . '&nbsp;';
                }
            }

            $threadSubject = htmlspecialchars_uni(
                parserObject()->parse_badwords($threadData['subject'])
            );

            $threadLink = get_thread_link($threadData['tid']);

            $threadLink = eval(getTemplate("{$templateName}Link"));
        }

        $awardImage = $awardClass = awardGetIcon($awardID);

        $awardImage = eval(
        getTemplate(
            $awardData['template'] === AWARD_TEMPLATE_TYPE_CLASS ? 'awardImageClass' : 'awardImage'
        )
        );

        $awardUrl = urlHandlerBuild(['action' => 'viewUsers', 'awardID' => $awardID]);

        $awardImage = eval(getTemplate('awardWrapper', false));

        $grantDate = my_date('normal', $grantData['date']);

        global $theme;

        $formattedContent .= eval(getTemplate($templateName));

        $alternativeBackground = alt_trow();
    }

    return $formattedContent;
}

// Most of this was taken from @Starpaul20's Move Post plugin (https://github.com/PaulBender/Move-Posts)
function getThreadByUrl(string $threadUrl): array
{
    global $db, $mybb;

    // Google SEO URL support
    if ($db->table_exists('google_seo')) {
        $regexp = "{$mybb->settings['bburl']}/{$mybb->settings['google_seo_url_threads']}";

        if ($regexp) {
            $regexp = preg_quote($regexp, '#');
            $regexp = str_replace('\\{\\$url\\}', '([^./]+)', $regexp);
            $regexp = str_replace('\\{url\\}', '([^./]+)', $regexp);
            $regexp = "#^{$regexp}$#u";
        }

        $url = $threadUrl;

        $url = preg_replace('/^([^#?]*)[#?].*$/u', '\\1', $url);

        $url = preg_replace($regexp, '\\1', $url);

        $url = urldecode($url);

        $query = $db->simple_select('google_seo', 'id', "idtype='4' AND url='" . $db->escape_string($url) . "'");
        $threadID = $db->fetch_field($query, 'id');
    }

    $real_url = explode('#', $threadUrl);

    $threadUrl = $real_url[0];

    if (substr($threadUrl, -4) == 'html') {
        preg_match('#thread-([0-9]+)?#i', $threadUrl, $threadmatch);

        preg_match('#post-([0-9]+)?#i', $threadUrl, $postmatch);

        if ($threadmatch[1]) {
            $parameters['tid'] = $threadmatch[1];
        }

        if ($postmatch[1]) {
            $parameters['pid'] = $postmatch[1];
        }
    } else {
        $splitloc = explode('.php', $threadUrl);

        $temp = explode('&', my_substr($splitloc[1], 1));

        if (!empty($temp)) {
            for ($i = 0; $i < count($temp); $i++) {
                $temp2 = explode('=', $temp[$i], MyBB::INPUT_ARRAY);

                $parameters[$temp2[0]] = $temp2[1];
            }
        } else {
            $temp2 = explode('=', $splitloc[1], MyBB::INPUT_ARRAY);

            $parameters[$temp2[0]] = $temp2[1];
        }
    }

    $threadID = 0;

    if (!empty($parameters['pid']) && empty($parameters['tid'])) {
        $post = get_post($parameters['pid']);

        $threadID = $post['tid'];
    } elseif (!empty($parameters['tid'])) {
        $threadID = $parameters['tid'];
    }

    return (array)get_thread($threadID);
}

function isModerator(): bool
{
    return (bool)is_member(getSetting('groupsModerators'));
}

function isVisibleCategory(int $categoryID): bool
{
    global $mybb;

    $categoryData = categoryGet($categoryID);

    $currentUserID = (int)$mybb->user['uid'];

    return !empty($categoryData['visible']) || isModerator();
}

function isVisibleAward(int $awardID): bool
{
    global $mybb;

    $awardData = awardGet($awardID);

    $categoryID = (int)$awardData['cid'];

    $currentUserID = (int)$mybb->user['uid'];

    return !empty($awardData['visible']) || isModerator() || ownerCategoryFind($categoryID, $currentUserID);
}

function myAlertsInitiate(): bool
{
    if (!function_exists('myalerts_info')) {
        return false;
    }

    if (class_exists('MybbStuff_MyAlerts_Formatter_AbstractFormatter')) {
        require_once ROOT . '/class_alerts.php';
    }

    if (version_compare(myalerts_info()['version'], getSetting('myAlertsVersion')) <= 0) {
        myalerts_register_client_alert_formatters();
    }

    return true;
}

function uploadAward(array $awardFile, int $awardID): array
{
    require_once MYBB_ROOT . 'inc/functions_upload.php';

    if (!is_uploaded_file($awardFile['tmp_name'])) {
        return ['error' => FILE_UPLOAD_ERROR_FAILED];
    }

    $fileExtension = get_extension(my_strtolower($awardFile['name']));

    if (!preg_match('#^(gif|jpg|jpeg|jpe|bmp|png)$#i', $fileExtension)) {
        return ['error' => FILE_UPLOAD_ERROR_INVALID_TYPE];
    }

    $uploadPath = getSetting('uploadPath');

    $fileName = "award_{$awardID}.{$fileExtension}";

    $fileUpload = upload_file($awardFile, $uploadPath, $fileName);

    $fullFilePath = "{$uploadPath}/{$fileName}";

    if (!empty($fileUpload['error'])) {
        delete_uploaded_file($fullFilePath);

        return ['error' => FILE_UPLOAD_ERROR_FAILED];
    }

    if (!file_exists($fullFilePath)) {
        delete_uploaded_file($fullFilePath);

        return ['error' => FILE_UPLOAD_ERROR_FAILED];
    }

    $imageDimensions = getimagesize($fullFilePath);

    if (!is_array($imageDimensions)) {
        delete_uploaded_file($fullFilePath);

        return ['error' => FILE_UPLOAD_ERROR_FAILED];
    }

    if (getSetting('uploadDimensions')) {
        list($maximumWidth, $maximumHeight) = preg_split('/[|x]/', getSetting('uploadDimensions'));

        if (($maximumWidth && $imageDimensions[0] > $maximumWidth) || ($maximumHeight && $imageDimensions[1] > $maximumHeight)) {
            require_once MYBB_ROOT . 'inc/functions_image.php';

            $thumbnail = generate_thumbnail(
                $fullFilePath,
                $uploadPath,
                $fileName,
                $maximumHeight,
                $maximumWidth
            );

            if (empty($thumbnail['filename'])) {
                delete_uploaded_file($fullFilePath);

                return ['error' => FILE_UPLOAD_ERROR_RESIZE];
            } else {
                copy_file_to_cdn("{$uploadPath}/{$thumbnail['filename']}");

                $awardFile['size'] = filesize($fullFilePath);

                $imageDimensions = getimagesize($fullFilePath);
            }
        }
    }

    $awardFile['type'] = my_strtolower($awardFile['type']);

    switch ($awardFile['type']) {
        case 'image/gif':
            $imageType = 1;
            break;
        case 'image/jpeg':
        case 'image/x-jpg':
        case 'image/x-jpeg':
        case 'image/pjpeg':
        case 'image/jpg':
            $imageType = 2;
            break;
        case 'image/png':
        case 'image/x-png':
            $imageType = 3;
            break;
        case 'image/bmp':
        case 'image/x-bmp':
        case 'image/x-windows-bmp':
            $imageType = 6;
            break;
    }

    if (empty($imageType) || (int)$imageDimensions[2] !== $imageType) {
        delete_uploaded_file($fullFilePath);

        return ['error' => FILE_UPLOAD_ERROR_FAILED];
    }

    if (getSetting('uploadSize') > 0 && $awardFile['size'] > (getSetting('uploadSize') * 1024)) {
        delete_uploaded_file($fullFilePath);

        return ['error' => FILE_UPLOAD_ERROR_UPLOAD_SIZE];
    }

    return [
        'fileName' => $fileName,
        'fileWidth' => (int)$imageDimensions[0],
        'fileHeight' => (int)$imageDimensions[1]
    ];
}

function awardsCacheGet(): array
{
    global $mybb;

    return (array)$mybb->cache->read('ougc_awards');
}

function getTimeTypes(): array
{
    global $lang;

    loadLanguage();

    return [
        TASK_REQUIREMENT_TIME_TYPE_HOURS => $lang->ougcAwardsControlPanelHours,
        TASK_REQUIREMENT_TIME_TYPE_DAYS => $lang->ougcAwardsControlPanelDays,
        TASK_REQUIREMENT_TIME_TYPE_WEEKS => $lang->ougcAwardsControlPanelWeeks,
        TASK_REQUIREMENT_TIME_TYPE_MONTHS => $lang->ougcAwardsControlPanelMonths,
        TASK_REQUIREMENT_TIME_TYPE_YEARS => $lang->ougcAwardsControlPanelYears,
    ];
}

function getComparisonTypes(): array
{
    global $lang;

    loadLanguage(true);

    return [
        COMPARISON_TYPE_GREATER_THAN => $lang->ougcAwardsControlPanelGreaterThan,
        COMPARISON_TYPE_GREATER_THAN_OR_EQUAL => $lang->ougcAwardsControlPanelGreaterThanOrEqualTo,
        COMPARISON_TYPE_EQUAL => $lang->ougcAwardsControlPanelEqualTo,
        COMPARISON_TYPE_NOT_EQUAL => $lang->ougcAwardsControlPanelNotEqualTo,
        COMPARISON_TYPE_LESS_THAN_OR_EQUAL => $lang->ougcAwardsControlPanelLessThanOrEqualTo,
        COMPARISON_TYPE_LESS_THAN => $lang->ougcAwardsControlPanelLessThan,
    ];
}

function getComparisonLanguageVariable(string $comparisonOperator): string
{
    global $lang;

    loadLanguage();

    switch ($comparisonOperator) {
        case COMPARISON_TYPE_GREATER_THAN:
            return $lang->ougcAwardsControlPanelViewTasksTypeGreaterThan;
        case COMPARISON_TYPE_GREATER_THAN_OR_EQUAL:
            return $lang->ougcAwardsControlPanelViewTasksTypeGreaterThanOrEqualTo;
        case COMPARISON_TYPE_EQUAL:
            return $lang->ougcAwardsControlPanelViewTasksTypeEqualTo;
        case COMPARISON_TYPE_NOT_EQUAL:
            return $lang->ougcAwardsControlPanelViewTasksTypeNotEqualTo;
        case COMPARISON_TYPE_LESS_THAN_OR_EQUAL:
            return $lang->ougcAwardsControlPanelViewTasksTypeLessThanOrEqualTo;
        case COMPARISON_TYPE_LESS_THAN:
            return $lang->ougcAwardsControlPanelViewTasksTypeLessThan;
    }

    return '';
}

function getComparisonResult(string $comparisonType, int $userValue, int $settingValue): bool
{
    $comparisonResult = false;

    switch ($comparisonType) {
        case COMPARISON_TYPE_GREATER_THAN;
            $comparisonResult = $userValue > $settingValue;
            break;
        case COMPARISON_TYPE_GREATER_THAN_OR_EQUAL;
            $comparisonResult = $userValue >= $settingValue;
            break;
        case COMPARISON_TYPE_EQUAL;
            $comparisonResult = $userValue == $settingValue;
            break;
        case COMPARISON_TYPE_NOT_EQUAL;
            $comparisonResult = $userValue != $settingValue;
            break;
        case COMPARISON_TYPE_LESS_THAN_OR_EQUAL;
            $comparisonResult = $userValue <= $settingValue;
            break;
        case COMPARISON_TYPE_LESS_THAN;
            $comparisonResult = $userValue < $settingValue;
            break;
    }

    return $comparisonResult;
}

function getTimeLanguageVariable(string $timeType, bool $isPlural): string
{
    global $lang;

    switch ($timeType) {
        case TASK_REQUIREMENT_TIME_TYPE_HOURS:
            if ($isPlural) {
                return $lang->ougcAwardsControlPanelViewTasksTimeTypeHoursPlural;
            }
            return $lang->ougcAwardsControlPanelViewTasksTimeTypeHours;
        case TASK_REQUIREMENT_TIME_TYPE_DAYS:
            if ($isPlural) {
                return $lang->ougcAwardsControlPanelViewTasksTimeTypeDaysPlural;
            }
            return $lang->ougcAwardsControlPanelViewTasksTimeTypeDays;
        case TASK_REQUIREMENT_TIME_TYPE_WEEKS:
            if ($isPlural) {
                return $lang->ougcAwardsControlPanelViewTasksTimeTypeWeeksPlural;
            }
            return $lang->ougcAwardsControlPanelViewTasksTimeTypeWeeks;
        case TASK_REQUIREMENT_TIME_TYPE_MONTHS:
            if ($isPlural) {
                return $lang->ougcAwardsControlPanelViewTasksTimeTypeMonthsPlural;
            }
            return $lang->ougcAwardsControlPanelViewTasksTimeTypeMonths;
        case TASK_REQUIREMENT_TIME_TYPE_YEARS:
            if ($isPlural) {
                return $lang->ougcAwardsControlPanelViewTasksTimeTypeYearsPlural;
            }
            return $lang->ougcAwardsControlPanelViewTasksTimeTypeYears;
    }

    return '';
}

function getProfileFieldsCache(): array
{
    global $mybb;
    global $profiecats;

    if (
        class_exists('OUGC_ProfiecatsCache') && $profiecats instanceof OUGC_ProfiecatsCache &&
        !empty($profiecats->cache['original'])
    ) {
        return $profiecats->cache['original'];
    }

    return (array)$mybb->cache->read('profilefields');
}