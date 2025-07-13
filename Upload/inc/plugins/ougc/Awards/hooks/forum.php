<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/ougc/Awards/hooks/admin.php)
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

namespace ougc\Awards\Hooks\Forum;

use MyBB;
use MybbStuff_MyAlerts_AlertFormatterManager;
use ougc\Awards\Core\MyAlertsFormatter;

use function ougc\Awards\Core\awardGetUser;
use function ougc\Awards\Core\awardsCacheGet;
use function ougc\Awards\Core\cacheUpdate;
use function ougc\Awards\Core\executeTask;
use function ougc\Awards\Core\grantCount;
use function ougc\Awards\Core\isModerator;
use function ougc\Awards\Core\myAlertsInitiate;
use function ougc\Awards\Core\ownerGetUserAwards;
use function ougc\Awards\Core\parseUserAwards;
use function ougc\Awards\Core\presetGet;
use function ougc\Awards\Core\presetUpdate;
use function ougc\Awards\Core\loadLanguage;
use function ougc\Awards\Core\getTemplate;
use function ougc\Awards\Core\requestGet;
use function ougc\Awards\Core\requestsCount;
use function ougc\Awards\Core\urlHandlerBuild;
use function ougc\Awards\Core\getSetting;

use const TIME_NOW;
use const ougc\Awards\Core\DEBUG;
use const ougc\Awards\Core\AWARDS_SECTION_NONE;
use const ougc\Awards\Core\PLUGIN_VERSION_CODE;
use const ougc\Awards\Core\GRANT_STATUS_POSTS;
use const ougc\Awards\Core\GRANT_STATUS_VISIBLE;
use const ougc\Awards\Core\REQUEST_STATUS_PENDING;

function global_start05(): bool
{
    if (DEBUG) {
        executeTask();
    }

    myAlertsInitiate();

    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= 'ougcawards_' . implode(',ougcawards_', [
            'awardImage',
            'awardImageClass',
            'awardWrapper',
            'css',
            'global_menu',
            'globalNotification',
            'globalPagination',
            'js',
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
            'stats',
            'stats_empty',
            'statsUserRow',
            'streamItem',
            'viewAll',
            'viewAllSection'
        ]);

    return true;
}

function global_intermediate(): bool
{
    global $mybb, $db, $lang, $templates, $ougcAwardsMenu, $ougcAwardsGlobalNotificationRequests, $ougcAwardsViewAll, $ougcAwardsViewAllSections, $ougcAwardsJavaScript, $ougcAwardsCSS;

    loadLanguage();

    if (getSetting('enableDvzStream') && isset($mybb->settings['dvz_stream_active_streams'])) {
        $mybb->settings['dvz_stream_active_streams'] .= ',ougcAwards';
    }

    $currentUserID = (int)$mybb->user['uid'];

    $fileVersion = PLUGIN_VERSION_CODE;

    if (DEBUG) {
        $fileVersion = TIME_NOW;
    }

    $ougcAwardsJavaScript = eval(getTemplate('js'));

    $ougcAwardsCSS = eval(getTemplate('css'));

    $ougcAwardsMenu = eval(getTemplate('global_menu'));

    $ougcAwardsGlobalNotificationRequests = $ougcAwardsViewAll = '';

    $ougcAwardsViewAllSections = [];

    $awardsCategoriesCache = awardsCacheGet()['categories'];

    foreach ($awardsCategoriesCache as $sectionID => $categoryData) {
        $ougcAwardsViewAllSections["section{$sectionID}"] = '';
    }

    if ($currentUserID) {
        $ougcAwardsViewAll = eval(getTemplate('viewAll'));

        foreach ($awardsCategoriesCache as $sectionID => $categoryData) {
            $sectionName = htmlspecialchars_uni($categoryData['name']);

            $sectionTitle = $lang->sprintf(
                $lang->ougcAwardsWelcomeLinkTextSection,
                $sectionName
            );

            $ougcAwardsViewAllSections["section{$sectionID}"] = eval(getTemplate('viewAllSection'));
        }
    }

    $cacheData = awardsCacheGet();

    if ($cacheData['time'] > (TIME_NOW - (60 * 5))) {
        cacheUpdate();
    }

    if (!$mybb->user['uid']) {
        return false;
    }

    $ownsCategories = !empty($mybb->user['ougc_awards_category_owner']);

    $ownsAwards = $ownsCategories || !empty($mybb->user['ougc_awards_owner']);

    if (!isModerator() && !$ownsAwards) {
        return false;
    }

    cacheUpdate();

    $awardsCache = $cacheData['awards'] ?? [];

    $awardRequestsCache = $cacheData['requests'] ?? [];

    $pendingRequestCount = empty($awardRequestsCache['pending']) ? 0 : (int)$awardRequestsCache['pending'];

    if (!isModerator() && $pendingRequestCount && $ownerAwardIDs = ownerGetUserAwards()) {
        $ownerAwardIDs = implode("','", array_keys($ownerAwardIDs));

        $statusPending = REQUEST_STATUS_PENDING;

        $pendingRequestCount = (int)(requestsCount(
            ["status='{$statusPending}'", "aid IN ('{$ownerAwardIDs}')"]
        )['total_requests'] ?? 0);
    }

    if ($pendingRequestCount < 1) {
        return false;
    }

    $messageContent = $lang->sprintf(
        $pendingRequestCount > 1 ? $lang->ougcAwardsGlobalNotificationRequestsPlural : $lang->ougcAwardsGlobalNotificationRequests,
        $mybb->settings['bburl'],
        urlHandlerBuild(['action' => 'viewRequests']),
        my_number_format($pendingRequestCount)
    );

    $ougcAwardsGlobalNotificationRequests = eval(getTemplate('globalNotification'));

    return true;
}

