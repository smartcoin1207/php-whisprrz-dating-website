async function initMap() {
    const mockData = await getMockData();
    console.log("mock data ===> ", mockData);

    renderUserList(mockData);

    function CustomMarker(latlng, map, imageSrc) {
        this.latlng_ = latlng;
        this.imageSrc = imageSrc; //added imageSrc
        this.setMap(map);
    }

    CustomMarker.prototype = new google.maps.OverlayView();

    CustomMarker.prototype.draw = function () {
        // Check if the div has been created.
        var div = this.div_;
        if (!div) {
            // Create a overlay text DIV
            div = this.div_ = document.createElement("div");
            // Create the DIV representing our CustomMarker
            div.className = "customMarker"; //replaced styles with className

            var img = document.createElement("img");
            img.src = this.imageSrc; //attach passed image uri
            div.appendChild(img);
            var me = this;
            div.addEventListener("click", function (event) {
                google.maps.event.trigger(me, "click");
            });
            div.addEventListener("mouseover", function (event) {
                google.maps.event.trigger(me, "mouseover");
            });
            div.addEventListener("mouseout", function (event) {
                google.maps.event.trigger(me, "mouseout");
            });

            // Then add the overlay to the DOM
            var panes = this.getPanes();
            panes.overlayImage.appendChild(div);
        }

        // Position the overlay
        var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
        if (point) {
            div.style.left = point.x + "px";
            div.style.top = point.y + "px";
        }
    };

    CustomMarker.prototype.remove = function () {
        // Check if the overlay was on the map and needs to be removed.
        if (this.div_) {
            this.div_.parentNode.removeChild(this.div_);
            this.div_ = null;
        }
    };

    CustomMarker.prototype.getPosition = function () {
        return this.latlng_;
    };

    // Center the map at 0, 0 (equator and prime meridian)
    var map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 39.8283, lng: -98.5795 },
        zoom: 4, // Zoom level for a global view
    });

    // Create markers and info windows
    var markers = [];
    var infoWindows = [];

    // render the all avatars
    mockData.forEach(function (user) {
        var marker = new CustomMarker(
            new google.maps.LatLng(
                parseFloat(user.location.coordinates.latitude),
                parseFloat(user.location.coordinates.longitude)
            ),
            map,
            user.picture.medium
        );

        var contentString = `<div><h3>${user.name.first} ${
            user.name.last
        }</h3><p>${
            user.location.street.name + " " + user.location.street.number
        } ${user.location.city} ${user.location.state} ${
            user.location.country
        }</p></div>`;

        var infoWindow = new google.maps.InfoWindow({
            content: contentString,
            pixelOffset: new google.maps.Size(0, -50),
        });

        console.log("marker ===> ", marker);

        markers.push(marker);
        infoWindows.push(infoWindow);

        marker.addListener("click", function () {
            closeAllInfoWindows();
            infoWindow.open(map, marker);
        });

        // marker.addListener("mouseout", function () {
        //     infoWindow.close();
        // });
    });

    // Function to close all info windows
    function closeAllInfoWindows() {
        infoWindows.forEach(function (infoWindow) {
            infoWindow.close();
        });
    }
}

async function getRandomUser() {
    try {
        const response = await fetch("https://randomuser.me/api/");
        const data = await response.json();
        return data.results[0];
    } catch (error) {
        console.error("Error fetching random user:", error);
    }
}

async function getMockData() {
    var mockData = [];
    for (let i = 0; i < 5; i++) {
        const user = await getRandomUser();
        mockData.push(user);
    }
    return mockData;
}

function renderUserList(mockData) {
    // Create a table element
    var table = document.createElement("table");

    // Create table header
    var thead = table.createTHead();
    var headerRow = thead.insertRow();
    var headers = ["Avatar", "User Name", "Location Name", "Email", "Phone"];
    headers.forEach(function (headerText) {
        var th = document.createElement("th");
        th.appendChild(document.createTextNode(headerText));
        headerRow.appendChild(th);
    });

    // Create table body
    var tbody = table.createTBody();

    // Populate table rows with user data
    mockData.forEach(function (user) {
        var row = tbody.insertRow();

        // Avatar
        var avatarCell = row.insertCell();
        var avatarImg = document.createElement("img");
        avatarImg.src = user.picture.thumbnail;
        avatarCell.appendChild(avatarImg);

        // User Name
        var userNameCell = row.insertCell();
        userNameCell.appendChild(
            document.createTextNode(
                `${user.name.title} ${user.name.first} ${user.name.last}`
            )
        );

        // Location Name
        var locationNameCell = row.insertCell();
        locationNameCell.appendChild(
            document.createTextNode(
                `${user.location.city}, ${user.location.country}`
            )
        );

        // Email
        var emailCell = row.insertCell();
        emailCell.appendChild(document.createTextNode(user.email));

        // Phone
        var phoneCell = row.insertCell();
        phoneCell.appendChild(document.createTextNode(user.phone));
    });

    // Append the table to the userList div
    document.getElementById("userList").appendChild(table);
}
