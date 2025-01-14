var remainTimeInterval;
$(document).ready(function () {

    $("<div id='invitee-popup-upcoming-container' class='draggable'></div>").appendTo($("body"));

    displayRoomLinkPopup();

    const popupElement = document.getElementById('invitee-popup-upcoming-container');
    let offsetX, offsetY;

    // Event handler for starting drag
    function handleMouseDown(event) {
        popupElement.style.cursor = 'grabbing'; // Change cursor style on mousedown

        offsetX = event.clientX - popupElement.getBoundingClientRect().left;
        offsetY = event.clientY - popupElement.getBoundingClientRect().top;

        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
    }

    // Event handler for moving the draggable div
    function handleMouseMove(event) {
        popupElement.style.left = (event.clientX - offsetX) + 'px';
        popupElement.style.top = (event.clientY - offsetY) + 'px';
    }

    // Event handler for ending drag
    function handleMouseUp(event) {
        popupElement.style.cursor = 'grab'; // Reset cursor style on mouseup

        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
    }

    // Attach event listener for mousedown
    popupElement.addEventListener('mousedown', handleMouseDown);

    // Prevent default drag-and-drop behavior
    document.addEventListener('dragover', event => event.preventDefault());
    document.addEventListener('drop', event => event.preventDefault());

    $(document).on('click', '.popup-upcoming-close', function () {
        $("#invitee-popup-upcoming-container").css("visibility", "hidden");
    });

    remainTimeInterval = setInterval(refreshRemainTimeSpans, 1000);

    $(document).on('click', '.room-join-decline', function () {
        console.log("asdf");
        var partyhou_id = $(this).data('id');
        var request_data = {
            user_id: user_id,
            partyhou_id: partyhou_id,
        }

        $.ajax({
            url: "https://www.whisprrz.com/invited_partyhou_decline.php",
            dataType: 'json',
            data: request_data,
            type: 'post',
            success: function () {
                displayRoomLinkPopup();
            },
            error: function () {
                console.log("error");
            }
        });
    });

});

function refreshRemainTimeSpans() {
    $(".room-count-down").each(function() {
        const $span = $(this);
        const countdownRemainTimeText = countdownRemainTime($span);
        $span.text(countdownRemainTimeText);
    });
}

function countdownRemainTime($element) {
    const formattedDifference = $element.text();
    const timeParts = formattedDifference.split(":");
    let mins = parseInt(timeParts[0]);
    let seconds = parseInt(timeParts[1]);
    seconds -= 1;
    if (seconds < 0) {
        mins -= 1;
        seconds += 60;
    }

    if (seconds == 0 && mins == 0) {
        clearInterval(remainTimeInterval);
        $element.siblings(".join-link").css("visibility", "visible");
    }

    const formattedMins = mins.toString().padStart(2, '0');
    const formattedSeconds = seconds.toString().padStart(2, '0');
    return formattedMins + ":" + formattedSeconds;
}


function displayRoomLinkPopup() {
    if (!xajax_login_status) {
        return;
    };

    var request_data = {
        user_id: user_id
    };

    $("#invitee-popup-upcoming-container").empty();

    $.ajax({
        url: "https://www.whisprrz.com/invited_partyhou_list_upcoming.php",
        dataType: 'json',
        data: request_data,
        type: 'post',
        success: function (response) {
            if (response.partyhous.length !== 0) {
                const popup_container = `
                    <div class='invitee-popup-header'>
                        <div class='draggable'>Upcoming Partyhouz</div>
                        <img class="popup-upcoming-close" src="${url_tmpl_images}im/btn_close.png" alt="close" />
                    </div>

                    <div id='invitee-popup-upcoming-body'>
                    
                    </div>
                `
                $(popup_container).appendTo($("#invitee-popup-upcoming-container"));
                response.partyhous.forEach(partyhou => {
                    const template = `
                        <div class="partyhou_item">
                            <a href="${url_main}partyhouz_partyhou_show.php?partyhou_id=${partyhou.partyhou_id}"><h1>${partyhou.partyhou_title}</h1></a>
                            <div class="partyhou_item_content">
                                <div class="partyhou_item_left_content">
                                    <div class="image_container">
                                        <a href="${url_main}partyhouz_partyhou_show.php?partyhou_id=${partyhou.partyhou_id}"><img src="${partyhou.image_thumbnail}" alt="${partyhou.partyhou_title_full}"/></a>
                                    </div>
                                </div>
                                <div class="partyhou_item_right_content">
                                    <div class="partyhou_info">
                                        <div class="partyhou_info_left">
                                            <div class="partyhou_info_item">
                                                <strong>${l_party_host}:</strong>
                                                <a href="${url_main}search_results.php?display=profile&uid=${partyhou.user_id}">${partyhou.user_name}</a>
                                            </div>
                                            <div class="partyhou_info_item">
                                                <strong>${l_partyhouz_category}:</strong>
                                                <a href="${url_main}partyhouz_search.php?category_id=${partyhou.category_id}">${partyhou.category_title}</a>
                                            </div>
                                            <div class="partyhou_info_item">
                                                <strong>${l_invited}:</strong>
                                                <span>${partyhou.guest_invited_count}</span>
                                            </div>
                                            <div class="partyhou_info_item">
                                                <strong>${l_partyhouz_guests}:</strong>
                                                <a href="${url_main}partyhouz_partyhou_show.php?partyhou_id=${partyhou.partyhou_id}"><span>${partyhou.partyhou_n_guests}</span></a>
                                            </div>
                                            <div class="partyhou_info_item">
                                                <strong>${l_partyhouz_comments}:</strong>
                                                <span>${partyhou.partyhou_n_comments}</span>
                                            </div>
                                            <div class="partyhou_info_item">
                                                <span><a class="txt_upper_header_color" href="${url_main}partyhouz_search.php?partyhou_datetime=${partyhou.partyhou_datetime_raw}">${partyhou.partyhou_date}</a> | ${partyhou.partyhou_time}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; flex-direction:column; gap: 10px;">
                                <div>
                                    <span>${l_wait_to}</span>
                                </div>
                                <div style="display:flex; justify-content:space-around; gap: 5px; align-items:center;">
                                    <span class="room-count-down" style="color:red">${partyhou.remain_time}</span>
                                    <a class="join-link" style="visibility:hidden" target="_blank" href="${url_main}partyhouz_partyhou_room.php?partyhou_id=${partyhou.partyhou_id}" class="partyhou_join">JOIN</a>
                                    <button class="room-join-decline" data-id="${partyhou.partyhou_id}" style="background-color: red; color: white; padding: 3px 5px; border:none;">Decline</button>
                                </div>
                            </div>
                        </div>
                    `;
                    $(template).appendTo($("#invitee-popup-upcoming-body"));
                });
            } else {
                $("#invitee-popup-upcoming-body").empty();
            }
        },
        error: function () {
            console.log("error");
        }
    });
}