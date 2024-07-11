!function() {
    var today = moment();

    function Calendar(selector, events, date, day) {
        this.el = document.querySelector(selector);
        this.events = events;
        // alert();
        date=date||'';
        day=(day*1)||0;

        if (date) {
            this.current = moment(date, "YYYY-MM-DD", true).date(1);
        } else {
            this.current = moment().date(1);
        }
        this.draw();
        var self=this,currentDay=false;
        if(day){
            currentDay=$('.day-'+day+':not(.other)')[0]
        }
        if(!currentDay){
            currentDay=document.querySelector('.today');
        }

        currentDay && $win.load(function(){
            clMediaTools.scrollToEl(currentDay,function(){
                self.openDay(currentDay)
            })
        });


        $jq('body').on('click', function(e){
            var $targ=$(e.target);
            if (!$targ.is('#month_wrapper')
                    &&!$targ.is('.calendar_arrow')
                    &&!$targ.closest('#month_wrapper')[0]
                    &&$('.details_bl:visible','#month_wrapper')[0]
                    &&!$targ.is('.modal') && !$targ.closest('.modal')[0]){

                self.hideDay();
            }
        })
        this.searchCalendar();
    }

    Calendar.prototype.setEvents = function(events) {
        this.events = events;
        this.currentOpenDay=0;
    }

    Calendar.prototype.updateEvents = function(events, event_id, maincalendar_type = '') {
        if(!event_id) return false;

        if(maincalendar_type) {
            let event  = this.events.find(item => item.id == event_id && item?.type == maincalendar_type);
            let newEvent = events.find(item => item.id == event_id && item?.type == maincalendar_type);
            
            if(event && newEvent) {
                event.eventName = newEvent.eventName;
            } else if( event && !newEvent) {
                this.events = this.events.filter(item => !(item?.type == maincalendar_type && item.id == event_id));
            }
        } else {
            let event  = this.events.find(item => item.id == event_id);
            let newEvent = events.find(item => item.id == event_id);
            
            if(event && newEvent) {
                event.eventName = newEvent.eventName;
            } else if( event && !newEvent) {
                // this.events = this.events.filter(item => item.id !== event_id);
            }
        }
    }

    Calendar.prototype.addEvents = function(events, dayNumber, numberMore) {
        console.log(events);
        var $blEvents=$('.events.in');

        if(!$blEvents[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.events.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.eventName.indexOf('event_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blEvents.find('.event.event_empty'),
            $numberMoreBl=$('.events.in').find('.event_num_bl')
            $numberMore=$('.events.in').find('.event_num'),
            $numberMoreTitle=$numberMore.find('.event_num_title'),
            $numberMoreEv=$numberMore.closest('.event'),
            n=$numberMore.data('number');

        events.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {

                ev.date=self.current.clone().date(ev.date);
                self.events.splice(ind, 0, ev);
                ind1++;

                var $event=$(self.getHtmlOneEvent(ev))
                    .css({display:'none', transition:'none'})
                    .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                        $event.removeAttr('style');
                    });
                clEventsCalendar.prepareEventDay($event);

                n--;
                if (!n) {
                    self.events[ind1].eventName=self.events[ind1].eventName.replace(/<div class="event_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');

                    $numberMoreBl.slideUp(dur,function(){
                        $(this).remove();
                    });
                }else{
                    $numberMore.data('number',n);
                    $numberMoreTitle.text(calendar_show_more_lang.replace('{number}',n));
                }
            }
        })
    }

    Calendar.prototype.addEvents_OLD = function(events, dayNumber, numberMore) {
        var $blEvents=$('.events.in');
        if(!$blEvents[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.events.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.eventName.indexOf('event_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blEvents.find('.event.event_empty'),
            $numberMoreBl=$('.events.in').find('.event_num_bl')
            $numberMore=$('.events.in').find('.event_num'),
            $numberMoreTitle=$numberMore.find('.event_num_title'),
            $numberMoreEv=$numberMore.closest('.event'),
            n=$numberMore.data('number');

        numberMore=defaultFunctionParamValue(numberMore, false);
        if (numberMore !== false && numberMore !== '') {
            numberMore *=1;
            if (numberMore) {
                $numberMore.data('number',numberMore);
                $numberMoreTitle.text(calendar_show_more_lang.replace('{number}',numberMore));
            } else {
                $numberMoreBl.slideUp(dur,function(){
                    $(this).remove();
                });
            }
        }

        events.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {
                ev.date=self.current.clone().date(ev.date);
                self.events.splice(ind, 0, ev);
                ind1++;
                var $event=$(self.getHtmlOneEvent(ev))
                      .css({display:'none', transition:'none'})
                      .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                            $event.removeAttr('style');
                      });
                clEventsCalendar.prepareEventDay($event);

                /*n--;
                if (!n) {
                    self.events[ind1].eventName=self.events[ind1].eventName.replace(/<div class="event_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');
                    $numberMoreBl.slideUp(dur,function(){
                        $(this).remove();
                    });
                }else{
                    $numberMore.data('number',n);
                    $numberMoreTitle.text(calendar_show_more_lang.replace('{number}',n));
                }*/
            }
        })
    }

    Calendar.prototype.draw = function() {
        this.drawHeader();
        this.drawMonth();
        //this.drawLegend();
    }

    Calendar.prototype.drawHeader = function() {
        var self = this;
        if(!this.header) {
            //Create the header elements
            this.header = createElement('div', 'header');
            this.header.className = 'header';

            this.title = createElement('h1');

            var titleSpan = createElement('span', 'event_loader');
            titleSpan.className = 'event_loader';

            var right = createElement('div', 'right calendar_arrow');
            right.addEventListener('click', function() { self.nextMonth(); });

            var left = createElement('div', 'left calendar_arrow');
            left.addEventListener('click', function() { self.prevMonth(); });

            //Append the Elements
            this.header.appendChild(this.title);
            this.header.appendChild(titleSpan);

            this.header.appendChild(right);
            this.header.appendChild(left);
            this.el.appendChild(this.header);
        }
        this.title.innerHTML = this.current.format('MMMM YYYY');
    }

  Calendar.prototype.drawMonth = function() {
    var self = this;
    this.events.forEach(function(ev) {
        ev.date = self.current.clone().date(ev.date);//self.current.clone().date(Math.random() * (29 - 1) + 1);
    });

    if(this.month) {
      this.oldMonth = this.month;
      this.oldMonth.className = 'month out ' + (self.next ? 'next' : 'prev');
      this.oldMonth.addEventListener('webkitAnimationEnd', function() {
        self.oldMonth.parentNode.removeChild(self.oldMonth);
        self.month = createElement('div', 'month');
        self.month.id='month_wrapper';
        self.backFill();
        self.currentMonth();
        self.fowardFill();
        self.el.appendChild(self.month);
        window.setTimeout(function() {
          self.month.className = 'month in ' + (self.next ? 'next' : 'prev');
        }, 16);
      });
    } else {
        this.month = createElement('div', 'month');
        this.month.id='month_wrapper';
        this.el.appendChild(this.month);
        this.backFill();
        this.currentMonth();
        this.fowardFill();
        this.month.className = 'month new';
    }
  }

  Calendar.prototype.searchCalendar = function() {
    var self = this;
    var searchForm = document.getElementById('calendar-search-form');
    var submitButton = document.getElementById('calendar-search-submit');

    submitButton.addEventListener('click', function() {
        var country_id = searchForm.elements['country_id'].value;
        var state_id = searchForm.elements['state_id'].value;
        var city_id = searchForm.elements['city_id'].value;

        var coupleValue = searchForm.elements['looking_for_couple'].checked ? '1' : '0';
        var maleValue = searchForm.elements['looking_for_male'].checked ? '1' : '0';
        var femaleValue = searchForm.elements['looking_for_female'].checked ? '1' : '0';
        var transgenderValue = searchForm.elements['looking_for_transgender'].checked ? '1' : '0';
        var nonbinaryValue = searchForm.elements['looking_for_nonbinary'].checked ? '1' : '0';

        var eventCategoryId  = searchForm.elements['event_category_id']?.value;
        var hotdateCategoryId  = searchForm.elements['hotdate_category_id']?.value;
        var partyhouCategoryId  = searchForm.elements['partyhou_category_id']?.value;
        var calendar_partyhou_locked = searchForm.elements['partyhou_lock_id']?.value;

        var calendar_month = searchForm.elements['calendar_month'].value;
        if(calendar_month.length == 1) {
            var calendar_month = '0' + calendar_month;
        }
        var calendar_year = searchForm.elements['calendar_year'].value;
        var calendar_distance = searchForm.elements['calendar_distance'].value;

        var data = 
        {
            calendar_month: calendar_month,
            calendar_year: calendar_year,
            country_id: country_id,
            state_id: state_id,
            city_id: city_id,
            event_category_id: eventCategoryId,
            hotdate_category_id: hotdateCategoryId,
            partyhou_category_id: partyhouCategoryId,
            partyhouz_locked: calendar_partyhou_locked,
            distance: calendar_distance,
            couple: coupleValue,
            male: maleValue,
            female: femaleValue,
            transgender: transgenderValue,
            nonbinary: nonbinaryValue,
        };

        clEventsCalendar.calendarSearch(data, function() {
            console.log('calendar search');

            var date = calendar_year + "-" + calendar_month + "-01";

            if (date) {
                self.current = moment(date, "YYYY-MM-DD", false).date(1);
            } else {
                self.current = moment().date(1);
            }
            
            var day = '01';
            currentDay=false;
            if(day){
                currentDay=$('.day-'+day+':not(.other)')[0]
            }
            if(!currentDay){
                currentDay=document.querySelector('.today');
            }

            currentDay && $win.load(function(){
                clMediaTools.scrollToEl(currentDay,function(){
                    self.openDay(currentDay)
                })
            });

            $jq('body').on('click', function(e){
                var $targ=$(e.target);
                if (!$targ.is('#month_wrapper')
                        &&!$targ.is('.calendar_arrow')
                        &&!$targ.closest('#month_wrapper')[0]
                        &&$('.details_bl:visible','#month_wrapper')[0]
                        &&!$targ.is('.modal') && !$targ.closest('.modal')[0]){

                    self.hideDay();
                }
            })
                        self.draw();

        })
    });
  }

  Calendar.prototype.backFill = function() {
    var clone = this.current.clone();
    var dayOfWeek = clone.weekday();//clone.day();
    if(!dayOfWeek) { return; }
    clone.subtract(dayOfWeek+1, 'days');

    for(var i = dayOfWeek; i > 0 ; i--) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  Calendar.prototype.fowardFill = function() {
    var clone = this.current.clone().add(1, 'months').subtract(1, 'days');
    var dayOfWeek = clone.weekday();//clone.day();

    if(dayOfWeek === 6) { return; }

    for(var i = dayOfWeek; i < 6 ; i++) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  Calendar.prototype.currentMonth = function() {
    var clone = this.current.clone();

    while(clone.month() === this.current.month()) {
      this.drawDay(clone);
      clone.add(1, 'days');
    }
  }

  Calendar.prototype.getWeek = function(day) {
    var day=day.weekday();//clone.day();
    if(!this.week || day === 0) {
      this.week = createElement('div', 'week');
      this.month.appendChild(this.week);
    }
  }

  Calendar.prototype.drawDay = function(day) {
    var self = this;
    this.getWeek(day);
    //Outer Day
    var outer = createElement('div', this.getDayClass(day) + ' day-'+day.format('D'));
    outer.addEventListener('click', function() {
      self.openDay(this);
    });

    //Day Name
    var name = createElement('div', 'day-name', day.format('ddd'));

    //Day Number
    var number = createElement('div', 'day-number', day.format('DD'));


    //Events
    var events = createElement('div', 'day-events');
    this.drawEvents(day, events);
    outer.appendChild(name);
    outer.appendChild(number);
    outer.appendChild(events);
    this.week.appendChild(outer);
  }

  Calendar.prototype.drawEvents = function(day, element) {
    if(day.month() === this.current.month()) {
      var todaysEvents = this.events.reduce(function(memo, ev) {
        if(ev.date.isSame(day, 'day')) {
          memo.push(ev);
        }
        return memo;
      }, []);

      todaysEvents.forEach(function(ev) {
            /*if(ev.eventName.indexOf('event_no_item')===-1 && ev.eventName.indexOf('btn_add_page')===-1){
                var evSpan = createElement('span', ev.color);
                if(!$(element).find('.'+ev.color)[0]){
                    element.appendChild(evSpan);
                }
            } else*/
            if (ev.id == 0 && ev.owners != undefined
                    && (ev.owners.my || ev.owners.other)) {
                var count=ev.owners.my + ev.owners.other,
                    cl='event_color_';
                if(count){
                    if(count>3)count=3;
                    for (var i = 0; i < count; i++) {
                        var cl1=cl+(i+1),
                            evSpan = createElement('span', cl1);
                        if(!$(element).find('.'+cl1)[0]){
                            element.appendChild(evSpan);
                        }
                    }
                }
                /*for (var key in ev.owners) {
                    if (ev.owners[key]) {
                        var cl=key=='my'?'my_event_color':'other_event_color';
                        var evSpan = createElement('span', cl);
                        if(!$(element).find('.'+cl)[0]){
                            element.appendChild(evSpan);
                        }
                    }
                }*/
            }
      });
    }
  }

  Calendar.prototype.getDayClass = function(day) {
    classes = ['day'];
    if(day.month() !== this.current.month()) {
        classes.push('other');
    } else if (today.isSame(day, 'day')) {
        classes.push('today');
    } else if (today.isAfter(day, 'day')) {
        classes.push('day_old');
    }
    return classes.join(' ');
  }

    Calendar.prototype.hideDay = function(callBack, notResetCurrentOpenDay) {
        if(typeof callBack!='function'){
            callBack=function(){
                isOpenDay=false;
            }
        }

        var $currentOpened = $('.details'),
            $currentOpenedBl = $currentOpened.closest('.details_bl');
        if($currentOpened[0] && $currentOpened.is('.to_animate')){
            return;
        }

        notResetCurrentOpenDay=notResetCurrentOpenDay||false;
        if (!notResetCurrentOpenDay) {
            this.currentOpenDay=0;
        }

        if($currentOpened[0] && $currentOpened.is('.in')) {
            $currentOpened.addClass('to_animate');
            $weekAll = $currentOpenedBl.closest('.week').nextAll('.week');

            var h=$currentOpened.height()+8;

            $currentOpened.find('.events').addClass('out');
            $currentOpenedBl.removeClass('in');
            $currentOpened.slideUp(500,function(){
                $currentOpened.removeClass('to_animate');
                $currentOpenedBl.remove();
                callBack();
            })

            /*$currentOpened.oneTransEnd(function(){
                $currentOpened.removeClass('to_animate');
                $currentOpenedBl.remove();
                $weekAll.css({transform:'', transition:'none'});
                callBack();
            },'transform').removeClass('in');

            $weekAll.css({transform:'translateY(-'+h+'px)', transition:'transform .5s'})*/

        } else {
            $currentOpenedBl.remove();
            callBack();
        }
    }

    var isOpenDay=false;
    Calendar.prototype.openDay = function(el,dayNumber) {
        if(isOpenDay)return;
        isOpenDay=true;
        var details, arrow, self=this;

        dayNumber = dayNumber || (+el.querySelectorAll('.day-number')[0].innerText || +el.querySelectorAll('.day-number')[0].textContent);
        var day = this.current.clone().date(dayNumber);

        var currentOpened = document.querySelector('.details'),
            $detailsBl=[], $week=[], $weekAll=[];

        if(this.currentOpenDay && this.currentOpenDay.isSame(day, 'day')){
            this.hideDay();
            return;
        }
        this.currentOpenDay=day;

        //Check to see if there is an open detais box on the current row
        if(currentOpened){
            //console.log(11111,currentOpened.parentNode , el.parentNode);
        }

        //if(currentOpened && currentOpened.parentNode === el.parentNode) {//details_bl, week
            //details = currentOpened;
            //arrow = document.querySelector('.arrow');
        //} else {
      //Close the open events on differnt week row
      //currentOpened && currentOpened.parentNode.removeChild(currentOpened);
     /*if(currentOpened) {
        currentOpened.addEventListener('webkitAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('oanimationend', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('msAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('animationend', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.className = 'details out';
      }*/
        this.hideDay(function(){

            //Create the Details Container
            //var detailsBl=createElement('div', 'details_bl');
            //details = createElement('div', 'details in');
            //detailsBl.appendChild(details);
            var $detailsBl=$('<div class="details_bl"><div class="arrow"></div><div class="details"></div></div>'),
            details=$detailsBl.find('.details')[0],
            arrow=$detailsBl.find('.arrow')[0];


            $week=$(el).closest('.week');
            $weekAll=$week.nextAll('.week');
            //$detailsBl.appendTo($week).find('.details').toggleClass('in',0);
        //}

        var todaysEvents = self.events.reduce(function(memo, ev) {
            if(ev.date.isSame(day, 'day')) {
                memo.push(ev);
            }
            return memo;
        }, []);

        var dayDate=day.format('YYYY-MM-DD'),
            $details=$(details).hide()
                               .data('current_day',dayDate).attr('current_day',dayDate);

            self.renderEvents(todaysEvents, details);

            //setZeroTimeout(function(){
            clEventsCalendar.replaceUrl(dayDate);

            //if($detailsBl[0]){
            arrow.style.left = el.offsetLeft + 24 + 'px';
            $week.append($detailsBl);
            setZeroTimeout(function(){
                $detailsBl.addClass('in');
                $details.addClass('in').slideDown(500,function(){
                    isOpenDay=false;
                })
            })
            //$details.slideDown(500);
            /*$detailsBl.appendTo($week);
            var h=$details.height();
            $weekAll.css({transform: 'translateY(-'+h+'px)', transition: 'none'})
            setZeroTimeout(function(){
                $details.oneTransEnd(function(){
                    isOpenDay=false;
                },'transform').addClass('in');
                $weekAll.css({transform: '', transition: 'transform .5s'})
            })

            //}
            arrow.style.left = el.offsetLeft + 24 + 'px';// - el.parentNode.offsetLeft + 24 + 'px';*/
        //})
        }, true);

        if(clEventsCalendar.highlightId){
            clEventsCalendar.highlightEvent(clEventsCalendar.highlightId);
            clEventsCalendar.highlightId=0;
        }
    }

    Calendar.prototype.getHtmlOneEvent = function(ev) {
        var cl='event event_content';
        if(ev.eventName.indexOf('event_no_item')!==-1 || ev.eventName.indexOf('btn_add_page')!==-1){
            cl='event event_empty';
        }
        var div = createElement('div', cl);
        if (ev.id) {
            div.id = 'task_' + ev.id;
        }

        //var square = createElement('div', 'event-category ' + ev.color);
        var span = createElement('div', 'bl_event', ev.eventName);

        //div.appendChild(square);
        div.appendChild(span);
        return div;
    }

    Calendar.prototype.renderEvents = function(events, ele) {
        //Remove any events in the current details element

        var self=this,
            currentWrapper = ele.querySelector('.events'),
            wrapper = createElement('div', 'events in' + (currentWrapper ? ' new' : ''));

        events.forEach(function(ev) {
            var div = self.getHtmlOneEvent(ev);
            wrapper.appendChild(div);
            clEventsCalendar.prepareEventDay($(div));
        });

        if(!events.length) {
            var div = createElement('div', 'event empty');
            var span = createElement('span', '', l('no_task'));
            div.appendChild(span);
            wrapper.appendChild(div);
        }

        if(currentWrapper) {//NOT
            currentWrapper.className = 'events out';
            currentWrapper.addEventListener('webkitAnimationEnd', function() {
                currentWrapper.parentNode.removeChild(currentWrapper);
                ele.appendChild(wrapper);
            });
            currentWrapper.addEventListener('oanimationend', function() {
                currentWrapper.parentNode.removeChild(currentWrapper);
                ele.appendChild(wrapper);
            });
            currentWrapper.addEventListener('msAnimationEnd', function() {
                currentWrapper.parentNode.removeChild(currentWrapper);
                ele.appendChild(wrapper);
            });
            currentWrapper.addEventListener('animationend', function() {
                currentWrapper.parentNode.removeChild(currentWrapper);
                ele.appendChild(wrapper);
            });
        } else {
            ele.appendChild(wrapper);
        }

  }

  Calendar.prototype.drawLegend = function() {
    var legend = createElement('div', 'legend');
    var calendars = this.events.map(function(e) {
      return e.calendar + '|' + e.color;
    }).reduce(function(memo, e) {
      if(memo.indexOf(e) === -1) {
        memo.push(e);
      }
      return memo;
    }, []).forEach(function(e) {
      var parts = e.split('|');
      var entry = createElement('span', 'entry ' +  parts[1], parts[0]);
      legend.appendChild(entry);
    });
    this.el.appendChild(legend);
  }

  Calendar.prototype.nextMonth = function() {
    var self = this;
    self.hideDay(function(){
        clEventsCalendar.eventsLoad(true,function(){
            console.log('NEXT');
            self.nextMonthDraw();
        })
    })
  }

  Calendar.prototype.nextMonthDraw = function() {
    this.current.add(1, 'months');
    this.next = true;
    this.draw();
  }

  Calendar.prototype.prevMonth = function() {
    var self = this;
    self.hideDay(function(){
        clEventsCalendar.eventsLoad(false,function(){
            console.log('PREV');
            self.prevMonthDraw();
        })
    })
  }

  Calendar.prototype.prevMonthDraw = function() {
    this.current.subtract(1, 'months');
    this.next = false;
    this.draw();
  }

  window.Calendar = Calendar;

  function createElement(tagName, className, innerText) {
    var ele = document.createElement(tagName);
    if(className) {
      ele.className = className;
    }
    if(innerText) {
      //ele.innderText = ele.textContent = innerText;
      ele.innerHTML = innerText;
    }
    return ele;
  }
}();

function initLocale() {
    var week={dow:0,doy:6}//US, Canada
    if(getSiteOption('first_day_week', 'edge_events_settings') == 'monday'){
        week={dow:1,doy:4};//ISO-8601, Europe
    }
    //week={dow:6,doy:12}//Many Arab countries
    //week={dow:1,doy:7}//Also common
    //https://momentjs.com/docs/#/customization/weekday-abbreviations/

    //l('calendar_month').split(',')
    var monthsDeclension=[
        l('january'), l('february'), l('march'), l('april'), l('may'), l('june'), l('july'),
        l('august'), l('september'), l('october'), l('november'), l('december')
    ];
    var lMonths=l('calendar_month'),
        months=lMonths.replace(/'/gi,'').split(','),
        monthsShort=[
            l('jan'), l('feb'), l('mar'), l('apr'), l('may'), l('jun'), l('jul'),
            l('aug'), l('sep'), l('oct'), l('nov'), l('dec')
        ];
    var weekdaysShort= [
        l('sun'), l('mon'), l('tue'), l('wed'), l('thu'), l('fri'), l('sat')
    ];

    //moment.updateLocale('en', {months:months, monthsShort:monthsShort, weekdaysShort:weekdaysShort, weekdaysMin:weekdaysShort, week:week})
    moment.updateLocale('en', {months:months, monthsShort:monthsDeclension, weekdaysShort:weekdaysShort, weekdaysMin:weekdaysShort, week:week})
}