<!DOCTYPE html>
<html>
<head>
    <title>Populating Google Map</title>
    <style>
        /* Set the map canvas size */
        #mapCanvas {
            width: 100%;
            height: 500px;
        }
    </style>
</head>
<body>
    <div>
        <label for="distanceSelect">Select Distance:</label>
        <select id="distanceSelect">
            <option value="all">All</option>
            <option value="10">50 miles</option>
            <option value="40">100 miles</option>
            <option value="80">150 miles</option>
        </select>
    </div>
    <div id="mapCanvas"></div>

    <script>
        // Data: Array of markers with name, latitude, and longitude
        var markers = [
            {'user_id': '0', 'geo_position_lat': '39.8283', 'geo_position_long': '-98.5795'},
{'user_id': '1', 'geo_position_lat': '26.936684579371256', 'geo_position_long': '67.66259490273139'},
{'user_id': '2', 'geo_position_lat': '24.7326273067383', 'geo_position_long': '66.62133394081398'},
{'user_id': '3', 'geo_position_lat': '25.589406251636554', 'geo_position_long': '66.43471437221646'},
{'user_id': '4', 'geo_position_lat': '26.36420405660004', 'geo_position_long': '66.95179338262496'},
{'user_id': '5', 'geo_position_lat': '24.85012138754622', 'geo_position_long': '65.13103585878687'},
{'user_id': '6', 'geo_position_lat': '24.950225707545936', 'geo_position_long': '67.09978271240057'},
{'user_id': '7', 'geo_position_lat': '23.955139083784278', 'geo_position_long': '67.48616296424736'},
{'user_id': '8', 'geo_position_lat': '25.725414708899397', 'geo_position_long': '66.52416034758481'},
{'user_id': '9', 'geo_position_lat': '24.73196172700539', 'geo_position_long': '67.66890238942382'},
{'user_id': '10', 'geo_position_lat': '26.477307827730964', 'geo_position_long': '67.46410718229471'},
{'user_id': '11', 'geo_position_lat': '23.69714692488829', 'geo_position_long': '66.21720866391334'},
{'user_id': '12', 'geo_position_lat': '26.030543314164447', 'geo_position_long': '68.20927883752914'},
{'user_id': '13', 'geo_position_lat': '26.626575152570524', 'geo_position_long': '67.24440086021283'},
{'user_id': '14', 'geo_position_lat': '25.02768009289745', 'geo_position_long': '68.87722872585685'},
{'user_id': '15', 'geo_position_lat': '24.566900034097248', 'geo_position_long': '66.03114975069356'}
        ];

    var convertedData = markers.map(user => {
    return [user.user_id, parseFloat(user.geo_position_lat), parseFloat(user.geo_position_long)];
});

        var mainMarker = convertedData[0]; // Main marker coordinates
        var map; // Declare map variable
        var markersArray = []; // Array to store markers

        // Function to initialize the map
        function initMap() {
            map = new google.maps.Map(document.getElementById('mapCanvas'), {
                zoom: 8,
                center: { lat: mainMarker[1], lng: mainMarker[2] },
            });
            createMarkers('all'); // Initialize markers with 'All' selected by default
        }

        // Function to create markers on the map
        function createMarkers(distance) {
    // Clear existing markers
    clearMarkers();

    for (var i = 0; i < convertedData.length; i++) {
        if (distance === 'all' || calculateDistance(mainMarker[1], mainMarker[2], convertedData[i][1], convertedData[i][2]) <= distance) {
            var marker = new google.maps.Marker({
                position: { lat: convertedData[i][1], lng: convertedData[i][2] },
                map: map,
                title: convertedData[i][0]
            });
            markersArray.push(marker);
        }
    }
}

        // Function to calculate distance between two sets of latitude and longitude coordinates in miles
        function calculateDistance(lat1, lon1, lat2, lon2) {
            var earthRadius = 3959; // Earth's radius in miles
            var lat1Rad = toRadians(lat1);
            var lat2Rad = toRadians(lat2);
            var deltaLat = toRadians(lat2 - lat1);
            var deltaLon = toRadians(lon2 - lon1);

            var a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
                    Math.cos(lat1Rad) * Math.cos(lat2Rad) *
                    Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);

            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            var distance = earthRadius * c;
            return distance;
        }

        // Function to convert degrees to radians
        function toRadians(degrees) {
            return degrees * (Math.PI / 180);
        }

        // Function to clear markers from the map
        function clearMarkers() {
            for (var i = 0; i < markersArray.length; i++) {
                markersArray[i].setMap(null);
            }
            markersArray = [];
        }

        // Event listener for the distance dropdown
        document.getElementById('distanceSelect').addEventListener('change', function () {
            var selectedDistance = this.value;
            createMarkers(selectedDistance == 'all' ? selectedDistance : parseInt(selectedDistance));
        });

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA7jXWz-DIWzQ-K5d1hehpvnLeylVCB2y0&callback=initMap"></script>

    <!-- Include the Google Maps JavaScript API with callback parameter -->
</body>
</html>
