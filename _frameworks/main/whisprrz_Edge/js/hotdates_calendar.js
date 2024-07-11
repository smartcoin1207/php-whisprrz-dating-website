var CHotdatesCalendar = function (guid, uid) {

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
        $jq('#bl_hotdates_new_frm_title').html(l('create_new_task'));

        $jq('#pp_task_create').find('.task_create_frm').append(createLoader('contact_frm_loader')).end()
            .one('shown.bs.modal', function () {
                $.post('hotdates_hotdate_task_edit.php?upload_page_content_ajax=1', {}, function (res) {
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

        $this.$date = $('#hotdate_date');
        $this.$date.datetimepicker({
            format: l('calendar_format'),
            minDate: '2019-01-01',
            tooltips: tooltips,
            animationShow: 'fadeIn',//slideDown
            animationHide: 'fadeOut',//slideUp
            animationTime: 250,
            showTodayButton: true
        })

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
        $this.$description = $jq('#hotdate_description')
            .autosize({ isSetScrollHeight: false, callback: function () { } });
    }

    this.initEditItem = function () {
        var isSubmit = false;

        $this.$blFrmAdd = $('#bl_hotdates_new_frm');

        $this.hotdateId = $jq('#hotdate_id').val();
        $this.hotdatePhotoId = $jq('#hotdate_photo_id').val() * 1;
        $this.$upPhotoBl = $jq('#hotdate_photo_upload_bl');
        $this.$upPhotoImg = $jq('#hotdate_photo_upload_img');
        $this.$upPhotoBtn = $('#hotdate_photo_upload');
        $this.$upPhotoBtnDelete = $('#hotdate_photo_upload_delete');

        $this.$upPhotoBtnDelete.prop('disabled', !$this.hotdatePhotoId);

        $this.upPhotoImgId = 0;

        $this.$title = $jq('#hotdate_title');
        $this.$description = $jq('#hotdate_description')
            .on('change propertychange input', setDisabledSave);

        var $hotdateDescBl = $('#hotdate_description_bl');
        $jq('#hotdate_description_expand').click(function () {
            $hotdateDescBl.stop(true, true).slideToggle(250, function () {
                $jq('#hotdate_description_expand').text($hotdateDescBl.is(':hidden') ? l('click_to_expand') : l('click_to_collapse'));
                $this.$description.focus();
            })
        })

        var durFrienBlock = 400, dur = 200;
        var fnToggleListFriends = function (show, hide) {
            if (isSubmit) return false;
            show = show || false;
            hide = hide || false;
            if ($this.$showListFriendBl.is(':hidden') && !hide) {
                $jq('#hotdate_friends_show').addClass('open');
                $this.$showListFriendBl.stop().slideDown(durFrienBlock);
            } else if (!show) {
                $jq('#hotdate_friends_show').removeClass('open');
                $this.$showListFriendBl.stop().slideUp(durFrienBlock);
            }
        }


        $jq('#hotdate_friends_show').click(function () {
            fnToggleListFriends()
        })
        $jq('#hotdate_to_user').click(function () {
            if (isSubmit) return false;
            fnToggleListFriends()
        })

        $this.$showListFriendBl = $jq('#hotdate_friends_list');
        $this.$liListFriends = $this.$showListFriendBl.find('li');
        $this.$listSearchUsersBl = $jq('#hotdate_search_users_bl');
        $this.$listSearchUsers = $jq('#hotdate_search_users_list');

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
                $this.hotdateUserTo.val(name).data('uid', uid);

                var $liFriend = $('#list_friend_hotdate_' + uid);
                $this.$liListFriends.removeClass('selected');
                $liFriend[0] && $liFriend.addClass('selected');
                fnHideAllList();
                setDisabledSave();
            }

        $('a', $this.$showListFriendBl).click(setValUserTo);
        $jq('body').on('click', 'li.search_user_item', setValUserTo)
            .on('click', function (e) {
                var $targ = $(e.target);
                if (!$targ.is('.bl_hotdate_to_list') && !$targ.closest('.bl_hotdate_to_list')[0]
                    && !$targ.is('.bl_hotdate_to_on') && !$targ.closest('.bl_hotdate_to_on')[0]
                    && !$targ.is('#hotdate_to_user')) {
                    fnToggleListFriends(false, true);
                }
                if (!$targ.is('.hotdate_search_users_bl') && !$targ.closest('.hotdate_search_users_bl')[0]) {
                    fnHideSearchList();
                }
            })

        $this.searchResponse = {};
        $this.keySearch = '';
        $this.hotdateUserTo = $jq('#hotdate_to_user').on('keyup', function (e) {//change propertychange input
            $this.hotdateUserTo.data('uid', 0);
            var name = trim(this.value);
            if (name) {
                var k = name.replace(/[%#&\'"\/\\\\< ]/g, '_').toLowerCase(),
                    fnResponse = function (data, notSave) {
                        //console.log('KKKKK', $this.keySearch==k);
                        if (k == $this.keySearch) {
                            if (data !== false && data) {
                                $this.$listSearchUsers.html(data).closest('#hotdate_search_users_bl')
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

        $this.$btnAdd = $('#hotdate_add');

        //https://eonasdan.github.io/bootstrap-datetimepicker/Hotdates/#hotdates

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

        $this.$date = $('#hotdate_date');
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

        /*$this.$time=$('#hotdate_time').datetimepicker({
            format: 'HH:mm',
            stepping: 10,
            tooltips: tooltips,
            animationShow: 'slideDown',
            animationHide: 'slideUp',
            animationTime: 250
        }).on('dp.change', function(e){
            setDisabledSave();
        })*/

        $this.$time = $('#hotdate_time').date_time_picker({
            datepicker: false,
            format: 'H:i',
            step: 30,
            sel: $('#hotdate_time').closest('.field'),
            fnAnimateIn: 'fadeIn',
            fnAnimateOut: 'fadeOut',
            fnAnimateDelay: 250,
            onChangeDateTime: function () {
                setDisabledSave();
            }
        })

        $this.$access = $jq('#hotdate_access');

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
            var userToName = trim($this.hotdateUserTo.val());
            if (userToName == l('myself')) {
                userToName = '';
            }

            var data = {
                ajax: 1,
                hotdate_id: $this.hotdateId,
                hotdate_photo_id: $this.hotdatePhotoId,
                hotdate_user_to: $this.hotdateUserTo.data('uid'),
                hotdate_user_to_name: userToName,
                hotdate_private: $this.$access.val() * 1,
                hotdate_title: trim($this.$title.val()),
                hotdate_description: trim($this.$description.val()),
                hotdate_date: $this.$date.data('current'),
                hotdate_time: $this.$time.val(),
                hotdate_photo_id: $this.upPhotoImgId
            };

            $.post('hotdates_hotdate_task_edit.php?cmd=save', data, function (res) {
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
                '?cmd=upload_temp_photo_hotdate',
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
                            $this.upPhotoImgId = res.id;
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

        $this.$upPhotoImg[0].src = url_tmpl_images + 'hotdate_clock_b.png';
        $this.upPhotoImgId = 0;
        $this.$upPhotoBtn.find('.btn_title').text(l('choose_an_image'));
        $this.hotdatePhotoId = 0;
        $upPhotoBtnDelete.prop('disabled', true);
    }

    this.signUpHotdate = function (element, hotdate_id) {
        $.ajax({
            url: './signEhp.php',
            type: 'post',
            data: { 
                cmd: 'add', 
                hotdate_id: hotdate_id,
                guest_sign_action: "1",
                upload_page_content_ajax: '1'
             },
            success: function (res) {
                dataRes = JSON.parse(res);

                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);

                    $data.filter('.script_hotdates_items').appendTo('#hotdates_calendar_data');

                    var parentElement = $(element).parent(); // Get the parent element
                    $(element).text('Sign Out');  // Example: Change the text of the element
                    $(element).attr('onclick', 'clHotdatesCalendar.signOutHotdate(this, ' + hotdate_id + ')'); // Update the onclick attribute

                    if (dataRes?.pending == true) {
                        parentElement.removeClass('pending_addition');
                    } else {
                        parentElement.removeClass('calendar_addition');
                    }
                }
            }
        });
    }

    this.signOutHotdate = function (element, hotdate_id) {
        $.ajax({
            url: './signEhp.php',
            type: 'post',
            data: {
                 cmd: 'remove', 
                 hotdate_id: hotdate_id,
                 guest_sign_action: "1",
                 upload_page_content_ajax: '1'
            },
            success: function (res) {
                dataRes = JSON.parse(res);

                var data = checkDataAjax(res);
                
                if (data) {
                    var $data = $(data);
                    $data.filter('.script_hotdates_items').appendTo('#hotdates_calendar_data');
                    
                    var parentElement = $(element).parent(); // Get the parent element
                    parentElement.addClass('calendar_addition');
                    parentElement.addClass('pending_addition');
                    $(element).text('Sign Up');  // Example: Change the text of the element
                    $(element).attr('onclick', 'clHotdatesCalendar.signUpHotdate(this, ' + hotdate_id + ')'); // Update the onclick attribute
                } 
            }
        });
    }

    this.getHtmlHotdateItem = function (hotdateId, hotdateDone, userName, userUrl, userPhoto, userOnline, title, description, time, hotdatesNum, urlCreateItem, uidHotdate, uidToHotdate, hotdateEditUrl, hotdateShowUrl, hotdateImage, hotdate_category = '', hotdate_additional_data = '') {
        hotdatesNum *= 1;
        urlCreateItem = urlCreateItem || '';
        title = title || '';
        uidHotdate = uidHotdate || 0;
        uidToHotdate = uidToHotdate || 0,
            hotdateEditUrl = hotdateEditUrl || '';
        hotdateDone *= 1;
        userOnline *= 1;

        hotdate_additional_data_obj = '';
        if (hotdate_additional_data) {
            hotdate_additional_data_obj = JSON.parse(hotdate_additional_data);
        }

        console.log(hotdate_additional_data_obj)

        var html = '';
        if (urlCreateItem) {
            var clEmpty = '';
            if (!title) {
                title = l('no_task');
                clEmpty = 'to_hide';
            }
            html = '<span class="hotdate_no_item ' + clEmpty + '">' + title + '</span>';
            if (hotdatesNum) {
                var numberMoreHotdate = calendar_show_more_lang.replace('{number}', hotdatesNum);
                html += '<div class="hotdate_num_bl">' +
                    '<span class="hotdate_num" data-page="1" data-number="' + hotdatesNum + '" onclick="clHotdatesCalendar.loadMoreHotdatesDay($(this))">' +
                    '<span class="icon_fa"><i class="fa fa-angle-double-down" aria-hidden="true"></i></span>' +
                    '<span class="hotdate_num_title">' + numberMoreHotdate + '</span>' +
                    '</span>' +
                    '</div>';
            }
            html += '<button onclick="redirectFormUrlLoader($(this),\'' + urlCreateItem + '\',\'show_back=1\')" class="btn btn-block btn_add_page btn-success">' +
                '<span class="icon_fa" data-cl-loader="header_menu_loader_more">' +
                '<i class="fa fa-plus"></i>' +
                '</span>' +
                '<span> ' + l('add_new_hotdate_task') + '</span>' +
                '</button>';

            /*html +='<button onclick="clHotdatesCalendar.openCreateForm(); return false;" class="btn btn-block btn_add_page btn-success">'+
                        '<span class="icon_fa" data-cl-loader="header_menu_loader_more"></span>'+
                        '<span><span class="fa fa-plus"></span> '+l('add_new_hotdate_task')+'</span>'+
                   '</button>';*/
            return html;
        }

        if (userPhoto || userName || time) {

            html += '<span class="hotdate_info_bl">';
            if (userPhoto) {
                var online = userOnline ? '<div class="hotdate_user_online"></div>' : '';
                html += '<div class="hotdate_user_photo">' +
                    '<a href="' + userUrl + '" onclick="clHotdatesCalendar.goToProfileFromPhoto($(this)); return false;" ' +
                    ' class="pic" style="background-image:url(' + urlFiles + userPhoto + ');"></a>' +
                    online +
                    '</div>';
            }

            html += '<span class="hotdate_info" style="width:80px; vertical-align: top;">';
            if (userName) {
                html += '<span class="hotdate_user_name"><a href="' + userUrl + '">' + userName + '</a></span>';
            }
            if (time) {
                html += '<span class="hotdate_time">' + time + '</span>';
            }

            var menuHotdateWrapper =
                '<span class="hotdate_more_menu" data-toggle="collapse" data-target="#task_more_menu_' + hotdateId + '" aria-expanded="false">' +
                '<span class="icon_chevron_down">' +
                '<svg viewBox="0 0 48 48"><path d="M14.83 16.42l9.17 9.17 9.17-9.17 2.83 2.83-12 12-12-12z"/></svg>' +
                '</span>' +
                '<ul id="task_more_menu_' + hotdateId + '" class="more_menu_collapse more_menu_right1 collapse">' +
                '{menu_items}' +
                '</ul>' +
                '</span>';
            var menuHotdateDone =
                '<li>' +
                '<a href="#" data-done="' + hotdateDone + '" onclick="clHotdatesCalendar.doneTask($(this), \'' + hotdateId + '\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-check" aria-hidden="true"></i></span>' +
                '<span class="task_done_title">' + (hotdateDone ? l('reopen') : l('done')) + '</span>' +
                '</a>' +
                '</li>';
            var menuHotdateItems =
                '{menu_item_done}' +
                '<li>' +
                '<a href="#" onclick="redirectFormUrlLoader($(this),\'' + hotdateEditUrl + '\',\'show_back=1\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>' +
                '<span>' + l('edit') + '</span>' +
                '</a>' +
                '</li>' +
                '<li>' +
                '<a href="#" onclick="clHotdatesCalendar.deleteTask($(this),\'' + hotdateId + '\'); return false;">' +
                '<span class="icon_fa" data-cl-loader="task_menu_loader_more"><i class="fa fa-times" aria-hidden="true"></i></span>' +
                '<span>' + l('delete') + '</span>' +
                '</a>' +
                '</li>';

            if ($this.guid == uidHotdate) {
                menuHotdateItems = menuHotdateItems.replace('{menu_item_done}', menuHotdateDone);
            } else if ($this.guid == uidToHotdate) {
                menuHotdateItems = menuHotdateDone;
            } else {
                menuHotdateItems = '';
            }
            var menuHotdate = '';
            if (menuHotdateItems) {
                menuHotdate = menuHotdateWrapper.replace('{menu_items}', menuHotdateItems);
            }
            html += menuHotdate + '</span>';

            html += '<div  style="display: flex; position: relative;">';

            html += '<a href="' + hotdateShowUrl + '">';
            html += '<img src="' + hotdateImage + '" style="width:66px; height: 45px; display: block; float: left; margin-right: 10px; ">';

            if (hotdate_additional_data_obj?.accepted == '1') {
                html += '<div class="approved_mark" style="background-image:url(' + urlFiles + 'icons/approved.png);"></div>';
            }

            html += "</a>";

            var clDone = hotdateDone ? 'task_done' : '';
            html += '<div style="text-align: center;"><a href="' + hotdateShowUrl + '"><span class="hotdate_title ' + clDone + '" style="color: white; font-size: 15px; text-decoration: underline;">' + title + '</span></a>';

            if (hotdate_category) {
                html += '<span class="hotdate_category" style="color: #bdc6d0; font-size: 12px;">' + hotdate_category + '</span>';
            }

            if (hotdate_additional_data_obj) {
                html += '<span class="hotdate_category" style="color: #bdc6d0; font-size: 12px;">' + '<br> State: ' + hotdate_additional_data_obj?.state_title + ', <br> City:' + hotdate_additional_data_obj?.city_title + '</span>';
            }

            html += '</div>';

            if (hotdate_additional_data_obj) {
                signUpHtml = '';
                if (!hotdate_additional_data_obj?.is_own && !hotdate_additional_data_obj?.is_finished) {
                    signUpHtml = hotdate_additional_data_obj?.is_member ? '<div class="calendar_button" onclick="clHotdatesCalendar.signOutHotdate(this, ' + hotdateId + ')">Sign Out</div>' : '<div class="calendar_button" onclick="clHotdatesCalendar.signUpHotdate(this, ' + hotdateId + ')">Sign Up</div>';
                }

                additionClassString = hotdate_additional_data_obj?.is_member ? '' : 'calendar_addition';
                pendingClass = 'pending_addition';
                if (hotdate_additional_data_obj?.accepted == 0 && hotdate_additional_data_obj?.is_member) {
                    additionClassString = 'calendar_addition';
                    pendingClass = '';
                }

                html += '<div style="display:flex; flex-wrap: wrap; align-content: flex-start" class="' + additionClassString + " " + pendingClass + '">' + signUpHtml;
                html += '<div class="pending_hide" style="padding: 5px; background-color: #e1b113; font-size: 12px; line-height: 17px; border-radius: 5px; margin: auto; margin-left: 30px;">Pending...</div>'

                html += '<div class="dropdown btn_hide"> <button class="dropdown-toggle calendar_button" type="button" data-toggle="dropdown" style="height: auto; border: 0px;">Full Description<span class="caret"></span></button> ';
                html += '<ul class="dropdown-menu" style="top: auto; width: 250px;">';
                html += '<li><a>Place: ' + hotdate_additional_data_obj?.place + ' </a></li>';
                html += '<li><div style="padding: 8px 17px 8px 15px; color: black;">Web Site: <a href="' + hotdate_additional_data_obj?.site + '">Site</a> </div></li>';
                html += '<li><a>Phone: ' + hotdate_additional_data_obj?.phone + ' </a></li>';
                html += '</ul></div>';

                html += '<a target="blank" href="' + hotdate_additional_data_obj?.address + '"  class="calendar_button btn_hide">address</a>';

                if (hotdate_additional_data_obj?.wall) {
                    html += '<a href="' + hotdate_additional_data_obj?.wall + '" class="calendar_button btn_hide">Hotdate wall</a>';
                }
                html += '</div>';
            }

            html += '</div></span>';
        }

        if (description) {
            html += '<span class="hotdate_description ' + clDone + '">' +
                '<span class="hotdate_description_content">' + description + '</span>' +
                '<span class="hotdate_description_shadow"></span>' +
                '</span>' +
                '<span class="hotdate_description_more down" onclick="clHotdatesCalendar.showHotdateDayDesc($(this));">' +
                '<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>' +
                '<i class="fa fa-chevron-circle-up" aria-hidden="true"></i>' +
                '</span>';
        }

        return html;
    }

    this.hotdatesLoad = function (next, callBack) {
        next = next || false;
        var month = $this.selectMonth - 1, year = $this.selectYear;
        if (next) {
            month = $this.selectMonth + 1, year = $this.selectYear;
        }

        $('.hotdate_loader').addChildrenLoader();

        $.post(urlMain + 'hotdates_calendar_ajax.php?upload_page_content_ajax=1&calendar_month=' + month + '&calendar_year=' + year + '&uid=' + this.uid, {}, function (res) {
            var data = checkDataAjax(res);
            if (data) {
                var $script = $(data).filter('script');
                if ($script[0]) {
                    $jq('#hotdates_calendar_data').append($script);
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
            $('.hotdate_loader').removeChildrenLoader();
        })

        return false;
    }

    this.calendarSearch = function (payload, callBack) {
        $('.hotdate_loader').addChildrenLoader();

        $.post(urlMain + 'hotdates_calendar_ajax.php?upload_page_content_ajax=1&search=1&uid=' + this.uid, payload, function (res) {
            var data = checkDataAjax(res);
            if (data) {
                var $script = $(data).filter('script');
                if ($script[0]) {
                    $jq('#hotdates_calendar_data').append($script);

                    $this.replaceUrl(payload.calendar_year + '-' + payload.calendar_month + '-01');
                    if (typeof callBack == 'function') {
                        callBack();
                    }
                } else {
                    alertServerError(true)
                }
            } else {
                alertServerError(true)
            }
            $('.hotdate_loader').removeChildrenLoader();
        })
        return false;
    }

    this.replaceUrl = function (date) {
        var pageUrl = $this.urlPage;
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

    this.setActionHotdate = function (hotdateId, action) {
        action = action || 'delete';
        $this.checkActionData[action][hotdateId] = {
            m: $this.selectMonth,
            y: $this.selectYear
        }
    }

    this.noActionHotdate = function (hotdateId, action) {
        action = action || 'delete';
        if (!$this.checkActionData[action][hotdateId]) return true;
        var m = $this.checkActionData[action][hotdateId].m,
            y = $this.checkActionData[action][hotdateId].y;
        return m != $this.selectMonth || y != $this.selectYear;
    }

    this.hotdateActionUpdate = function (hotdateId, action, data) {
        hotdatesItems.forEach(function (ev, i) {
            if (ev.id == hotdateId) {
                if (action == 'delete') {
                    delete hotdatesItems[i];
                } else if (action == 'done') {
                    var $ev = $('<div>' + ev.hotdateName + '</div>');
                    $ev.find('.hotdate_title, .hotdate_description')
                    [data ? 'removeClass' : 'addClass']('task_done');
                    var $link = $ev.find('.task_done_title').text(data ? l('done') : l('reopen'));
                    $link.closest('a').data('done', !data).attr('data-done', !data);
                    if (ev.hotdateName) {
                        ev.hotdateName = $ev[0].innerHTML;
                    }
                }
                return false;
            }
        })
    }

    this.hotdate_delete = {};
    this.deleteTask = function ($el, hotdateId) {
        if ($this.hotdate_delete[hotdateId]) return;
        $this.hotdate_delete[hotdateId] = 1;
        confirmCustom(l('this_action_can_not_be_undone'), function () {
            $this.setActionHotdate(hotdateId, 'delete');

            addChildrenLoader($el, false);

            var $hotdates = $el.closest('.hotdates'),
                lastHotdate = !$hotdates.find('.hotdate_num_bl')[0] && $hotdates.find('.hotdate_content:not(.to_hide)').length == 1,
                $noTask = $hotdates.find('.hotdate_no_item.to_hide'),
                $ev = $el.closest('.hotdate'),
                h = $ev[0].scrollHeight,
                $prevHotdate = $ev.prev('.hotdate_content:not(.to_hide)'),
                $evBorder = $prevHotdate[0] ? [] : $ev.next('.hotdate_content:not(.to_hide)');

            $ev.data({
                res: function () { },
                show: function () { },
                hide: function () {
                    lastHotdate && $noTask.removeClass('to_hide');
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
                        lastHotdate && $noTask.addClass('to_hide');
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
                $lastHotdate = $hotdates.find('.hotdate_content:not(.to_hide)').last();
            if ($lastHotdate[0] && $lastHotdate[0].id != ('task_' + hotdateId)) {
                lastId = $lastHotdate[0].id.replace('task_', '');
            } else if ($prevHotdate[0]) {
                lastId = $prevHotdate[0].id.replace('task_', '');
            }

            $.post(urlMain + 'hotdates_hotdate_delete.php?ajax=1',
                {
                    hotdate_id: hotdateId,
                    uid: $this.uid,
                    last_id: lastId,
                    upload_page_content_ajax: 1
                }, function (res) {
                    $this.hotdate_delete[hotdateId] = 0;
                    if ($this.noActionHotdate(hotdateId, 'delete')) return;
                    var data = checkDataAjax(res);
                    if (data !== false) {

                        $(data).filter('.script_hotdates_items').appendTo('#update_server');

                        $this.hotdateActionUpdate(hotdateId, 'delete');
                        if ($ev.is(':hidden')) {
                            fnRemove();
                        } else {
                            $ev.data('res', fnRemove)
                        }
                        delete $this.hotdate_delete[hotdateId];

                        var $loadMore = $hotdates.find('.hotdate_num');
                        if ($loadMore[0]) {
                            $this.loadMoreHotdatesDay($loadMore, true);
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
            $bl = $day.find('.day-hotdates');

        $bl.empty();

        var count = owners.my + owners.other,
            cl = 'hotdate_color_';
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
            var cl=k=='my'?'my_hotdate_color':'other_hotdate_color',
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

    this.hotdate_load_day = {};
    this.hotdate_load_day_page = {};
    this.loadMoreHotdatesDay = function ($el, inc) {
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
        $this.setActionHotdate(id, 'load_more');

        $el.addClass('loaded');
        addChildrenLoader($el);

        $.post('hotdates_calendar_ajax.php',
            {
                upload_page_content_ajax: 1,
                uid: $this.uid,
                hotdate_day_load_more: date,
                hotdate_calendar_day_page: page
            },
            function (res) {

                if ($this.noActionHotdate(id, 'load_more')) return;
                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);
                    $data.filter('.script_hotdates_items').appendTo('#update_server');

                    $el.data('page', page);
                } else {
                    alertServerError();
                }
                removeChildrenLoader($el);
                $el.removeClass('loaded');
            })
    }

    this.loadMoreHotdatesDay_OLD = function ($el) {
        if ($el.is('.loaded')) return;

        $el.addClass('loaded');

        var id = +new Date;
        $this.setActionHotdate(id, 'load_more');

        var lastId = 0,
            $last = $el.closest('.hotdates').find('.hotdate_content').last();
        if ($last[0]) {
            lastId = $last[0].id.replace('task_', '') * 1;
        }
        if (!lastId) {
            $this.hideLoadMoreLink($el);
            return;
        }

        addChildrenLoader($el);

        $.post('hotdates_calendar_ajax.php?cmd=load_more_day',
            {
                upload_page_content_ajax: 1,
                uid: $this.uid,
                hotdate_id: lastId,
                last_id: lastId
            },
            function (res) {

                if ($this.noActionHotdate(id, 'load_more')) return;
                var data = checkDataAjax(res);
                if (data) {
                    var $data = $(data);
                    $data.filter('.script_hotdates_items').appendTo('#update_server');
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

    this.prepareHotdateResize = function () {
        $('.hotdates.in', '#hotdates_calendar').each(function () {
            var $el = $(this);
            $el.find('.hotdate').each(function () {
                var $hotdate = $(this),
                    $desc = $hotdate.find('.hotdate_description'),
                    $contDesc = $hotdate.find('.hotdate_description_content');
                if ($desc.data('down') === undefined) {
                    $this.prepareHotdateOneDay($hotdate);
                } else {
                    $desc.css({ height: '', maxHeight: '' });
                    if ($contDesc.height() > $desc.outerHeight()) {
                        $el.find('.hotdate_description_more, .hotdate_description_shadow').show();
                    } else {
                        $el.find('.hotdate_description_more, .hotdate_description_shadow').hide();
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

    this.prepareHotdateOneDay = function ($el) {
        var $desc = $el.find('.hotdate_description');
        if (!$desc[0]) return;
        var $contDesc = $el.find('.hotdate_description_content');
        if ($contDesc.height() > $desc.outerHeight()) {
            $el.find('.hotdate_description_more, .hotdate_description_shadow').show();
            $desc.data({ h1: $desc.outerHeight(), down: 1 }).attr({ 'data-h1': $desc.outerHeight(), 'data-down': 1 });
        } else {
            $el.find('.hotdate_description_more, .hotdate_description_shadow').hide();
        }
    }

    this.prepareHotdateDay = function ($el) {
        setTimeout(function () {
            $this.prepareHotdateOneDay($el);
        }, 10)
    }

    this.showHotdateDayDesc = function ($el) {
        var $hotdate = $el.closest('.hotdate'),
            $desc = $hotdate.find('.hotdate_description'),
            $contDesc = $hotdate.find('.hotdate_description_content'),
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
    this.hotdate_done = {};
    this.doneTask = function ($el, hotdateId) {
        if ($this.hotdate_delete[hotdateId] || $this.hotdate_done[hotdateId]) return;
        $this.hotdate_done[hotdateId] = 1;

        $this.setActionHotdate(hotdateId, 'done');

        addChildrenLoader($el, false);
        var doneEv = $el.data('done'),
            $bl = $el.closest('.bl_hotdate').find('.hotdate_title, .hotdate_description'),
            fnUpdate = function (done) {
                $bl[done ? 'removeClass' : 'addClass']('task_done');
                $el.find('.task_done_title').text(done ? l('done') : l('reopen'));
                $el.data('done', !done).attr('data-done', !done);
            }

        fnUpdate(doneEv);

        var date = $el.closest('.details').data('current_day');
        $.post(url_ajax + '?cmd=task_done', { hotdate_id: hotdateId, uid: $this.uid, date: date }, function (res) {
            $this.hotdate_done[hotdateId] = 0;
            if ($this.noActionHotdate(hotdateId, 'done')) return;
            var data = checkDataAjax(res);
            if (data !== false) {
                $this.updateCounter(data);
                if (data.done == doneEv) {
                    doneEv = !doneEv;
                    fnUpdate(doneEv);
                }
                $this.hotdateActionUpdate(hotdateId, 'done', doneEv);
            } else {
                alertServerError(true)
            }
            removeChildrenLoader($el);
        })

        return false;
    }

    this.hideLoadMoreLink = function ($el) {
        var dur = 250;
        $el.closest('.hotdates').find('.hotdate_num_bl').slideUp(dur, function () {
            $(this).remove();
        })
    }

    this.goToCalendar = function ($el, url) {
        /*if($el.is('.disabled')){
            return;
        }*/
        redirectUrl(url);
    }

    this.highlightHotdate = function (id) {
        var $el = $('#task_' + id);
        if ($el[0]) {
            $el.addClass('hotdate_highlight').delay(2000).removeClass('hotdate_highlight', 0);
        }
    }

    $(function () {
        setWndResizeEvent($this.prepareHotdateResize);
    })

    return this;
}