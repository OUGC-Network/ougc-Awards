<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/plugins/dvz_stream/ougcAwards.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012-2020 Omar Gonzalez
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

use dvzStream\Stream;
use dvzStream\StreamEvent;

use function dvzStream\addStream;
use function ougc\Awards\Core\awardGetIcon;
use function ougc\Awards\Core\awardsCacheGet;
use function ougc\Awards\Core\grantGet;
use function ougc\Awards\Core\getTemplate;
use function ougc\Awards\Core\loadLanguage;

use function ougc\Awards\Core\urlHandlerBuild;

use const ougc\Awards\Core\AWARD_TEMPLATE_TYPE_CLASS;

global $lang;

$stream = new Stream();

$stream->setName(explode('.', basename(__FILE__))[0]);

loadLanguage();

$stream->setTitle($lang->ougcAwardsDvzStream);

$stream->setEventTitle($lang->ougcAwardsDvzStreamEvent);

$stream->setFetchHandler(function (int $query_limit, int $lastGrantID = 0) use ($stream) {
    global $db, $cache;

    $whereClauses = ["gid>'{$lastGrantID}'", "visible='1'"];

    $awardsCache = awardsCacheGet()['awards'] ?? [];

    $awardsIDs = implode("','", array_keys($awardsCache));

    $whereClauses[] = "aid IN ('{$awardsIDs}')";

    $grantedAwards = grantGet(
        $whereClauses,
        ['uid AS userID', 'aid AS awardID', 'thread AS threadID', 'date AS grantStamp'],
        [
            'limit' => $query_limit
        ]
    );

    $usersCache = [];

    $userIDs = implode("','", array_map('intval', array_column($grantedAwards, 'userID')));

    $query = $db->simple_select(
        'users',
        'uid AS userID, username AS userName, usergroup AS userGroup, displaygroup AS displayGroup, avatar AS userAvatar',
        "uid IN ('{$userIDs}')"
    );

    while ($user_data = $db->fetch_array($query)) {
        $usersCache[(int)$user_data['userID']] = $user_data;
    }

    $streamEvents = [];

    foreach ($grantedAwards as $grantID => $grantData) {
        $awardID = (int)$grantData['awardID'];

        $awardData = $awardsCache[$awardID] ?? [];

        if (!$awardData) {
            continue;
        }

        $streamEvent = new StreamEvent();

        $streamEvent->setStream($stream);

        $streamEvent->setId($grantID);

        $streamEvent->setDate($grantData['grantStamp']);

        $streamEvent->setUser([
            'id' => $grantData['userID'],
            'username' => $usersCache[$grantData['userID']]['userName'],
            'usergroup' => $usersCache[$grantData['userID']]['userGroup'],
            'displaygroup' => $usersCache[$grantData['userID']]['displayGroup'],
            'avatar' => $usersCache[$grantData['userID']]['userAvatar'],
        ]);

        $streamEvent->addData([
            'awardID' => $awardID,
            'awardName' => $awardData['name'],
            'awardTemplate' => (int)$awardData['template'],
            'awardImage' => $awardData['image'],
            'templateType' => (int)$awardData['type'],
        ]);

        $streamEvents[] = $streamEvent;
    }

    return $streamEvents;
});

$stream->addProcessHandler(function (StreamEvent $streamEvent) {
    global $mybb, $lang;

    $streamData = $streamEvent->getData();

    $awardName = htmlspecialchars_uni($streamData['awardName']);

    $awardImage = $awardClass = awardGetIcon($streamData['awardID']);

    $awardImage = eval(
    getTemplate(
        $streamData['awardTemplate'] === AWARD_TEMPLATE_TYPE_CLASS ? 'awardImageClass' : 'awardImage'
    )
    );

    $awardUrl = urlHandlerBuild(['action' => 'viewUsers', 'awardID' => $streamData['awardID']]);

    $awardImage = eval(getTemplate('awardWrapper', false));

    $userData = $streamEvent->getUser();

    $userID = (int)$userData['id'];

    $streamText = $lang->sprintf($lang->ougcAwardsDvzStreamTextUser, $awardName);

    $streamItem = eval(getTemplate('streamItem'));

    $streamEvent->setItem($streamItem);
});

addStream($stream);