function fetch_wol_activity_end(array &$activityArguments): array
{
    if ($activityArguments['activity'] === 'unknown' && my_strpos(
            $activityArguments['location'],
            'awards.php'
        ) !== false) {
        $activityArguments['activity'] = 'ougc_awards';
    }

    return $activityArguments;
}

function build_friendly_wol_location_end(array &$locationArguments): array
{
    if ($locationArguments['user_activity']['activity'] === 'ougc_awards') {
        global $mybb, $lang;

        loadLanguage();

        $locationArguments['location_name'] = $lang->sprintf(
            $lang->ougcAwardsWhoIsOnlineViewing,
            $mybb->settings['bburl']
        );
    }

    return $locationArguments;
}

function xmlhttp_02(): bool
{
    global $mybb;

    if (getSetting('enableDvzStream') && isset($mybb->settings['dvz_stream_active_streams'])) {
        $mybb->settings['dvz_stream_active_streams'] .= ',ougcAwards';
    }

    myAlertsInitiate();

    return true;
}

function xmlhttp(): bool
{
    global $mybb, $lang;

    if ($mybb->get_input('action') === 'awardPresets') {
        loadLanguage();

        $mybb->input['ajax'] = 1;

        if (!is_member(getSetting('groupsPresets'))) {
            error_no_permission();
        }

        $presetID = $mybb->get_input('presetID', MyBB::INPUT_INT);

        $currentUserID = (int)$mybb->user['uid'];

        if ($presetID && !($currentPresetData = presetGet(
                [
                    "pid='{$presetID}'",
                    "uid='{$currentUserID}'"
                ],
                ['pid'],
                ['limit' => 1]
            ))) {
            error_no_permission();
        }

        if (!empty($lang->settings['charset'])) {
            $charset = $lang->settings['charset'];
        } else {
            $charset = 'UTF-8';
        }

        header("Content-type: application/json; charset={$charset}");

        $responseData = [];

        if ($mybb->request_method === 'post') {
            if (!empty($currentPresetData)) {
                $hiddenAwards = json_decode($mybb->get_input('hiddenAwards'), true);

                $hiddenAwards = empty($hiddenAwards) ? '' : my_serialize(array_map('intval', $hiddenAwards));

                $visibleAwards = json_decode($mybb->get_input('visibleAwards'), true);

                $visibleAwards = empty($visibleAwards) ? '' : my_serialize(array_map('intval', $visibleAwards));

                if (presetUpdate([
                    'hidden' => $hiddenAwards,
                    'visible' => $visibleAwards,
                ], $presetID)) {
                    $responseData = ['success' => $lang->ougcAwardsControlPanelPresetsSuccess];
                } else {
                    $responseData = ['error' => $lang->ougcAwardsControlPanelPresetsError];
                }
            }
        }

        echo json_encode($responseData);

        exit;
    }

    return true;
}

