var nd_images_list = [];
var updateCount = 5;
var currentCount = 0;
// nudity image change function
function nd_image_change() {
    currentCount = 0;
    // Select all image elements on the page
    if ($('#nd_change_filter').prop('checked')) {
        const image = document.getElementsByTagName('img');
        // Loop through each image and update its source attribute
        nd_images_list = [];
        for (let i = 0; i < image.length; i++) {
            if (image[i].width > 20 && image[i].height > 20) {
                if(image[i].src.includes("usr-more.png")) continue;
                if(image[i].closest('.bl_logo #logo')) continue;
                if(image[i].src.includes("rec_audio.png")) continue;
                if(image[i].closest('.bg_fon')) continue;
                if(image[i].src.includes("main_Popcorn_Edge.png")) continue;
                if(image[i].src.includes("zoom_out.png")) continue;
                if(image[i].src.includes("zoom_in.png")) continue;
                if(image[i].src.includes("zoom_out.png")) continue;
                if(image[i].src.includes("/logo/")) continue;
                if(!(image[i].src.includes("_files/"))) continue;

                //game
                if(image[i].closest('.games_check')) continue;
                var obj = {img_obj: image[i], img_src: image[i].src};    
                nd_images_list.push(obj);
                const randomNumber = Math.floor(Math.random() * 20) + 1;
                image[i].src = "_server/betty_bears/betty ("+ randomNumber +").jpg";
            }
        }
    } else{
        for (let i = 0; i < nd_images_list.length; i++) {
            nd_images_list[i].img_obj.src = nd_images_list[i].img_src;
        }
    }
}

function update_image_interval() {
    if(currentCount < updateCount) {
        // Select all image elements on the page
        if ($('#nd_change_filter').prop('checked')) {
            const image = document.getElementsByTagName('img');
            // Loop through each image and update its source attribute
            for (let i = 0; i < image.length; i++) {
                if (image[i].width > 20 && image[i].height > 20) {
                    if(image[i].src.includes("usr-more.png")) continue;
                    if(image[i].closest('.bl_logo #logo')) continue;
                    if(image[i].src.includes("rec_audio.png")) continue;
                    if(image[i].closest('.bg_fon')) continue;
                    if(image[i].src.includes("main_Popcorn_Edge.png")) continue;
                    if(image[i].src.includes("zoom_out.png")) continue;
                    if(image[i].src.includes("zoom_in.png")) continue;
                    if(image[i].src.includes("/logo/")) continue;
                    if(!(image[i].src.includes("_files/"))) continue;

                    //game
                    if(image[i].closest('.games_check')) continue;
                    var obj = {img_obj: image[i], img_src: image[i].src};    
                    nd_images_list.push(obj);
                    const randomNumber = Math.floor(Math.random() * 20) + 1;

                    image[i].src = "_server/betty_bears/betty ("+ randomNumber +").jpg";
                }
            }
        }
    }
    currentCount++;
}

nd_image_change();
setInterval(update_image_interval, 1000);

//when nd filter button clicked
document.querySelector('#nd_change_filter').addEventListener('change', function() {
    if ($('#nd_change_filter').prop('checked')) {
          xajax_nd_filter_change('checked');
              nd_image_change();
    } else {
      xajax_nd_filter_change('');
      nd_image_change();
    }
});