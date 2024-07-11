!function() {
    var today = moment();

    function CalendarPartyhouz(selector, partyhouz, date, day) {
        this.el = document.querySelector(selector);
        this.partyhouz = partyhouz;

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

    CalendarPartyhouz.prototype.setPartyhouz = function(partyhouz) {
        this.partyhouz = partyhouz;
        this.currentOpenDay=0;
    }

    CalendarPartyhouz.prototype.updatePartyhouz = function(partyhouz, partyhou_id) {
        if(!partyhou_id) return false;

        let partyhou  = this.partyhouz.find(item => item.id == partyhou_id);
        let newPartyhou = partyhouz.find(item => item.id == partyhou_id);
        
        if(partyhou && newPartyhou) {
            partyhou.partyhouName = newPartyhou.partyhouName;
        } else if( partyhou && !newPartyhou) {
            this.partyhouz = this.partyhouz.filter(item => item.id !== partyhou_id);
        }
    }

    CalendarPartyhouz.prototype.addPartyhouz = function(partyhouz, dayNumber, numberMore) {
        var $blPartyhouz=$('.partyhouz.in');

        if(!$blPartyhouz[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.partyhouz.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.partyhouName.indexOf('partyhou_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blPartyhouz.find('.partyhou.partyhou_empty'),
            $numberMoreBl=$('.partyhouz.in').find('.partyhou_num_bl')
            $numberMore=$('.partyhouz.in').find('.partyhou_num'),
            $numberMoreTitle=$numberMore.find('.partyhou_num_title'),
            $numberMoreEv=$numberMore.closest('.partyhou'),
            n=$numberMore.data('number');

        partyhouz.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {

                ev.date=self.current.clone().date(ev.date);
                self.partyhouz.splice(ind, 0, ev);
                ind1++;

                var $partyhou=$(self.getHtmlOnePartyhou(ev))
                    .css({display:'none', transition:'none'})
                    .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                        $partyhou.removeAttr('style');
                    });
                clPartyhouzCalendar.preparePartyhouDay($partyhou);

                n--;
                if (!n) {
                    self.partyhouz[ind1].partyhouName=self.partyhouz[ind1].partyhouName.replace(/<div class="partyhou_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');

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

    CalendarPartyhouz.prototype.addPartyhouz_OLD = function(partyhouz, dayNumber, numberMore) {
        var $blPartyhouz=$('.partyhouz.in');
        if(!$blPartyhouz[0])return;

        var self=this;

        dayNumber=this.current.clone().date(dayNumber);

        var ind=0, ind1=0;
        this.partyhouz.forEach(function(ev,i){
            if(ev.date.isSame(dayNumber, 'day') && ev.partyhouName.indexOf('partyhou_num') !== -1) {
                ind=i;
                ind1=i;
                return false;
            }
        })

        var dur=250,
            $blAdd=$blPartyhouz.find('.partyhou.partyhou_empty'),
            $numberMoreBl=$('.partyhouz.in').find('.partyhou_num_bl')
            $numberMore=$('.partyhouz.in').find('.partyhou_num'),
            $numberMoreTitle=$numberMore.find('.partyhou_num_title'),
            $numberMoreEv=$numberMore.closest('.partyhou'),
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

        partyhouz.forEach(function(ev){
            if (!$('#task_'+ev.id)[0]) {
                ev.date=self.current.clone().date(ev.date);
                self.partyhouz.splice(ind, 0, ev);
                ind1++;
                var $partyhou=$(self.getHtmlOnePartyhou(ev))
                      .css({display:'none', transition:'none'})
                      .insertBefore($blAdd).delay(10).slideDown(dur,function(){
                            $partyhou.removeAttr('style');
                      });
                clPartyhouzCalendar.preparePartyhouDay($partyhou);

                /*n--;
                if (!n) {
                    self.partyhouz[ind1].partyhouName=self.partyhouz[ind1].partyhouName.replace(/<div class="partyhou_num_bl"[^>]*?>[\s\S]*?<\/div>/i, '');
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

    CalendarPartyhouz.prototype.draw = function() {
        this.drawHeader();
        this.drawMonth();
        //this.drawLegend();
    }

    CalendarPartyhouz.prototype.drawHeader = function() {
        var self = this;
        if(!this.header) {
            //Create the header elements
            this.header = createElement('div', 'header');
            this.header.className = 'header';

            this.title = createElement('h1');

            var titleSpan = createElement('span', 'partyhou_loader');
            titleSpan.className = 'partyhou_loader';

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

  CalendarPartyhouz.prototype.drawMonth = function() {
    var self = this;
    this.partyhouz.forEach(function(ev) {
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

  CalendarPartyhouz.prototype.searchCalendar = function() {
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

        var categoryId  = searchForm.elements['partyhou_category_id']?.value;
        var calendar_month = searchForm.elements['calendar_month'].value;
        if(calendar_month.length == 1) {
            var calendar_month = '0' + calendar_month;
        }
        var calendar_year = searchForm.elements['calendar_year'].value;
        var calendar_distance = searchForm.elements['calendar_distance'].value;
        var calendar_partyhou_locked = searchForm.elements['partyhou_lock_id'].value;

        var data = 
        {
            calendar_month: calendar_month,
            calendar_year: calendar_year,
            country_id: country_id,
            state_id: state_id,
            city_id: city_id,
            partyhou_category_id: categoryId,
            partyhouz_locked: calendar_partyhou_locked,
            distance: calendar_distance,
            couple: coupleValue,
            male: maleValue,
            female: femaleValue,
            transgenderValue: transgenderValue,
            nonbinaryValue: nonbinaryValue,
        };

        clPartyhouzCalendar.calendarSearch(data, function() {
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

  CalendarPartyhouz.prototype.backFill = function() {
    var clone = this.current.clone();
    var dayOfWeek = clone.weekday();//clone.day();
    if(!dayOfWeek) { return; }
    clone.subtract(dayOfWeek+1, 'days');

    for(var i = dayOfWeek; i > 0 ; i--) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  CalendarPartyhouz.prototype.fowardFill = function() {
    var clone = this.current.clone().add(1, 'months').subtract(1, 'days');
    var dayOfWeek = clone.weekday();//clone.day();

    if(dayOfWeek === 6) { return; }

    for(var i = dayOfWeek; i < 6 ; i++) {
      this.drawDay(clone.add(1, 'days'));
    }
  }

  CalendarPartyhouz.prototype.currentMonth = function() {
    var clone = this.current.clone();

    while(clone.month() === this.current.month()) {
      this.drawDay(clone);
      clone.add(1, 'days');
    }
  }

  CalendarPartyhouz.prototype.getWeek = function(day) {
    var day=day.weekday();//clone.day();
    if(!this.week || day === 0) {
      this.week = createElement('div', 'week');
      this.month.appendChild(this.week);
    }
  }

  CalendarPartyhouz.prototype.drawDay = function(day) {
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


    //Partyhouz
    var partyhouz = createElement('div', 'day-partyhouz');
    this.drawPartyhouz(day, partyhouz);
    outer.appendChild(name);
    outer.appendChild(number);
    outer.appendChild(partyhouz);
    this.week.appendChild(outer);
  }

  CalendarPartyhouz.prototype.drawPartyhouz = function(day, element) {
    if(day.month() === this.current.month()) {
      var todaysPartyhouz = this.partyhouz.reduce(function(memo, ev) {
        if(ev.date.isSame(day, 'day')) {
          memo.push(ev);
        }
        return memo;
      }, []);

      todaysPartyhouz.forEach(function(ev) {
            /*if(ev.partyhouName.indexOf('partyhou_no_item')===-1 && ev.partyhouName.indexOf('btn_add_page')===-1){
                var evSpan = createElement('span', ev.color);
                if(!$(element).find('.'+ev.color)[0]){
                    element.appendChild(evSpan);
                }
            } else*/
            if (ev.id == 0 && ev.owners != undefined
                    && (ev.owners.my || ev.owners.other)) {
                var count=ev.owners.my + ev.owners.other,
                    cl='partyhou_color_';
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
                        var cl=key=='my'?'my_partyhou_color':'other_partyhou_color';
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

  CalendarPartyhouz.prototype.getDayClass = function(day) {
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

    CalendarPartyhouz.prototype.hideDay = function(callBack, notResetCurrentOpenDay) {
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

            $currentOpened.find('.partyhouz').addClass('out');
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
    CalendarPartyhouz.prototype.openDay = function(el,dayNumber) {
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
      //Close the open partyhouz on differnt week row
      //currentOpened && currentOpened.parentNode.removeChild(currentOpened);
     /*if(currentOpened) {
        currentOpened.addPartyhouListener('webkitAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addPartyhouListener('oanimationend', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addPartyhouListener('msAnimationEnd', function() {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addPartyhouListener('animationend', function() {
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

        var todaysPartyhouz = self.partyhouz.reduce(function(memo, ev) {
            if(ev.date.isSame(day, 'day')) {
                memo.push(ev);
            }
            return memo;
        }, []);

        var dayDate=day.format('YYYY-MM-DD'),
            $details=$(details).hide()
                               .data('current_day',dayDate).attr('current_day',dayDate);

            self.renderPartyhouz(todaysPartyhouz, details);

            //setZeroTimeout(function(){
            clPartyhouzCalendar.replaceUrl(dayDate);

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

        if(clPartyhouzCalendar.highlightId){
            clPartyhouzCalendar.highlightPartyhou(clPartyhouzCalendar.highlightId);
            clPartyhouzCalendar.highlightId=0;
        }
    }

    CalendarPartyhouz.prototype.getHtmlOnePartyhou = function(ev) {
        var cl='partyhou partyhou_content';
        if(ev.partyhouName.indexOf('partyhou_no_item')!==-1 || ev.partyhouName.indexOf('btn_add_page')!==-1){
            cl='partyhou partyhou_empty';
        }
        var div = createElement('div', cl);
        if (ev.id) {
            div.id = 'task_'+ev.id;
        }

        //var square = createElement('div', 'partyhou-category ' + ev.color);
        var span = createElement('div', 'bl_partyhou', ev.partyhouName);

        //div.appendChild(square);
        div.appendChild(span);
        return div;
    }

    CalendarPartyhouz.prototype.renderPartyhouz = function(partyhouz, ele) {
        //Remove any partyhouz in the current details element

        var self=this,
            currentWrapper = ele.querySelector('.partyhouz'),
            wrapper = createElement('div', 'partyhouz in' + (currentWrapper ? ' new' : ''));

        partyhouz.forEach(function(ev) {
            var div = self.getHtmlOnePartyhou(ev);
            wrapper.appendChild(div);
            clPartyhouzCalendar.preparePartyhouDay($(div));
        });

        if(!partyhouz.length) {
            var div = createElement('div', 'partyhou empty');
            var span = createElement('span', '', l('no_task'));
            div.appendChild(span);
            wrapper.appendChild(div);
        }

        if(currentWrapper) {//NOT
            currentWrapper.className = 'partyhouz out';
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

  CalendarPartyhouz.prototype.drawLegend = function() {
    var legend = createElement('div', 'legend');
    var calendars = this.partyhouz.map(function(e) {
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

  CalendarPartyhouz.prototype.nextMonth = function() {
    var self = this;
    self.hideDay(function(){
        clPartyhouzCalendar.partyhouzLoad(true,function(){
            console.log('NEXT');
            self.nextMonthDraw();
        })
    })
  }

  CalendarPartyhouz.prototype.nextMonthDraw = function() {
    this.current.add(1, 'months');
    this.next = true;
    this.draw();
  }

  CalendarPartyhouz.prototype.prevMonth = function() {
    var self = this;
    self.hideDay(function(){
        clPartyhouzCalendar.partyhouzLoad(false,function(){
            console.log('PREV');
            self.prevMonthDraw();
        })
    })
  }

  CalendarPartyhouz.prototype.prevMonthDraw = function() {
    this.current.subtract(1, 'months');
    this.next = false;
    this.draw();
  }

  window.CalendarPartyhouz = CalendarPartyhouz;

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