<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/languages/english/admin/user_ougc_awards.lang.php)
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

$l = [
    'ougcAwards' => 'ougc Awards',
    'ougcAwardsDescription' => 'Manage a powerful awards system for your community.',

    'setting_group_ougc_awards' => 'Awards',
    'setting_group_ougc_awards_desc' => 'Manage a powerful awards system for your community.',

    'ougc_awards_import_title' => 'Import Awards',
    'ougcAwardsImportDescription' => '<br />&nbsp;&nbsp;&nbsp;<a href="./index.php?module=config-plugins&amp;ougc_awards_import=myawards">Import From MyAwards</a>
<br />&nbsp;&nbsp;&nbsp;<a href="./index.php?module=config-plugins&amp;ougc_awards_import=nickawards">Import From Nickman\'s Awards System</a>',
    'ougc_awards_import_confirm_myawards' => 'Are you sure you want to import awards from MyAwards?',
    'ougc_awards_import_confirm_nickawards' => 'Are you sure you want to import awards from Nickman\'s award system?',
    'ougc_awards_import_end' => 'Awards Imported Successfully.',
    'ougc_awards_import_error' => 'There was an error trying to import the selected awards. The "{1}" table doesn\'t seems to exists.',

    'setting_ougc_awards_showInPosts' => 'Maximum Awards in Posts',
    'setting_ougc_awards_showInPosts_desc' => 'Maximum number of awards to be shown in posts.',
    'setting_ougc_awards_groupAwardGrantsInPosts' => 'Group Awards in Posts',
    'setting_ougc_awards_groupAwardGrantsInPosts_desc' => 'If you enable this, awards will be shown once per user only.',
    'setting_ougc_awards_showInPostsPresets' => 'Maximum Presets in Posts',
    'setting_ougc_awards_showInPostsPresets_desc' => 'Type the maximum preset awards to display in posts.',
    'setting_ougc_awards_showInProfile' => 'Maximum Awards in Profile',
    'setting_ougc_awards_showInProfile_desc' => 'Maximum number of awards to be shown in profiles.',
    'setting_ougc_awards_groupAwardGrantsInProfiles' => 'Group Awards in Profiles',
    'setting_ougc_awards_groupAwardGrantsInProfiles_desc' => 'If you enable this, awards will be shown once per user only.',
    'setting_ougc_awards_showInProfilePresets' => 'Maximum Preset Awards in Profiles',
    'setting_ougc_awards_showInProfilePresets_desc' => 'Type the maximum preset awards to display in profiles.',
    'setting_ougc_awards_perPage' => 'Items Per Page',
    'setting_ougc_awards_perPage_desc' => 'Maximum number of items to show per page in the View Category Owners, View Award Owners, View Requests, and Task Logs pages.',
    'setting_ougc_awards_perPageMyAwards' => 'Grants Per Page in My Awards',
    'setting_ougc_awards_perPageMyAwards_desc' => 'Maximum number of award grants to show per page in the My Awards page.',
    'setting_ougc_awards_perPageViewUsers' => 'Users Per Page in Granted Users',
    'setting_ougc_awards_perPageViewUsers_desc' => 'Maximum number of user grants to show per page in the Granted Users page.',
    'setting_ougc_awards_perPageViewUserModal' => 'Grants Per Page in User Modal',
    'setting_ougc_awards_perPageViewUserModal_desc' => 'Maximum number of award grants to show per page in the user modal.',
    'setting_ougc_awards_groupsView' => 'View Groups',
    'setting_ougc_awards_groupsView_desc' => 'Allowed groups to view the awards page.',
    'setting_ougc_awards_groupsPresets' => 'Presets Allowed Groups',
    'setting_ougc_awards_groupsPresets_desc' => 'Select which groups are allowed to use and create presets.',
    'setting_ougc_awards_presetsMaximum' => 'Maximum Presets',
    'setting_ougc_awards_presetsMaximum_desc' => 'Select the maximum amount of presets can create.',
    'setting_ougc_awards_groupsModerators' => 'Moderator Groups',
    'setting_ougc_awards_groupsModerators_desc' => 'Allowed groups to manage awards.',
    'setting_ougc_awards_statsEnabled' => 'Enable Stats',
    'setting_ougc_awards_statsEnabled_desc' => 'Do you want to enable the top and last granted users in the stats page?',
    'setting_ougc_awards_statsLatestGrants' => 'latest Grants in Stats',
    'setting_ougc_awards_statsLatestGrants_desc' => 'Type the maximum number of latest grants to show in the stats page.',
    'setting_ougc_awards_notificationPrivateMessage' => 'Send PM',
    'setting_ougc_awards_notificationPrivateMessage_desc' => 'Do you want to send an PM to users when receiving an award?',
    'setting_ougc_awards_grantDefaultVisibleStatus' => 'Grant Default Visible Status',
    'setting_ougc_awards_grantDefaultVisibleStatus_desc' => 'Select the visible status of awards when granting awards to users. If set to <code>No</code>, users will need to set their awards as visible in the sorting page from the My Awards page.',
    'setting_ougc_awards_uploadPath' => 'Uploads Path',
    'setting_ougc_awards_uploadPath_desc' => 'Type the path where the awards images will be uploaded.',
    'setting_ougc_awards_uploadDimensions' => 'Uploads Dimensions',
    'setting_ougc_awards_uploadDimensions_desc' => 'Type the maximum dimensions for the awards images. Default <code>32|32</code>.',
    'setting_ougc_awards_uploadSize' => 'Uploads Size',
    'setting_ougc_awards_uploadSize_desc' => 'Type the maximum size in bytes for the awards images. Default <code>50</code>.',
    'setting_ougc_awards_privateMessageSenderUserID' => 'Private Message Sender User ID',
    'setting_ougc_awards_privateMessageSenderUserID_desc' => 'Select the user identifier (UID) that will send the private messages to users when receiving an award. Default is <code>0</code>.',
    'setting_ougc_awards_groupsTasks' => 'Tasks View Groups',
    'setting_ougc_awards_groupsTasks_desc' => 'Allowed groups to view the awards tasks page.',
    'setting_ougc_awards_groupsMyAwards' => 'My Awards: View Groups',
    'setting_ougc_awards_groupsMyAwards_desc' => 'Allowed groups to view the My Awards page.',
    'setting_ougc_awards_groupsMyAwardsDisplayOrder' => 'My Awards: Display Order Permission',
    'setting_ougc_awards_groupsMyAwardsDisplayOrder_desc' => 'Allowed groups to modify the display order of their award grants.',
    'setting_ougc_awards_enableDvzStream' => 'Enable DVZ Stream Integration',
    'setting_ougc_awards_enableDvzStream_desc' => 'Enable DVZ Stream integration for granted awards.',

    'ougcAwardsRecountRebuildGrantsDisplayOrder' => 'Awards: Rebuild Grants Display Order',
    'ougcAwardsRecountRebuildGrantsDisplayOrderDescription' => 'When this is run, the display order of user grants will be rebuilt to match the award display order.',

    'ougcAwardsRecountRebuildGrantsDisplayOrderSuccess' => 'The awards grants rebuilt was successful.',

    'ougcAwardsTaskRan' => 'The awards task ran successfully.',

    'ougcAwardsPluginLibrary' => 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.'
];
