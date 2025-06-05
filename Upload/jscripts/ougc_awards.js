/***************************************************************************
 *
 *    ougc Awards plugin (/jscripts/ougc_awards.js)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012-2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Adds a powerful awards system to you community.
 *
 ***************************************************************************

 ****************************************************************************
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

let ougcAwards = {
    RequestAward: function (awardID) {
        var postData = 'action=requestAward&modal=1&awardID=' + parseInt(awardID);

        MyBB.popupWindow('/awards.php?' + postData);
    },

    ViewAwards: function (userID, currentPage, SectionID, postID = 0) {
        var postData = 'viewAwards=1&uid=' + parseInt(userID) + '&sectionID=' + parseInt(SectionID) + '&page' + parseInt(SectionID) + '=' + parseInt(currentPage) + '&pid=' + parseInt(postID);

        if(parseInt(postID) === 0) {
            postData = postData + '&action=profile';
        }

        $.ajax(
            {
                type: 'post',
                dataType: 'json',
                url: parseInt(postID) !== 0 ? 'showthread.php' : 'member.php',
                data: postData,
                success: function (request) {
                    if (typeof request.content === 'string') {

                        document.getElementById(
                            'ougcAwardsProfileTable' + parseInt(userID) + '_' + parseInt(SectionID) + '_' + parseInt(postID)
                        ).innerHTML = request.content;
                    }
                },
                error: function (xhr) {
                    //location.reload(true);
                }
            });
    },

    DoRequestAward: function (awardID) {
        var postData = $('.requestForm' + parseInt(awardID)).serialize();

        $.ajax(
            {
                type: 'post',
                dataType: 'json',
                url: 'awards.php',
                data: postData,
                success: function (request) {
                    if (request.error) {
                        alert(request.error);
                    } else {
                        $(request.modal).appendTo('body').modal({fadeDuration: 250}).fadeIn('slow');
                    }
                },
                error: function (xhr) {
                    location.reload(true);
                }
            });

        return false;
    },

    ViewAll: function (userID, currentPage = 1, sectionID = 0) {
        var postData = 'action=viewUser&modal=1&userID=' + parseInt(userID) + '&sectionID=' + parseInt(sectionID) + '&page=' + parseInt(currentPage);

        MyBB.popupWindow('/awards.php?' + postData);
    }
};