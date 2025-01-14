$(document).ready(function () {

    $("<div id='invitee-popup-container' class='draggable'></div>").appendTo($("body"));
    $("#invitee-popup-container").css("visibility", "hidden");

    displayPopup();

    const popupElement = document.getElementById('invitee-popup-container');
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

    $(document).on('click', '.partyhou_accept', function () {
        var partyhou_id = $(this).data('id');
        var request_data = {
            user_id: user_id,
            partyhou_id: partyhou_id,
            accepted: 1
        }

        $.ajax({
            url: urlSiteSubfolder  + "invited_partyhou_accept.php",
            dataType: 'json',
            data: request_data,
            type: 'post',
            success: function () {
                displayPopup();
            },
            error: function () {
                console.log("error");
            }
        });
    });

    $(document).on('click', '.partyhou_decline', function () {
        var partyhou_id = $(this).data('id');
        var request_data = {
            user_id: user_id,
            partyhou_id: partyhou_id,
            accepted: 2
        }

        $.ajax({
            url: urlSiteSubfolder  + "invited_partyhou_accept.php",
            dataType: 'json',
            data: request_data,
            type: 'post',
            success: function () {
                displayPopup();
            },
            error: function () {
                console.log("error");
            }
        });
    });

    $(document).on('click', '.popup-close', function () {
        $("#invitee-popup-container").css("visibility", "hidden");
    });

});


function displayPopup() {
    if (!xajax_login_status) {
        return;
    };

    var request_data = {
        user_id: user_id
    };

    $("#invitee-popup-container").empty();

    $.ajax({
        url: urlSiteSubfolder + "invited_partyhou_list.php",
        dataType: 'json',
        data: request_data,
        type: 'post',
        success: function (response) {
            if (response.partyhous.length !== 0) {
                const popup_container = `
                    <div class='invitee-popup-header'>
                        <div class='draggable'>Invited Partyhouz</div>
                        <img class="popup-close" src="${url_tmpl_images}im/btn_close.png" alt="close" />
                    </div>

                    <div id='invitee-popup-body'>

                    </div>
                `
                $("#invitee-popup-container").css("visibility", "visible");
                $(popup_container).appendTo($("#invitee-popup-container"));
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
                                    <div class="partyhou_action">
                                        <button class="partyhou_accept" data-id="${partyhou.partyhou_id}">${l_accept}</button>
                                        <button class="partyhou_decline" data-id="${partyhou.partyhou_id}">${l_decline}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $(template).appendTo($("#invitee-popup-body"));
                });
            } else {
                $("#invitee-popup-body").empty();
            }
        },
        error: function () {
            console.log("error");
        }
    });
}