function postbit_prev(array &$postData): array
{
    return postbit($postData);
}

function postbit_pm(array &$postData): array
{
    return postbit($postData);
}

function postbit_announcement(array &$postData): array
{
    return postbit($postData);
}

function postbit(array &$postData): array
{
    return member_profile_end($postData);
}

function myshowcase_system_render_build_entry_comment_end(array &$hookArguments): array
{
    if ($hookArguments['postType'] === $hookArguments['renderObject']::POST_TYPE_ENTRY && $hookArguments['renderObject']->showcaseObject->config['display_user_details_entries'] ||
        $hookArguments['postType'] === $hookArguments['renderObject']::POST_TYPE_COMMENT && $hookArguments['renderObject']->showcaseObject->config['display_user_details_comments']) {
        $hookArguments['userData'] = member_profile_end($hookArguments['userData']);
    }

    return $hookArguments;
}

function showthread_start(): void
{
    global $mybb;

    $isAjaxCall = $mybb->get_input('viewAwards', MyBB::INPUT_INT) === 1;

    if (!$isAjaxCall) {
        return;
    }

    global $thread;

    $threadID = $mybb->get_input('tid', MyBB::INPUT_INT);

    $postID = $mybb->get_input('pid', MyBB::INPUT_INT);

    $postData = get_post($postID);

    $userID = $mybb->get_input('uid', MyBB::INPUT_INT);

    if ($threadID !== (int)$thread['tid'] || $threadID !== (int)$postData['tid'] || $userID !== (int)$postData['uid']) {
        return;
    }

    $userData = array_merge(get_post($postID), get_user($userID));

    member_profile_end($userData);
}

