	var cursor = {x:0, y:0};
		if (navigator.appName=="Netscape")
		{
			document.captureEvents( Event.MOUSEMOVE );
			document.onmousemove = getCoord;
		}

		function getCoord( event )
		{
			cursor.x = event.pageX;
			cursor.y = event.pageY;
			
		}

		function show_face(e,d) {
		
			var obj = document.getElementById(d).style;

		    if (navigator.appName=="Netscape")
				{
					cursor.x -= 20;
					cursor.y -= 115;
				}
			else
				{
		        	var de = document.documentElement;
		        	var b = document.body;
		        	cursor.x = event.clientX + (de.scrollLeft || b.scrollLeft) - (de.clientLeft || 0) - 20;
		        	cursor.y = event.clientY + (de.scrollTop || b.scrollTop) - (de.clientTop || 0) - 110;
				}

			obj.visibility = 'visible';
			obj.left = cursor.x + 'px';
			obj.top = cursor.y + 'px';
		}

		function hide_face(d)
		{
			var obj = document.getElementById(d).style;
			obj.visibility = 'hidden';
			obj.left = '-1000px';
			obj.top = '-1000px';
			cursor.x = 0;
			cursor.y = 0;
		}
        
        function show_face_jq(popup_usr, e) {
            var e = e || window.event;
            if (typeof e != 'undefined') {
                top_popup = e.clientY - 115,
                left_popup = e.clientX - 20;
            }
            $('#'+popup_usr).css({'visibility':'visible', 'left':left_popup + 'px', 'top': top_popup  + 'px'})
		}
        
        function hide_face_jq(popup_usr)
		{
            $('#'+popup_usr).css('visibility', 'hidden');
		}