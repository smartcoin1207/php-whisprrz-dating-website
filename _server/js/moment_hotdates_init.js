!function() {
    var today = moment();

    function CalendarHotdates(selector, hotdates, date, day) {
        this.el = document.querySelector(selector);
        this.hotdates = hotdates;

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

    CalendarHotdates.prototype.setHotdates = function(hotdates) {
        this.hotdates = hotdates;
        this.currentOpenDay=0;
    }

    CalendarHotdates.prototype.updateHotdates = function(hotdates, hotdate_id) {
        if(!hotdate_id) return false;

        let hotdate  = this.hotdates.find(item => item.id == hotdate_id);
        let newHotdate = hotdates.find(item => item.id == hotdate_id);
        
        if(hotdate && newHotdate) {
            hotdate.hotdateName = newHotdate.hotdateName;
        } else if( hotdate && !newHotdate ) {
            this.hotdates = this.hotdates.filter(item => item.id !== hotdate_id);
        }
    }

    CalendarHotdates.prototype.addHotdates = function(hotdates, dayNumber, numberMore) {
        var $blHotdates=$('.hotdates.in');

        if(!$blHotdates[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.hotdates.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.hotdateName.indexOf('hotdate_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blHotdates.find('.hotdate.hotdate_empty'),
            $numberMoreBl=$('.hotdates.in').find('.hotdate_num_bl')
            $numberMore=$('.hotdates.in').find('.hotdate_num'),
            $numberMoreTitle=$numberMore.find('.hotdate_num_title'),
            $numberMoreEv=$numberMore.closest('.hotdate'),
            n=$numberMore.data('number');

        hotdates.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {

                ev.date=self.current.clone().date(ev.date);
                self.hotdates.splice(ind, 0, ev);
                ind1++;

                var $hotdate=$(self.getHtmlOneHotdate(ev))
                    .css({display:'none', transition:'none'})
                    .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                        $hotdate.removeAttr('style');
                    });
                clHotdatesCalendar.prepareHotdateDay($hotdate);

                n--;
                if (!n) {
                    self.hotdates[ind1].hotdateName=self.hotdates[ind1].hotdateName.replace(/<div class="hotdate_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');

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

    CalendarHotdates.prototype.addHotdates_OLD = function(hotdates, dayNumber, numberMore) {
        var $blHotdates=$('.hotdates.in');
        if(!$blHotdates[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.hotdates.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.hotdateName.indexOf('hotdate_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blHotdates.find('.hotdate.hotdate_empty'),
            $numberMoreBl=$('.hotdates.in').find('.hotdate_num_bl')
            $numberMore=$('.hotdates.in').find('.hotdate_num'),
            $numberMoreTitle=$numberMore.find('.hotdate_num_title'),
            $numberMoreEv=$numberMore.closest('.hotdate'),
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

        hotdates.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {
                ev.date=self.current.clone().date(ev.date);
                self.hotdates.splice(ind, 0, ev);
                ind1++;
                var $hotdate=$(self.getHtmlOneHotdate(ev))
                      .css({display:'none', transition:'none'})
                      .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                            $hotdate.removeAttr('style');
                      });
                clHotdatesCalendar.prepareHotdateDay($hotdate);

                /*n--;
                if (!n) {
                    self.hotdates[ind1].hotdateName=self.hotdates[ind1].hotdateName.replace(/<div class="hotdate_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');
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

    CalendarHotdates.prototype.draw = function() {
        this.drawHeader();
        this.drawMonth();
        //this.drawLegend();
    }

    CalendarHotdates.prototype.drawHeader = function() {
        var self = this;
        if(!this.header) {
            //Create the header elements
            this.header = createElement('div', 'header');
            this.header.className = 'header';

            this.title = createElement('h1');

            var titleSpan = createElement('span', 'hotdate_loader');
            titleSpan.className = 'hotdate_loader';

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

  CalendarHotdates.prototype.drawMonth = function() {
    var self = this;
    this.hotdates.forEach(function(ev) {
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

CalendarHotdates.prototype.searchCalendar = function() {
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

        var categoryId  = searchForm.elements['hotdate_category_id']?.value;
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
            hotdate_category_id: categoryId,
            distance: calendar_distance,
            couple: coupleValue,
            male: maleValue,
            female: femaleValue,
            transgender: transgenderValue,
            nonbinary: nonbinaryValue
        };

        clHotdatesCalendar.calendarSearch(data, function() {
            console.log('calendar search');

            var date = calendar_year + "-" + calendar_month + "-01";

            if (date) {
                self.current = moment(date, "YYYY-MM-DD", false).date(1);
            } else {
                self.current = moment().date(1);
            }
            
            self.draw();

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
        })
    });
  }


  CalendarHotdates.prototype.backFill = function() {
    var clone = this.current.clone();
    var dayOfWeek = clone.weekday();//clone.day();
    if(!dayOfWeek) { return; }
    clone.subtract(dayOfWeek+1, 'days');

    for(var i = dayOfWeek; i > 0 ; i--) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  CalendarHotdates.prototype.fowardFill = function() {
    var clone = this.current.clone().add(1, 'months').subtract(1, 'days');
    var dayOfWeek = clone.weekday();//clone.day();

    if(dayOfWeek === 6) { return; }

    for(var i = dayOfWeek; i < 6 ; i++) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  CalendarHotdates.prototype.currentMonth = function() {
    var clone = this.current.clone();

    while(clone.month() === this.current.month()) {
      this.drawDay(clone);
      clone.add(1, 'days');
    }
  }

  CalendarHotdates.prototype.getWeek = function(day) {
    var day=day.weekday();//clone.day();
    if(!this.week || day === 0) {
      this.week = createElement('div', 'week');
      this.month.appendChild(this.week);
    }
  }

  CalendarHotdates.prototype.drawDay = function(day) {
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


    //Hotdates
    var hotdates = createElement('div', 'day-hotdates');
    this.drawHotdates(day, hotdates);
    outer.appendChild(name);
    outer.appendChild(number);
    outer.appendChild(hotdates);
    this.week.appendChild(outer);
  }

  CalendarHotdates.prototype.drawHotdates = function(day, element) {
    if(day.month() === this.current.month()) {
      var todaysHotdates = this.hotdates.reduce(function(memo, ev) {
        if(ev.date.isSame(day, 'day')) {
          memo.push(ev);
        }
        return memo;
      }, []);

      todaysHotdates.forEach(function(ev) {
            /*if(ev.hotdateName.indexOf('hotdate_no_item')===-1 && ev.hotdateName.indexOf('btn_add_page')===-1){
                var evSpan = createElement('span', ev.color);
                if(!$(element).find('.'+ev.color)[0]){
                    element.appendChild(evSpan);
                }
            } else*/
            if (ev.id == 0 && ev.owners != undefined
                    && (ev.owners.my || ev.owners.other)) {
                var count=ev.owners.my + ev.owners.other,
                    cl='hotdate_color_';
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
                        var cl=key=='my'?'my_hotdate_color':'other_hotdate_color';
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

  CalendarHotdates.prototype.getDayClass = function(day) {
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

    CalendarHotdates.prototype.hideDay = function(callBack, notResetCurrentOpenDay) {
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

            $currentOpened.find('.hotdates').addClass('out');
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
    CalendarHotdates.prototype.openDay = function(el,dayNumber) {
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
      //Close the open hotdates on differnt week row
      //currentOpened && currentOpened.parentNode.removeChild(currentOpened);
     /*if(currentOpened) {
        currentOpened.addHotdateListener('webkitAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addHotdateListener('oanimationend', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addHotdateListener('msAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addHotdateListener('animationend', function() {
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

        var todaysHotdates = self.hotdates.reduce(function(memo, ev) {
            if(ev.date.isSame(day, 'day')) {
                memo.push(ev);
            }
            return memo;
        }, []);

        var dayDate=day.format('YYYY-MM-DD'),
            $details=$(details).hide()
                               .data('current_day',dayDate).attr('current_day',dayDate);

            self.renderHotdates(todaysHotdates, details);

            //setZeroTimeout(function(){
            clHotdatesCalendar.replaceUrl(dayDate);

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

        if(clHotdatesCalendar.highlightId){
            clHotdatesCalendar.highlightHotdate(clHotdatesCalendar.highlightId);
            clHotdatesCalendar.highlightId=0;
        }
    }

    CalendarHotdates.prototype.getHtmlOneHotdate = function(ev) {
        var cl='hotdate hotdate_content';
        if(ev.hotdateName.indexOf('hotdate_no_item')!==-1 || ev.hotdateName.indexOf('btn_add_page')!==-1){
            cl='hotdate hotdate_empty';
        }
        var div = createElement('div', cl);
        if (ev.id) {
            div.id = 'task_'+ev.id;
        }

        //var square = createElement('div', 'hotdate-category ' + ev.color);
        var span = createElement('div', 'bl_hotdate', ev.hotdateName);

        //div.appendChild(square);
        div.appendChild(span);
        return div;
    }

    CalendarHotdates.prototype.renderHotdates = function(hotdates, ele) {
        //Remove any hotdates in the current details element

        var self=this,
            currentWrapper = ele.querySelector('.hotdates'),
            wrapper = createElement('div', 'hotdates in' + (currentWrapper ? ' new' : ''));

        hotdates.forEach(function(ev) {
            var div = self.getHtmlOneHotdate(ev);
            wrapper.appendChild(div);
            clHotdatesCalendar.prepareHotdateDay($(div));
        });

        if(!hotdates.length) {
            var div = createElement('div', 'hotdate empty');
            var span = createElement('span', '', l('no_task'));
            div.appendChild(span);
            wrapper.appendChild(div);
        }

        if(currentWrapper) {//NOT
            currentWrapper.className = 'hotdates out';
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

  CalendarHotdates.prototype.drawLegend = function() {
    var legend = createElement('div', 'legend');
    var calendars = this.hotdates.map(function(e) {
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

  CalendarHotdates.prototype.nextMonth = function() {
    var self = this;
    self.hideDay(function(){
        clHotdatesCalendar.hotdatesLoad(true,function(){
            console.log('NEXT');
            self.nextMonthDraw();
        })
    })
  }

  CalendarHotdates.prototype.nextMonthDraw = function() {
    this.current.add(1, 'months');
    this.next = true;
    this.draw();
  }

  CalendarHotdates.prototype.prevMonth = function() {
    var self = this;
    self.hideDay(function(){
        clHotdatesCalendar.hotdatesLoad(false,function(){
            console.log('PREV');
            self.prevMonthDraw();
        })
    })
  }

  CalendarHotdates.prototype.prevMonthDraw = function() {
    this.current.subtract(1, 'months');
    this.next = false;
    this.draw();
  }

  window.CalendarHotdates = CalendarHotdates;

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