function member_profile_end(&$userData = []): array
{
    global $mybb, $plugins, $lang, $theme;

    // THIS_SCRIPT check is a workaround for https://github.com/mybb/mybb/issues/4861
    $isProfilePage = $plugins->current_hook === 'member_profile_end' || THIS_SCRIPT === 'member.php';

    $isShowcasePage = $plugins->current_hook === 'myshowcase_system_render_build_entry_comment_end' || THIS_SCRIPT === 'showcase.php';

    if ($isProfilePage) {
        global $memprofile;

        $userData = &$memprofile;
    }

    $userData['ougc_awards'] = $userData['ougc_awards_preset'] = $userData['ougc_awards_view_all'] = '';

    $userID = (int)$userData['uid'];

    static $usersAwardsCache = [];

    // todo, we remove this for now because it is using the same pid for all posts, which breaks the pagination
    // instead should bulk query and only query results should be cached
    if (false && isset($usersAwardsCache[$userID])) {
        return array_merge($userData, $usersAwardsCache[$userID]);
    } else {
        $usersAwardsCache[$userID]['ougc_awards'] = &$userData['ougc_awards'];

        $usersAwardsCache[$userID]['ougc_awards_preset'] = &$userData['ougc_awards_preset'];

        $usersAwardsCache[$userID]['ougc_awards_view_all'] = &$userData['ougc_awards_view_all'];
    }

    if (!isset($userData['additionalgroups'])) {
        $userData['additionalgroups'] = '';
    }

    if ($isProfilePage) {
        $maximumAwardsToDisplay = (int)getSetting('showInProfile');

        $groupAwardGrants = getSetting('groupAwardGrantsInProfiles');

        $templatePrefix = 'profile';
    } elseif ($isShowcasePage) {
        // todo, implement custom settings per showcase
        $maximumAwardsToDisplay = (int)getSetting('showInPosts');

        $groupAwardGrants = getSetting('groupAwardGrantsInPosts');

        $templatePrefix = 'postBit';
    } else {
        $maximumAwardsToDisplay = (int)getSetting('showInPosts');

        $groupAwardGrants = getSetting('groupAwardGrantsInPosts');

        $templatePrefix = 'postBit';
    }

    if ($maximumAwardsToDisplay < 1) {
        $maximumAwardsToDisplay = 0;
    }

    $maximumPresetAwardsToDisplay = 0;

    if (is_member(getSetting('groupsPresets'), $userData)) {
        if ($isProfilePage) {
            $maximumPresetAwardsToDisplay = (int)getSetting('showInProfilePresets');
        } else {
            $maximumPresetAwardsToDisplay = (int)getSetting('showInPostsPresets');
        }

        if ($maximumPresetAwardsToDisplay < 1) {
            $maximumPresetAwardsToDisplay = 0;
        }
    }

    if ($maximumPresetAwardsToDisplay) {
        $presetID = (int)(get_user($userID)['ougc_awards_preset'] ?? 0);

        $presetData = presetGet(
            ["pid='{$presetID}'", "uid='{$userID}'"],
            ['uid', 'name', 'hidden', 'visible'],
            ['limit' => 1]
        );

        if (empty($presetData['visible'])) {
            $maximumPresetAwardsToDisplay = 0;
        }
    }

    $categoryIDs = [];

    $awardsCategoriesCache = awardsCacheGet()['categories'];

    foreach ($awardsCategoriesCache as $categoryID => $categoryData) {
        $userData["ougcAwardsSection{$categoryID}"] = $userData["ougcAwardsViewAllSection{$categoryID}"] = $usersAwardsCache[$userID]["ougcAwardsSection{$categoryID}"] = $usersAwardsCache[$userID]["ougcAwardsViewAllSection{$categoryID}"] = '';
    }

    if (!$maximumAwardsToDisplay && !$maximumPresetAwardsToDisplay) {
        return $userData;
    }

    loadLanguage();

    $primarySectionAwardsIDs = $categorySectionsAwardsIDs = $userAllAwardsIDs = [];

    $categoriesIDs = implode("','", array_keys($awardsCategoriesCache));

    $awardsCache = awardsCacheGet()['awards'];

    foreach ($awardsCache as $awardID => $awardData) {
        $categoryID = (int)$awardData['cid'];

        if (!empty($awardsCategoriesCache[$categoryID]) &&
            (int)$awardData['type'] !== GRANT_STATUS_POSTS) {
            if (empty($awardsCategoriesCache[$categoryID]['outputInCustomSection'])) {
                $primarySectionAwardsIDs[$awardID] = $userAllAwardsIDs[] = $awardID;
            } else {
                $categorySectionsAwardsIDs[$categoryID][$awardID] = $userAllAwardsIDs[] = $awardID;
            }
        }
    }

    $primarySectionAwardsIDs = implode("','", $primarySectionAwardsIDs);

    $grantStatusVisible = GRANT_STATUS_VISIBLE;

    $whereClauses = [
        "uid='{$userID}'",
        "visible='{$grantStatusVisible}'",
    ];

    $presetList = '';

    if (/*$totalGrantedCount &&*/ $maximumPresetAwardsToDisplay) {
        $presetAwards = array_filter(
            !empty($presetData['visible']) ? (array)my_unserialize($presetData['visible']) : []
        );

        $presetAwards = implode("','", $presetAwards);

        $userAllAwardsIDs = implode("','", $userAllAwardsIDs);

        $grantPresetsCacheData = awardGetUser(
            array_merge($whereClauses, ["gid IN ('{$presetAwards}')", "aid IN ('{$userAllAwardsIDs}')"]),
            ['uid', 'oid', 'aid', 'rid', 'tid', 'thread', 'reason', 'pm', 'date', 'disporder', 'visible'],
            [
                'order_by' => 'disporder, date',
                'order_dir' => 'desc',
                'limit' => $maximumPresetAwardsToDisplay,
            ]
        );

        foreach ($grantPresetsCacheData as $v) {
            if (!is_array($v)) {
                $grantPresetsCacheData = [$grantPresetsCacheData];

                break;
            }
        }

        parseUserAwards($presetList, $grantPresetsCacheData, $templatePrefix . 'PresetsRow');
    }

    $queryOptions = [
        'order_by' => 'disporder, date',
        'order_dir' => 'desc'
    ];

    $queryFields = [
        'uid',
        'oid',
        'aid',
        'rid',
        'tid',
        'thread',
        'reason',
        'pm',
        'date',
        'disporder',
        'visible'
    ];

    $sectionObjects = [
        0 => [
            'whereClauses' => array_merge($whereClauses, ["aid IN ('{$primarySectionAwardsIDs}')"]),
            'queryFields' => $queryFields,
            'sectionVariable' => &$userData['ougc_awards']
        ]
    ];

    foreach ($awardsCategoriesCache as $categoryID => $categoryData) {
        if (!empty($categoryData['outputInCustomSection']) && !empty($categorySectionsAwardsIDs[$categoryID])) {
            $sectionAwardsIDs = implode("','", $categorySectionsAwardsIDs[$categoryID]);

            $sectionObjects[$categoryID] = [
                'whereClauses' => array_merge($whereClauses, ["aid IN ('{$sectionAwardsIDs}')"]),
                'queryFields' => $queryFields,
                'sectionVariable' => &$userData["ougcAwardsSection{$categoryID}"],
                'sectionVariableCache' => &$usersAwardsCache[$userID]["ougcAwardsSection{$categoryID}"]
            ];
        }
    }

    $isAjaxCall = $mybb->get_input('viewAwards', MyBB::INPUT_INT) === 1;

    $currentSectionID = $mybb->get_input('sectionID', MyBB::INPUT_INT);

    // uses the post id in posts, nothing on profiles as it doesn't matter
    $postID = (int)($userData['pid'] ?? 0);

    foreach ($sectionObjects as $sectionID => $sectionData) {
        if ($isAjaxCall && $currentSectionID !== $sectionID) {
            continue;
        }

        if ($sectionID === AWARDS_SECTION_NONE) {
            $sectionName = '';
        } else {
            $sectionName = htmlspecialchars_uni($awardsCategoriesCache[$sectionID]['name']);

            $sectionTitle = $lang->sprintf(
                $lang->ougcAwardsWelcomeLinkTextSection,
                $sectionName
            );

            $userData["ougcAwardsViewAllSection{$sectionID}"] = $usersAwardsCache[$userID]["ougcAwardsViewAllSection{$sectionID}"] = eval(
            getTemplate(
                $templatePrefix . 'ViewAllSection'
            )
            );
        }

        $grantedList = '';

        if ($groupAwardGrants) {
            $totalGrantedCount = (int)(grantCount(
                $sectionData['whereClauses'],
                ['limit' => 1, 'group_by' => 'aid'],
                ['COUNT(DISTINCT aid) AS total_grants']
            )['total_grants'] ?? 0);
        } else {
            $totalGrantedCount = (int)(grantCount(
                $sectionData['whereClauses']
            )['total_grants'] ?? 0);
        }

        if ($totalGrantedCount > $maximumAwardsToDisplay && empty($userData['ougc_awards_view_all'])) {
            $userData['ougc_awards_view_all'] = eval(getTemplate($templatePrefix . 'ViewAll'));
        }

        $paginationMenu = '';

        $startPage = 0;

        if ($maximumAwardsToDisplay && $totalGrantedCount) {
            $currentPage = $isAjaxCall ? $mybb->get_input('page' . $sectionID, MyBB::INPUT_INT) : 1;

            if ($currentPage > 0) {
                $startPage = ($currentPage - 1) * $maximumAwardsToDisplay;

                if ($currentPage > ceil($totalGrantedCount / $maximumAwardsToDisplay)) {
                    $startPage = 0;

                    $currentPage = 1;
                }
            }

            $paginationMenu = (string)multipage(
                $totalGrantedCount,
                $maximumAwardsToDisplay,
                $currentPage,
                "javascript: ougcAwards.ViewAwards('{$userID}', '{page}', '{$sectionID}', '{$postID}');"
            //urlHandlerBuild(['view' => 'awards'])
            );

            if ($paginationMenu) {
                $paginationMenu = eval(getTemplate($templatePrefix . 'Pagination'));
            }
        }

        $queryOptions['limit'] = $maximumAwardsToDisplay;

        $queryOptions['limit_start'] = $startPage;

        $grantsGroupCache = [];

        if ($groupAwardGrants) {
            $grantsGroupCache = grantCount(
                $sectionData['whereClauses'],
                ['group_by' => 'aid'],
                ['aid']
            );
        }

        if ($groupAwardGrants) {
            $grantCacheData = awardGetUser(
                $sectionData['whereClauses'],
                $sectionData['queryFields'],
                array_merge($queryOptions, ['group_by' => 'aid'])
            );
        } else {
            $grantCacheData = awardGetUser(
                $sectionData['whereClauses'],
                $sectionData['queryFields'],
                $queryOptions
            );
        }

        foreach ($grantCacheData as $v) {
            if (!is_array($v)) {
                $grantCacheData = [$grantCacheData];

                break;
            }
        }

        if (!$totalGrantedCount) {
            if ($maximumAwardsToDisplay) {
                $grantedList = eval(getTemplate($templatePrefix . 'Empty'));
            }
        } elseif ($maximumAwardsToDisplay) {
            parseUserAwards($grantedList, $grantCacheData, $templatePrefix . 'Row', $grantsGroupCache);
        }

        if ($maximumAwardsToDisplay) {
            $userName = htmlspecialchars_uni($userData['username']);

            if ($sectionID === AWARDS_SECTION_NONE) {
                $sectionTitle = $lang->sprintf(
                    $isProfilePage ? $lang->ougcAwardsProfileTitle : $lang->ougcAwardsPostTitle,
                    $userName
                );
            } else {
                $sectionTitle = $lang->sprintf(
                    $isProfilePage ? $lang->ougcAwardsProfileTitleSection : $lang->ougcAwardsPostTitleSection,
                    $userName,
                    $sectionName
                );
            }

            $sectionContents = eval(getTemplate($templatePrefix . 'Content'));

            $sectionData['sectionVariable'] = eval(getTemplate($templatePrefix));

            if (isset($sectionData['sectionVariableCache'])) {
                $sectionData['sectionVariableCache'] = $sectionData['sectionVariable'];
            }

            if ($isAjaxCall && $currentSectionID === $sectionID) {
                break;
            }
        }
    }

    global $templates;

    foreach ($awardsCategoriesCache as $categoryID => $categoryData) {
        if ($isProfilePage) {
            if (my_strpos(
                    $templates->cache['member_profile'],
                    '{$memprofile[\'ougcAwardsSection' . $categoryID . '\']}'
                ) === false) {
                $userData['ougc_awards'] .= $userData["ougcAwardsSection{$categoryID}"];
            }
        } elseif (isset($templates->cache[$mybb->settings['postlayout']]) &&
            my_strpos(
                $templates->cache[$mybb->settings['postlayout'] === 'classic' ? 'postbit_classic' : 'postbit'],
                '{$post[\'ougcAwardsSection' . $categoryID . '\']}'
            ) === false) {
            $userData['ougc_awards'] .= $userData["ougcAwardsSection{$categoryID}"];
        }
    }

    if ($presetList) {
        $alternativeBackground = alt_trow(true);

        $presetName = $presetData['name'] = htmlspecialchars_uni($presetData['name'] ?? '');

        $userData['ougc_awards_preset'] = eval(getTemplate($templatePrefix . 'Presets'));
    }

    if ($isAjaxCall && isset($sectionContents)) {
        if (!empty($lang->settings['charset'])) {
            $charset = $lang->settings['charset'];
        } else {
            $charset = 'UTF-8';
        }

        header("Content-type: application/json; charset={$charset}");

        echo json_encode(['content' => $sectionContents]);

        exit;
    }

    return $userData;
}

