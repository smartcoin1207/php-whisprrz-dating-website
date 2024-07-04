    function show_face_jq(popup_usr, e) {
        var e = e || window.event;
        if (typeof e != 'undefined') {
            top_popup = e.clientY - 304,
                left_popup = e.clientX + 4;
        }
        if(e.offsetX < (70* 0.3) ) { 
            
            $('#' + popup_usr).addClass("flip-box-hover");
        } else if(e.offsetX > (70* 0.7) ) {
            $('#' + popup_usr).removeClass("flip-box-hover");
        }
    
        
        $('#' + popup_usr).css({ 'visibility': 'visible', 'left': left_popup + 'px', 'top': top_popup + 'px' })
    }

    function hide_face_jq(popup_usr) {
        $('#' + popup_usr).css('visibility', 'hidden');
    }
    function hide_face(d) {
        var obj = document.getElementById(d).style;
        obj.visibility = 'hidden';
    }