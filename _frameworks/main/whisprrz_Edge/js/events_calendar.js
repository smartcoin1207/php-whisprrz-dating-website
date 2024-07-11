var CEventsCalendar = function (guid, uid) {

    var $this = this;

    this.guid = guid * 1;
    this.uid = uid * 1;

    this.setData = function (data) {
        for (var key in data) {
            $this[key] = data[key];
        }
    }

    this.openCreateForm = function (show, call) {

        var $content = $jq('.task_create_frm', '#pp_task_create');
        $jq('#bl_events_new_frm_title').html(l('create_new_task'));

        $jq('#pp_task_create').find('.task_create_frm').append(createLoader('contact_frm_loader')).end()
            .one('shown.bs.modal', function () {
                $.post('events_event_task_edit.php?upload_page_content_ajax=1', {}, function (res) {
                    var data = checkDataAjax(res);
                    if (data) {
                        var h = $content.html(data).find('.bl_modal_body').height();
                        setTimeout(function () {
                            $content.oneTransEnd(function () {
                                $content.css({ height: 'auto', overflow: 'visible' });
                            }).height(h);
                            $content.find('.bl_modal_body').addClass('to_show');
                            var fnSuccess = function () {
                                $jq('#pp_task_create').one('hidden.bs.modal', function () {
                                    //setTimeout(function(){alertCustom(l('message_sent'),l('success'))},0)
                                }).modal('hide')
                            }
                            //initContactUs($jq('#pp_contact'),fnSuccess,showError,hideError);
                        }, 0)
                    } else {
                        $jq('#pp_task_create').one('hidden.bs.modal', alertServerError).modal('hide');
                    }
                })
            }).on('hidden.bs.modal', function () {
                $content.empty().removeAttr('style');
            }).modal('show');

        return;
        show = show || 'show';
        if (show == 'show') {
            //setPushStateHistory('upload_file');
        }
        $jq('#pp_task_create')
            .one('hide.bs.modal', function () {
                $jq('body').removeClass('pp_task_create');
            }).one('hidden.bs.modal', function () {
                checkOpenModal();
                if (typeof call == 'function') call();
            }).one('show.bs.modal', function () {
                $jq('body').addClass('pp_task_create');
            }).modal(show);
    }

    this.initDatePiker = false;
    this.initEditDatePiker = function () {
        $this.initDatePiker = true;
        var tooltips = {
            today: l('today'),
            selectMonth: l('select_month'),
            prevMonth: l('previous_month'),
            nextMonth: l('next_month'),
            selectYear: l('select_year'),
            prevYear: l('previous_year'),
            nextYear: l('next_year'),
            selectDecade: l('select_decade'),
            prevDecade: l('previous_decade'),
            nextDecade: l('next_decade'),

            clear: 'Clear selection',
            close: 'Close the picker',
            prevCentury: 'Previous Century',
            nextCentury: 'Next Century',
            pickHour: 'Pick Hour',
            incrementHour: 'Increment Hour',
            decrementHour: 'Decrement Hour',
            pickMinute: 'Pick Minute',
            incrementMinute: 'Increment Minute',
            decrementMinute: 'Decrement Minute',
            pickSecond: 'Pick Second',
            incrementSecond: 'Increment Second',
            decrementSecond: 'Decrement Second',
            togglePeriod: 'Toggle Period',
            selectTime: 'Select Time'
        };

        $this.$date = $('#event_date');
        $this.$date.datetimepicker({
            format: l('calendar_format'),
            minDate: '2019-01-01',
            tooltips: tooltips,
            animationShow: 'fadeIn',//slideDown
            animationHide: 'fadeOut',//slideUp
            animationTime: 250,
            showTodayButton: true
        });

        $this.todayDate = moment().format(l('calendar_format'));
        $this.tomorrowDate = moment().add(1, 'day').format(l('calendar_format'));
    }

    this.todayDate = false;
    this.tomorrowDate = false;
    this.initEditCurDate = function (val) {
        if (!$this.initDatePiker) {
            $this.initEditDatePiker();
        }
        val = val || trim($this.$date.val());
        if ($this.todayDate == val) {
            $this.$date.val(l('today').toLowerCase());
        } else if ($this.tomorrowDate == val) {
            $this.$date.val(l('tomorrow').toLowerCase());
        }
    }

    this.initEdit = function () {
        $(function () {
            $this.initEditItem()
        })
    }

    this.initEditDescription = function () {
        $this.$description = $jq('#event_description').autosize({ isSetScrollHeight: false, callback: function () { } });
    }

    this.initEditItem = function () {
        var isSubmit = false;

        $this.$blFrmAdd = $('#bl_events_new_frm');

        $this.eventId = $jq('#event_id').val();
        $this.eventPhotoId = $jq('#event_photo_id').val() * 1;
        $this.$upPhotoBl = $jq('#event_photo_upload_bl');
        $this.$upPhotoImg = $jq('#event_photo_upload_img');
        $this.$upPhotoBtn = $('#event_photo_upload');
        $this.$upPhotoBtnDelete = $('#event_photo_upload_delete');

        $this.$upPhotoBtnDelete.prop('disabled', !$this.eventPhotoId);

        $this.upPhotoImgId = 0;

        $this.$title = $jq('#event_title');
        $this.$description = $jq('#event_description')
            .on('change propertychange input', setDisabledSave);

        var $eventDescBl = $('#event_description_bl');
        $jq('#event_description_expand').click(function () {
            $eventDescBl.stop(true, true).slideToggle(250, function () {
                $jq('#event_description_expand').text($eventDescBl.is(':hidden') ? l('click_to_expand') : l('click_to_collapse'));
                $this.$description.focus();
            })
        })

        var durFrienBlock = 400, dur = 200;
        var fnToggleListFriends = function (show, hide) {
            if (isSubmit) return false;
            show = show || false;
            hide = hide || false;
            if ($this.$showListFriendBl.is(':hidden') && !hide) {
                $jq('#event_friends_show').addClass('open');
                $this.$showListFriendBl.stop().slideDown(durFrienBlock);
            } else if (!show) {
                $jq('#event_friends_show').removeClass('open');
                $this.$showListFriendBl.stop().slideUp(durFrienBlock);
            }
        }


        $jq('#event_friends_show').click(function () {
            fnToggleListFriends()
        })
        $jq('#event_to_user').click(function () {
            if (isSubmit) return false;
            fnToggleListFriends()
        })

        $this.$showListFriendBl = $jq('#event_friends_list');
        $this.$liListFriends = $this.$showListFriendBl.find('li');
        $this.$listSearchUsersBl = $jq('#event_search_users_bl');
        $this.$listSearchUsers = $jq('#event_search_users_list');

        var fnHideSearchList = function () { $this.$listSearchUsersBl.stop(true, true).slideUp(dur) },
            fnHideAllList = function () {
                fnToggleListFriends(false, true)
                fnHideSearchList();
            },
            setValUserTo = function () {
                if (isSubmit) return;
                var $el = $(this),
                    uid = $el.data('uid'),
                    name = $el.data('name');//?$el.data('name'):$el.attr('title');
                $this.eventUserTo.val(name).data('uid', uid);

                var $liFriend = $('#list_friend_event_' + uid);
                $this.$liListFriends.removeClass('selected');
                $liFriend[0] && $liFriend.addClass('selected');
                fnHideAllList();
                setDisabledSave();
            }

        $('a', $this.$showListFriendBl).click(setValUserTo);
        $jq('body').on('click', 'li.search_user_item', setValUserTo)
            .on('click', function (e) {
                var $targ = $(e.target);
                if (!$targ.is('.bl_event_to_list') && !$targ.closest('.bl_event_to_list')[0]
                    && !$targ.is('.bl_event_to_on') && !$targ.closest('.bl_event_to_on')[0]
                    && !$targ.is('#event_to_user')) {
                    fnToggleListFriends(false, true);
                }
                if (!$targ.is('.event_search_users_bl') && !$targ.closest('.event_search_users_bl')[0]) {
                    fnHideSearchList();
                }
            })

        $this.searchResponse = {};
        $this.keySearch = '';
        $this.eventUserTo = $jq('#event_to_user').on('keyup', function (e) {//change propertychange input
            $this.eventUserTo.data('uid', 0);
            var name = trim(this.value);
            if (name) {
                var k = name.replace(/[%#&\'"\/\\\\< ]/g, '_').toLowerCase(),
                    fnResponse = function (data, notSave) {
                        //console.log('KKKKK', $this.keySearch==k);
                        if (k == $this.keySearch) {
                            if (data !== false && data) {
                                $this.$listSearchUsers.html(data).closest('#event_search_users_bl')
                                    .stop(true, true).slideDown(dur)
                            } else {
                                fnHideSearchList();
                            }
                        }
                        if (data !== false && !(notSave || 0)) {
                            //console.log('UPDATE DATA', k);
                            $this.searchResponse[k] = data;
                        }
                    };
                $this.keySearch = k;
                if ($this.searchResponse[k] != undefined) {
                    //console.log('OLD DATA', k);
                    fnResponse($this.searchResponse[k], true);
                    return;
                }
                //console.log('AJX DATA', k);
                //$this.$listSearchUsers.empty();
                $.post('ajax.php?cmd=search_users_from_name', { name: name }, function (res) {
                    var data = checkDataAjax(res);
                    fnResponse(data);

                })
            } else {
                fnHideSearchList();
            }
        })

        $this.$btnAdd = $('#event_add');

        //https://eonasdan.github.io/bootstrap-datetimepicker/Events/#events

        var tooltips = {
            today: l('today'),
            selectMonth: l('select_month'),
            prevMonth: l('previous_month'),
            nextMonth: l('next_month'),
            selectYear: l('select_year'),
            prevYear: l('previous_year'),
            nextYear: l('next_year'),
            selectDecade: l('select_decade'),
            prevDecade: l('previous_decade'),
            nextDecade: l('next_decade'),

            clear: 'Clear selection',
            close: 'Close the picker',
            prevCentury: 'Previous Century',
            nextCentury: 'Next Century',
            pickHour: 'Pick Hour',
            incrementHour: 'Increment Hour',
            decrementHour: 'Decrement Hour',
            pickMinute: 'Pick Minute',
            incrementMinute: 'Increment Minute',
            decrementMinute: 'Decrement Minute',
            pickSecond: 'Pick Second',
            incrementSecond: 'Increment Second',
            decrementSecond: 'Decrement Second',
            togglePeriod: 'Toggle Period',
            selectTime: 'Select Time'
        };

        $this.$date = $('#event_date');
        $this.$date/*.datetimepicker({
            format: l('calendar_format'),
            minDate: '2019-01-01',
            tooltips: tooltips,
            animationShow: 'fadeIn',//slideDown
            animationHide: 'fadeOut',//slideUp
            animationTime: 250,
            showTodayButton: true
        })*/.on('dp.change', function (e) {
            var val = trim($this.$date.val());
            if (val) {
                var date = e.date.format('YYYY-MM-DD');
                $this.$date.data({ current: date, start: val })
                    .attr({ 'data-current': date, 'data-start': val });
            } else {
                val = $this.$date.data('start');
                $this.$date.val(val);
            }
            $this.initEditCurDate(val);
            setDisabledSave();
        }).on('dp.show', function (e) {
            var val = trim($this.$date.val());
            $this.initEditCurDate(val);
        })
        //$this.$date.val($this.$date.data('start'));

        /*$this.$time=$('#event_time').datetimepicker({
            format: 'HH:mm',
            stepping: 10,
            tooltips: tooltips,
            animationShow: 'slideDown',
            animationHide: 'slideUp',
            animationTime: 250
        }).on('dp.change', function(e){
            setDisabledSave();
        })*/

        $this.$time = $('#event_time').date_time_picker({
            datepicker: false,
            format: 'H:i',
            step: 30,
            sel: $('#event_time').closest('.field'),
            fnAnimateIn: 'fadeIn',
            fnAnimateOut: 'fadeOut',
            fnAnimateDelay: 250,
            onChangeDateTime: function () {
                setDisabledSave();
            }
        })

        $this.$access = $jq('#event_access');

        function setDisabledSave() {
            var disabled = false, isHideError = false;
            $jq('.field_required', $this.$blFrmAdd).each(function () {
                var $el = $(this),
                    val = trim(this.value);
                if ($el.is('.field_required')) {
                    var isError = !val, msgError = l('required_field');
                    if (isError) {
                        if (isSubmit) showError($(this), msgError, isHideError, isHideError);
                        isHideError = true;
                        disabled = true;
                    } else {
                        hideError($(this));
                    }
                }
            })

            disabled = disabled || $this.isProcessUpload;
            /*if (isSubmit) {
                //$this.$btnAdd.prop('disabled', disabled);
                $this.$btnAdd.removeClass('disabled');
            } else {
                $this.$btnAdd[disabled?'addClass':'removeClass']('disabled');
            }*/
            $this.$btnAdd.prop('disabled', disabled);

            return disabled;
        }

        $('.field_required', $this.$blFrmAdd).on('change propertychange input', setDisabledSave)
            .on('focus', function () { focusError($(this)) })
            .on('blur', function () { blurError($(this)) });

        $this.$btnAdd.click(function () {
            isSubmit = true;
            if (setDisabledSave()) return false;

            $this.$showListFriendBl.stop().slideUp(durFrienBlock);

            var fnDisabledControls = function (disabled) {
                $('input, textarea, select, button', $this.$blFrmAdd).prop('disabled', disabled);
                if (disabled) {
                    addChildrenLoader($this.$btnAdd);
                } else {
                    removeChildrenLoader($this.$btnAdd);
                }
            }

            fnDisabledControls(true);
            var userToName = trim($this.eventUserTo.val());
            if (userToName == l('myself')) {
                userToName = '';
            }

            var data = {
                ajax: 1,
                event_id: $this.eventId,
                event_photo_id: $this.eventPhotoId,
                event_user_to: $this.eventUserTo.data('uid'),
                event_user_to_name: userToName,
                event_private: $this.$access.val() * 1,
                event_title: trim($this.$title.val()),
                event_description: trim($this.$description.val()),
                event_date: $this.$date.data('current'),
                event_time: $this.$time.val(),
                event_photo_id: $this.upPhotoImgId
            };

            $.post('events_event_task_edit.php?cmd=save', data, function (res) {
                var data = checkDataAjax(res), resError = true;
                if (data) {
                    resError = false;
                    if (data.redirect) {

                        redirectUrl(data.redirect);
                    } else {
                        resError = true;
                    }
                }
                if (resError) {
                    alertServerError();
                    fnDisabledControls(false);
                }
            })
        })
        setDisabledSave();

    }

    this.isProcessUpload = false;
    this.clickUploadPhoto = function ($file) {
        $file.next('input[type=reset]').click();
    }

    this.changeUploadPhoto = function ($file) {
        $file.parent('form').find('input[type=submit]').click();
    }

    this.submitUploadPhoto = function ($frm) {
        $this.$btnAdd.prop('disabled', true);
        var file = $frm.find('input[type=file]'),
            fileName = file.attr('name'),
            url = url_ajax +
                '?cmd=upload_temp_photo_event',
            formData = new FormData(),
            error = '',
            acceptTypes = 'image/jpeg,image/png,image/gif';
        $.each(file[0].files, function (i, file) {
            var tpp = file.type;
            if (acceptTypes.indexOf(tpp) === -1) {
                error = l('accept_file_types');
                return false;
            } else if (file.size > (clProfilePhoto.maxphotoFileSize)) {
                error = clProfilePhoto.maxphotoFileSizeLimit;
                return false;
            }
            formData.append(fileName, file);
        });

        if (error) {
            alertCustom(error);
            return false;
        }

        $this.$upPhotoBtnDelete.prop('disabled', true);
        addChildrenLoader($this.$upPhotoBtn.prop('disabled', true));
        $this.isProcessUpload = true;

        var fnRes = function () {
            removeChildrenLoader($this.$upPhotoBtn.prop('disabled', false));
            $this.isProcessUpload = false;
        }

        //return false;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var data = xhr.responseText,
                        res = checkDataAjax(data);
                    if (typeof res == 'object') {

                        if (res.error) {
                            alertCustom(res.error);
                            fnRes();
                        } else {
                            var idPre = res.id
                            idPre = idPre.replace(/[^0-9]/g, '');

                            $this.upPhotoImgId = idPre;
                            var url = urlFiles + res.image;
                            img = new Image();
                            img.onload = function () {
                                $this.$upPhotoImg[0].src = url;
                                $this.$upPhotoBtnDelete.prop('disabled', false);
                                fnRes();
                                $this.$upPhotoBtn.find('.btn_title').text(l('use_another'));
                            }
                            img.src = url;
                        }
                    } else {
                        alertServerError(true);
                        fnRes();
                    }
                    $this.$btnAdd.prop('disabled', false);
                }
            }
        };
        xhr.send(formData);
        return false;
    }

    this.deleteUploadPhoto = function () {
        if ($this.isProcessUpload) return;

        $this.$upPhotoImg[0].src = url_tmpl_images + 'event_clock_b.png';
        $this.upPhotoImgId = 0;
        $this.$upPhotoBtn.find('.btn_title').text(l('choose_an_image'));
        $this.eventPhotoId = 0;
        $this.$upPhotoBtnDelete.prop('disabled', true);
    }

    this.signUpEvent = function (element, event_id, type = '') {
        var datac = { cmd: 'signup', event_id: event_id };
        if (type == 'event') {
            datac = {
                cmd: 'add',
                event_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else if (type == 'hotdate') {
            datac = {
                cmd: 'add',
                hotdate_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else if (type == 'partyhou') {
            datac = {
                cmd: 'add',
                partyhou_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else {
            datac = {
                cmd: 'add',
                event_id: event_id,
                guest_sign_action: '1',
                upload_page_content_ajax: '1',
            }
        }

        $.ajax({
            url: './signEhp.php',
            type: 'post',
            data: datac,
            success: function (res) {
                dataRes = JSON.parse(res);

                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);

                    $data.filter('.script_events_items').appendTo('#events_calendar_data');

                    var parentElement = $(element).parent(); // Get the parent element
                    $(element).text('Sign Out');  // Example: Change the text of the element
                    $(element).attr('onclick', 'clEventsCalendar.signOutEvent(this, ' + event_id + ', "' + type + '")'); // Update the onclick attribute

                    if (dataRes?.pending == true) {
                        parentElement.removeClass('pending_addition');
                    } else {
                        parentElement.removeClass('calendar_addition');
                    }
                }
            }
        });
    }

    this.signOutEvent = function (element, event_id, type = '') {
        var datac = { cmd: 'signout', event_id: event_id };
        if (type == 'event') {
            datac = {
                cmd: 'remove',
                event_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else if (type == 'hotdate') {
            datac = {
                cmd: 'remove',
                hotdate_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else if (type == 'partyhou') {
            datac = {
                cmd: 'remove',
                partyhou_id: event_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1',
                mainCalendar: '1'
            }
        } else {
            datac = {
                cmd: 'remove',
                event_id: event_id,
                guest_sign_action: '1',
                upload_page_content_ajax: '1',
            }
        }

        $.ajax({
            url: './signEhp.php',
            type: 'post',
            data: datac,
            success: function (res) {
                dataRes = JSON.parse(res);

                var data = checkDataAjax(res);
                
                if (data) {
                    var $data = $(data);

                    $data.filter('.script_events_items').appendTo('#events_calendar_data');
                    
                    var parentElement = $(element).parent(); // Get the parent element
                    parentElement.addClass('calendar_addition');
                    parentElement.addClass('pending_addition');
                    $(element).text('Sign Up');  // Example: Change the text of the element
                    $(element).attr('onclick', 'clEventsCalendar.signUpEvent(this, ' + event_id + ', "' + type + '")'); // Update the onclick attribute
                } 
            }
        });
    }

    this.getHtmlEventItem = function (eventId, eventDone, userName, userUrl, userPhoto, userOnline, title, description, time, eventsNum, urlCreateItem, uidEvent, uidToEvent, eventEditUrl, eventShowUrl = '', eventImage, event_category = '', event_additional_data = '') {
        eventsNum *= 1;
        urlCreateItem = urlCreateItem || '';
        title = title || '';
        uidEvent = uidEvent || 0;
        uidToEvent = uidToEvent || 0,
            eventEditUrl = eventEditUrl || '';
        eventDone *= 1;
        userOnline *= 1;
        var html = '';

        event_additional_data_obj = '';
        if (event_additional_data) {
            event_additional_data_obj = JSON.parse(event_additional_data);
        }

        console.log(event_additional_data_obj)

        if (urlCreateItem) {
            var clEmpty = '';
            if (!title) {
                title = l('no_task');
                clEmpty = 'to_hide';
            }
            html = '<span class="event_no_item ' + clEmpty + '">' + title + '</span>';
            if (eventsNum) {
                var numberMoreEvent = calendar_show_more_lang.replace('{number}', eventsNum);
                html += '<div class="event_num_bl">' +
                    '<span class="event_num" data-page="1" data-number="' + eventsNum + '" onclick="clEventsCalendar.loadMoreEventsDay($(this))">' +
                    '<span class="icon_fa"><i class="fa fa-angle-double-down" aria-hidden="true"></i></span>' +
                    '<span class="event_num_title">' + numberMoreEvent + '</span>' +
                    '</span>' +
                    '</div>';
            }
            if (urlCreateItem) {
                html += '<button onclick="redirectFormUrlLoader($(this),\'' + urlCreateItem + '\',\'show_back=1\')" class="btn btn-block btn_add_page btn-success">' +
                    '<span class="icon_fa" data-cl-loader="header_menu_loader_more">' +
                    '<i class="fa fa-plus"></i>' +
                    '</span>' +
                    '<span> ' + l('add_new_task') + '</span>' +
                    '</button>';
            }
            return html;
        }

        if (userPhoto || userName || time) {
            html += '<span class="event_info_bl">';
            if (userPhoto) {
                var online = userOnline ? '<div class="event_user_online"></div>' : '';
                html += '<div class="event_user_photo">' +
                    '<a href="' + userUrl + '" onclick="clEventsCalendar.goToProfileFromPhoto($(this)); return false;" ' +
                    ' class="pic" style="background-image:url(' + urlFiles + userPhoto + ');"></a>' +
                    online +
                    '</div>';
            }

            html += '<span class="event_info" style="width:80px; vertical-align: top;">';
            if (userName) {
                html += '<span class="event_user_name"><a href="' + userUrl + '">' + userName + '</a></span>';
            }
            if (time) {
                html += '<span class="event_time">' + time + '</span>';
            }

            var menuEventWrapper =
                '<span class="event_more_menu" data-toggle="collapse" data-target="#task_more_menu_' + eventId + '" aria-expanded="false">' +
                '<span class="icon_chevron_down">' +
                '<svg viewBox="0 0 48 48"><path d="M14.83 16.42l9.17 9.17 9.17-9.17 2.83 2.83-12 12-12-12z"/></svg>' +
                '</span>' +
                '<ul id="task_more_menu_' + eventId + '" class="more_menu_collapse more_menu_right1 collapse">' +
                '{menu_items}' +
                '</ul>' +
                '</span>';
            var menuEventDone =
                '<li>' +
                '<a href="#" data-done="' + eventDone + '" onclick="clEventsCalendar.doneTask($(this), \'' + eventId + '\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-check" aria-hidden="true"></i></span>' +
                '<span class="task_done_title">' + (eventDone ? l('reopen') : l('done')) + '</span>' +
                '</a>' +
                '</li>';
            var menuEventItems =
                '{menu_item_done}' +
                '<li>' +
                '<a href="#" onclick="redirectFormUrlLoader($(this),\'' + eventEditUrl + '\',\'show_back=1\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>' +
                '<span>' + l('edit') + '</span>' +
                '</a>' +
                '</li>' +
                '<li>' +
                '<a href="#" onclick="clEventsCalendar.deleteTask($(this),\'' + eventId + '\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-times" aria-hidden="true"></i></span>' +
                '<span>' + l('delete') + '</span>' +
                '</a>' +
                '</li>';

            if ($this.guid == uidEvent) {
                menuEventItems = menuEventItems.replace('{menu_item_done}', menuEventDone);
            } else if ($this.guid == uidToEvent) {
                menuEventItems = menuEventDone;
            } else {
                menuEventItems = '';
            }
            var menuEvent = '';
            if (menuEventItems) {
                menuEvent = menuEventWrapper.replace('{menu_items}', menuEventItems);
            }
            html += menuEvent + '</span>';

            html += '<div  style="display: flex; position: relative;">';
            html += '<a href="' + eventShowUrl + '">';
            html += '<img src="' + eventImage + '" class="event_user_photo" style="width:66px; height: 45px; display: block; float: left; margin-right: 10px; ">';

            if (event_additional_data_obj?.accepted == 0 && event_additional_data_obj?.is_member) {
                additionClassString = 'calendar_addition';
                pendingClass = '';
            }

            if (event_additional_data_obj?.accepted == 1) {
                html += '<div class="approved_mark" style="background-image:url(' + urlFiles + 'icons/approved.png);"></div>';
            }

            html += "</a>";

            var clDone = eventDone ? 'task_done' : '';

            html += '<div style="text-align: center;"><a href="' + eventShowUrl + '"><span class="event_title ' + clDone + '" style="color: white; font-size: 15px; text-decoration: underline;">' + title + '</span></a>';
            if (event_category) {
                html += '<span class="event_category" style="color: #bdc6d0; font-size: 12px;">' + event_category + '</span>';
            }

            if (event_additional_data_obj) {
                html += '<span class="event_category" style="color: #bdc6d0; font-size: 12px;">' + '<br> State: ' + event_additional_data_obj?.state_title + ', <br> City:' + event_additional_data_obj?.city_title + '</span>';
            }

            html += '</div>';

            if (event_additional_data_obj) {
                signUpHtml = '';
                if (!event_additional_data_obj?.is_own && !event_additional_data_obj?.is_finished && event_additional_data_obj.signin_available) {
                    signUpHtml = event_additional_data_obj?.is_member ? '<div class="calendar_button" onclick="clEventsCalendar.signOutEvent(this, ' + eventId + ', `' + event_additional_data_obj?.type + '`)">Sign Out</div>' : '<div class="calendar_button" onclick="clEventsCalendar.signUpEvent(this, ' + eventId + ', `' + event_additional_data_obj?.type + '`)">Sign Up</div>';
                }

                additionClassString = event_additional_data_obj?.is_member ? '' : 'calendar_addition';
                pendingClass = 'pending_addition';
                if (event_additional_data_obj?.accepted == 0 && event_additional_data_obj?.is_member) {
                    additionClassString = 'calendar_addition';
                    pendingClass = '';
                }

                html += '<div style="display:flex; flex-wrap: wrap; align-content: flex-start" class="' + additionClassString + " " + pendingClass + '">' + signUpHtml;
                html += '<div class="pending_hide" style="padding: 5px; background-color: #e1b113; font-size: 12px; line-height: 17px; border-radius: 5px; margin: auto; margin-left: 30px;">Pending...</div>'
                html += '<div class="dropdown btn_hide"> <button class="dropdown-toggle calendar_button" type="button" data-toggle="dropdown" style="height: auto; border: 0px;">Full Description<span class="caret"></span></button> ';
                html += '<ul class="dropdown-menu" style="top: auto; width: 250px;">';
                html += '<li><a>Place: ' + event_additional_data_obj?.place + ' </a></li>';
                html += '<li><div style="padding: 8px 17px 8px 15px; color: black;">Web Site: <a href="' + event_additional_data_obj?.site + '">Site</a> </div></li>';
                html += '<li><a>Phone: ' + event_additional_data_obj?.phone + ' </a></li>';

                html += '</ul></div>';

                html += '<a target="blank" href="' + event_additional_data_obj?.address + '"  class="calendar_button btn_hide">address</a>';

                if (event_additional_data_obj?.wall) {
                    html += '<a href="' + event_additional_data_obj?.wall + '" class="calendar_button btn_hide">' + event_additional_data_obj?.type + ' wall</a>';
                }
                html += '</div>';
            }

            html += '</div></span>';
        }

        if (description) {
            html += '<span class="event_description ' + clDone + '">' +
                '<span class="event_description_content">' + description + '</span>' +
                '<span class="event_description_shadow"></span>' +
                '</span>' +
                '<span class="event_description_more down" onclick="clEventsCalendar.showEventDayDesc($(this));">' +
                '<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>' +
                '<i class="fa fa-chevron-circle-up" aria-hidden="true"></i>' +
                '</span>';
        }

        return html;
    }

    this.eventsLoad = function (next, callBack) {
        next = next || false;
        var month = $this.selectMonth - 1, year = $this.selectYear;
        if (next) {
            month = $this.selectMonth + 1, year = $this.selectYear;
        }

        $('.event_loader').addChildrenLoader();

        var ajax_main = "";
        if (window.location.href.includes('event_calendar') || window.location.href.includes('events_calendar.php')) {
            ajax_main = "events_calendar_ajax.php";
        } else {
            ajax_main = "main_calendar_ajax.php";
        }

        $.post(urlMain + ajax_main + '?upload_page_content_ajax=1&calendar_month=' + month + '&calendar_year=' + year + '&uid=' + this.uid, {}, function (res) {
            var data = checkDataAjax(res);
            if (data) {
                var $script = $(data).filter('script');
                if ($script[0]) {
                    $jq('#events_calendar_data').append($script);
                    month = '' + month;
                    if (month.length == 1) month = '0' + month;
                    $this.replaceUrl(year + '-' + month + '-01');
                    if (typeof callBack == 'function') {
                        callBack();
                    }
                } else {
                    alertServerError(true)
                }
            } else {
                alertServerError(true)
            }
            $('.event_loader').removeChildrenLoader();
        })

        return false;
    }

    this.calendarSearch = function (payload, callBack) {
        $('.event_loader').addChildrenLoader();

        var ajax_main = "";
        if (window.location.href.includes('event_calendar') || window.location.href.includes('events_calendar.php')) {
            ajax_main = "events_calendar_ajax.php";
        } else {
            ajax_main = "main_calendar_ajax.php";
        }

        $.post(urlMain + ajax_main + '?upload_page_content_ajax=1&search=1&uid=' + this.uid, payload, function (res) {
            var data = checkDataAjax(res);
            if (data) {
                var $script = $(data).filter('script');
                if ($script[0]) {
                    $jq('#events_calendar_data').append($script);

                    $this.replaceUrl(payload.calendar_year + '-' + payload.calendar_month + '-01');
                    if (typeof callBack == 'function') {
                    }
                    callBack();
                } else {
                    alertServerError(true)
                }
            } else {
                alertServerError(true)
            }
            $('.event_loader').removeChildrenLoader();
        })
        return false;
    }

    this.replaceUrl = function (date) {
        var pageUrl = $this.urlPage;
        console.log(pageUrl)
        if (~pageUrl.indexOf('php')) {
            var d = ~pageUrl.indexOf('?') ? '&' : '?';
            pageUrl += d + 'date=' + date;
        } else {
            pageUrl += '/' + date;
        }
        replaceUrl(pageUrl);
    }

    this.checkActionData = {
        delete: {},
        done: {},
        load_more: {},
    }

    this.setActionEvent = function (eventId, action) {
        action = action || 'delete';
        $this.checkActionData[action][eventId] = {
            m: $this.selectMonth,
            y: $this.selectYear
        }
    }

    this.noActionEvent = function (eventId, action) {
        action = action || 'delete';
        if (!$this.checkActionData[action][eventId]) return true;
        var m = $this.checkActionData[action][eventId].m,
            y = $this.checkActionData[action][eventId].y;
        return m != $this.selectMonth || y != $this.selectYear;
    }

    this.eventActionUpdate = function (eventId, action, data) {
        eventsItems.forEach(function (ev, i) {
            if (ev.id.replace(/[^0-9]/g, '') == eventId) {
                if (action == 'delete') {
                    delete eventsItems[i];
                } else if (action == 'done') {
                    var $ev = $('<div>' + ev.eventName + '</div>');
                    $ev.find('.event_title, .event_description')
                    [data ? 'removeClass' : 'addClass']('task_done');
                    var $link = $ev.find('.task_done_title').text(data ? l('done') : l('reopen'));
                    $link.closest('a').data('done', !data).attr('data-done', !data);
                    if (ev.eventName) {
                        ev.eventName = $ev[0].innerHTML;
                    }
                }
                return false;
            }
        })
    }

    this.event_delete = {};
    this.deleteTask = function ($el, eventId) {
        if ($this.event_delete[eventId]) return;
        $this.event_delete[eventId] = 1;
        confirmCustom(l('this_action_can_not_be_undone'), function () {
            $this.setActionEvent(eventId, 'delete');

            addChildrenLoader($el, false);

            var $events = $el.closest('.events'),
                lastEvent = !$events.find('.event_num_bl')[0] && $events.find('.event_content:not(.to_hide)').length == 1,
                $noTask = $events.find('.event_no_item.to_hide'),
                $ev = $el.closest('.event'),
                h = $ev[0].scrollHeight,
                $prevEvent = $ev.prev('.event_content:not(.to_hide)'),
                $evBorder = $prevEvent[0] ? [] : $ev.next('.event_content:not(.to_hide)');

            $ev.data({
                res: function () { },
                show: function () { },
                hide: function () {
                    lastEvent && $noTask.removeClass('to_hide');
                    $ev.hide().css({ overflow: '', height: '' });
                }
            }).oneTransEnd(function () {
                $ev.data('hide')();
                $ev.data('res')();
            }).data('h', h).css({ overflow: 'hidden', height: h }).delay(1).addClass('to_hide', 1);
            $evBorder[0] && $evBorder.addClass('no_border');

            var fnRestore = function () {
                $ev.data({
                    hide: function () { },
                    show: function () {
                        $ev.removeAttr('style')
                        lastEvent && $noTask.addClass('to_hide');
                    }
                }).oneTransEnd(function () {
                    $ev.data('show')();
                }).css({ overflow: 'hidden', height: $ev.data('h') })
                    .show().delay(1).removeClass('to_hide', 1);
                $evBorder[0] && $evBorder.removeClass('no_border');
            },
                fnRemove = function () {
                    $ev.remove();
                    $evBorder[0] && $evBorder.removeClass('no_border');
                };

            var lastId = 0,
                $lastEvent = $events.find('.event_content:not(.to_hide)').last();
            if ($lastEvent[0] && $lastEvent[0].id != ('task_' + eventId)) {
                lastId = $lastEvent[0].id.replace('task_', '');
                lastId = lastId.replace(/[^0-9]/g, '');
            } else if ($prevEvent[0]) {
                lastId = $prevEvent[0].id.replace('task_', '');
                lastId = lastId.replace(/[^0-9]/g, '');
            }

            $.post(urlMain + 'events_event_delete.php?ajax=1',
                {
                    event_id: eventId,
                    uid: $this.uid,
                    last_id: lastId,
                    upload_page_content_ajax: 1
                }, function (res) {
                    $this.event_delete[eventId] = 0;
                    if ($this.noActionEvent(eventId, 'delete')) return;
                    var data = checkDataAjax(res);
                    if (data !== false) {

                        $(data).filter('.script_events_items').appendTo('#update_server');

                        $this.eventActionUpdate(eventId, 'delete');
                        if ($ev.is(':hidden')) {
                            fnRemove();
                        } else {
                            $ev.data('res', fnRemove)
                        }
                        delete $this.event_delete[eventId];

                        var $loadMore = $events.find('.event_num');
                        if ($loadMore[0]) {
                            $this.loadMoreEventsDay($loadMore, true);
                        }

                    } else {
                        fnRestore();
                        alertServerError(true);
                    }
                    removeChildrenLoader($el);
                })
        })
    }

    this.updateMarkersDay = function (day, owners) {
        var $day = $('.day-' + day + ':not(.other)'),
            $bl = $day.find('.day-events');

        $bl.empty();

        var count = owners.my + owners.other,
            cl = 'event_color_';
        if (count) {
            if (count > 3) count = 3;
            for (var i = 0; i < count; i++) {
                var cl1 = cl + (i + 1),
                    $ev = $('<span class="' + cl1 + '">');
                if (!$bl.find(cl1)[0]) {
                    $bl.append($ev);
                }
            }
        }

        /*for (var k in owners) {
            var cl=k=='my'?'my_event_color':'other_event_color',
            clL='.'+cl;
            if (owners[k]) {
                var $ev=$('<span class="'+cl+'">');
                if(!$bl.find(clL)[0]){
                    $bl.append($ev);
                }
            } else {
                $bl.find(clL).remove();
            }
        }*/
    }

    this.event_load_day = {};
    this.event_load_day_page = {};
    this.loadMoreEventsDay = function ($el, inc) {
        if ($el.is('.loaded')) return;
        inc = inc || false;
        var page = $el.data('page') + 1,
            date = $el.closest('.details').data('current_day');
        if (inc) {
            page--;
            console.log('PAAAAAAAA', page);
        }
        if (!date) {
            return;
        }

        var id = +new Date;
        $this.setActionEvent(id, 'load_more');

        $el.addClass('loaded');
        addChildrenLoader($el);


        var ajax_main = "";
        if (window.location.href.includes('event_calendar') || window.location.href.includes('events_calendar.php')) {
            ajax_main = "events_calendar_ajax.php";
        } else {
            ajax_main = "main_calendar_ajax.php";
        }

        $.post(ajax_main,
            {
                upload_page_content_ajax: 1,
                uid: $this.uid,
                event_day_load_more: date,
                event_calendar_day_page: page
            },
            function (res) {

                if ($this.noActionEvent(id, 'load_more')) return;
                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);
                    $data.filter('.script_events_items').appendTo('#update_server');

                    $el.data('page', page);
                } else {
                    alertServerError();
                }
                removeChildrenLoader($el);
                $el.removeClass('loaded');
            })
    }

    this.loadMoreEventsDay_OLD = function ($el) {
        if ($el.is('.loaded')) return;

        $el.addClass('loaded');

        var id = +new Date;
        $this.setActionEvent(id, 'load_more');

        var lastId = 0,
            $last = $el.closest('.events').find('.event_content').last();
        if ($last[0]) {
            var lastIdPre = $last[0].id.replace('task_', '').replace(/[^0-9]/g, '');

            lastId = lastIdPre * 1;
        }
        if (!lastId) {
            $this.hideLoadMoreLink($el);
            return;
        }

        addChildrenLoader($el);

        var ajax_main = "";
        if (window.location.href.includes('event_calendar') || window.location.href.includes('events_calendar.php')) {
            ajax_main = "events_calendar_ajax.php";
        } else {
            ajax_main = "main_calendar_ajax.php";
        }

        $.post(ajax_main + '?cmd=load_more_day',
            {
                upload_page_content_ajax: 1,
                uid: $this.uid,
                event_id: lastId,
                last_id: lastId
            },
            function (res) {

                if ($this.noActionEvent(id, 'load_more')) return;
                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);
                    $data.filter('.script_events_items').appendTo('#update_server');
                } else {
                    alertServerError();
                }
                removeChildrenLoader($el);
                $el.removeClass('loaded');
            })
    }

    this.getHeightContDesc = function ($contDesc) {
        return $contDesc.height() + 25;
    }

    this.prepareEventResize = function () {
        $('.events.in', '#events_calendar').each(function () {
            var $el = $(this);
            $el.find('.event').each(function () {
                var $event = $(this),
                    $desc = $event.find('.event_description'),
                    $contDesc = $event.find('.event_description_content');
                if ($desc.data('down') === undefined) {
                    $this.prepareEventOneDay($event);
                } else {
                    $desc.css({ height: '', maxHeight: '' });
                    if ($contDesc.height() > $desc.outerHeight()) {
                        $el.find('.event_description_more, .event_description_shadow').show();
                    } else {
                        $el.find('.event_description_more, .event_description_shadow').hide();
                    }
                    var h1 = $desc.outerHeight(),
                        h = h1,
                        h2 = 0;
                    $desc.data({ h1: h1, h2: h2 }).attr({ 'data-h1': h1, 'data-h2': h2 });
                    if (!$desc.data('down')) {
                        h2 = $this.getHeightContDesc($contDesc);
                        $desc.data('h2', h2).attr({ 'data-h2': h2 });
                        h = h2;
                    }
                    $desc.css({ height: h, maxHeight: h });
                }
            })
        })
    }

    this.prepareEventOneDay = function ($el) {
        var $desc = $el.find('.event_description');
        if (!$desc[0]) return;
        var $contDesc = $el.find('.event_description_content');
        if ($contDesc.height() > $desc.outerHeight()) {
            $el.find('.event_description_more, .event_description_shadow').show();
            $desc.data({ h1: $desc.outerHeight(), down: 1 }).attr({ 'data-h1': $desc.outerHeight(), 'data-down': 1 });
        } else {
            $el.find('.event_description_more, .event_description_shadow').hide();
        }
    }

    this.prepareEventDay = function ($el) {
        setTimeout(function () {
            $this.prepareEventOneDay($el);
        }, 10)
    }

    this.showEventDayDesc = function ($el) {
        var $event = $el.closest('.event'),
            $desc = $event.find('.event_description'),
            $contDesc = $event.find('.event_description_content'),
            h1 = $desc.data('h1'),
            h2 = $desc.data('h2'),
            h = h1;

        if ($desc.data('down')) {
            if (!h2) {
                h2 = $this.getHeightContDesc($contDesc);
                $desc.data('h2', h2).attr('data-h2', h2);
            }
            h = h2;
            $desc.data('down', 0).attr('data-down', 0)
        } else {
            $desc.data('down', 1).attr('data-down', 1)
        }

        $desc.stop().animate(
            { height: h + 'px', maxHeight: h },
            {
                duration: 400,
                step: function () { },
                complete: function () {
                    $el.removeClass('down');
                    $el[$desc.data('down') ? 'addClass' : 'removeClass']('down');
                }
            })
    }

    this.goToProfileFromPhoto = function ($el) {
        if (!notLoaderIos) $el.addChildrenLoader();
        redirectUrl($el[0].href);//getPrepareUrl($el[0].href)
    }

    this.updateCounter = function (data) {
        if (data.my_open_task != undefined) {
            clCounters.setDataOneCounter('new_tasks', data.my_open_task.count, data.my_open_task.count, true, data.my_open_task.title);
        }
    }
    this.event_done = {};
    this.doneTask = function ($el, eventId) {
        if ($this.event_delete[eventId] || $this.event_done[eventId]) return;
        $this.event_done[eventId] = 1;

        $this.setActionEvent(eventId, 'done');

        addChildrenLoader($el, false);
        var doneEv = $el.data('done'),
            $bl = $el.closest('.bl_event').find('.event_title, .event_description'),
            fnUpdate = function (done) {
                $bl[done ? 'removeClass' : 'addClass']('task_done');
                $el.find('.task_done_title').text(done ? l('done') : l('reopen'));
                $el.data('done', !done).attr('data-done', !done);
            }

        fnUpdate(doneEv);

        var date = $el.closest('.details').data('current_day');
        $.post(url_ajax + '?cmd=task_done', { event_id: eventId, uid: $this.uid, date: date }, function (res) {
            $this.event_done[eventId] = 0;
            if ($this.noActionEvent(eventId, 'done')) return;
            var data = checkDataAjax(res);
            if (data !== false) {
                $this.updateCounter(data);
                if (data.done == doneEv) {
                    doneEv = !doneEv;
                    fnUpdate(doneEv);
                }
                $this.eventActionUpdate(eventId, 'done', doneEv);
            } else {
                alertServerError(true)
            }
            removeChildrenLoader($el);
        })

        return false;
    }

    this.hideLoadMoreLink = function ($el) {
        var dur = 250;
        $el.closest('.events').find('.event_num_bl').slideUp(dur, function () {
            $(this).remove();
        })
    }

    this.goToCalendar = function ($el, url) {
        /*if($el.is('.disabled')){
            return;
        }*/
        redirectUrl(url);
    }

    this.highlightEvent = function (id) {
        var $el = $('#task_' + id);
        if ($el[0]) {
            $el.addClass('event_highlight').delay(2000).removeClass('event_highlight', 0);
        }
    }


    $(function () {
        setWndResizeEvent($this.prepareEventResize);
    })

    return this;
}