function stats_end(): bool
{
    global $db, $lang, $ougc_awards_most, $ougcAwardsStatsLast, $theme;

    $ougc_awards_most = $ougcAwardsStatsLast = $userList = '';

    if (!getSetting('statsEnabled')) {
        return false;
    }

    loadLanguage();

    $statsCache = awardsCacheGet();

    if (empty($statsCache['top'])) {
        $userList = eval(getTemplate('stats_empty'));
    } else {
        $usersCache = [];

        $query = $db->simple_select(
            'users',
            'uid, username, usergroup, displaygroup',
            "uid IN ('" . implode("','", array_keys($statsCache['top'])) . "')"
        );

        while ($userData = $db->fetch_array($query)) {
            $usersCache[(int)$userData['uid']] = $userData;
        }

        $alternativeBackground = alt_trow(true);

        $statOrder = 0;

        foreach ($statsCache['top'] as $userID => $total) {
            ++$statOrder;

            $usernameFormatted = format_name(
                htmlspecialchars_uni($usersCache[$userID]['username']),
                $usersCache[$userID]['usergroup'],
                $usersCache[$userID]['displaygroup']
            );

            $profileLink = build_profile_link($usersCache[$userID]['username'], $userID);

            $profileLinkFormatted = build_profile_link(
                $usernameFormatted,
                $userID
            );

            $message = $total;

            $grantDate = '';

            $userList .= eval(getTemplate('statsUserRow'));

            $alternativeBackground = alt_trow();
        }
    }

    $title = $lang->ougcAwardsStatsMostTitle;

    $ougc_awards_most = eval(getTemplate('stats'));

    $userList = '';

    if (empty($statsCache['last'])) {
        $userList = eval(getTemplate('stats_empty'));
    } else {
        $usersCache = [];

        $query = $db->simple_select(
            'users',
            'uid, username, usergroup, displaygroup',
            "uid IN ('" . implode("','", array_values($statsCache['last'])) . "')"
        );

        while ($userData = $db->fetch_array($query)) {
            $usersCache[(int)$userData['uid']] = $userData;
        }

        $alternativeBackground = alt_trow(true);

        $statOrder = 0;

        foreach ($statsCache['last'] as $grantDate => $userID) {
            ++$statOrder;

            $usernameFormatted = format_name(
                htmlspecialchars_uni($usersCache[$userID]['username']),
                $usersCache[$userID]['usergroup'],
                $usersCache[$userID]['displaygroup']
            );

            $profileLink = build_profile_link($usersCache[$userID]['username'], $userID);

            $profileLinkFormatted = build_profile_link(
                $usernameFormatted,
                $userID
            );

            $grantDate = my_date('relative', $grantDate);

            $userList .= eval(getTemplate('statsUserRow'));

            $alternativeBackground = alt_trow();
        }
    }

    $title = $lang->ougc_awards_stats_last;

    $ougcAwardsStatsLast = eval(getTemplate('stats'));

    return true;
}

function myalerts_register_client_alert_formatters(): bool
{
    if (
        class_exists('MybbStuff_MyAlerts_Formatter_AbstractFormatter') &&
        class_exists('MybbStuff_MyAlerts_AlertFormatterManager') &&
        class_exists('ougc\Awards\Core\MyAlertsFormatter')
    ) {
        global $mybb, $lang;

        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        if ($formatterManager) {
            $formatterManager->registerFormatter(new MyAlertsFormatter($mybb, $lang, 'ougc_awards'));
        }
    }

    return true;
}
/*
 * lets remove this feature for now
function myalerts_alerts_output_end(array &$hookArguments): array
{
    if ($hookArguments['outputAlert']['alert_code'] !== 'ougc_awards') {
        return $hookArguments;
    }

    $Details = $hookArguments['alertToParse']->toArray();

    $awardImage = awardGetIcon((int)$Details['object_id']);

    if (my_validate_url($awardImage)) {
        $hookArguments['outputAlert']['avatar']['image'] = $awardImage;
    }

    return $hookArguments;
}
*/