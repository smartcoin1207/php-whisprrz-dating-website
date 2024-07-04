<?php
// Include necessary files and establish a database connection here if not already done
include("./_include/core/main_start.php");

// Replace the following lines with your database connection code
$db_host = 'localhost';
$db_user = 'eric_cui';
$db_pass = 'nnsscc123456!#';
$db_name = 'eric_whisprrz_new';

// Establish a database connection
try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    // Set PDO error mode to exception
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error (e.g., log the error)
    $error_message = "Database connection failed: " . $e->getMessage();

    // Return an error response as JSON
    $response_data = [
        'error' => $error_message
    ];
    header('Content-Type: application/json');
    echo json_encode($response_data);
    exit;
}

// Your query to fetch user data
$query = "
    SELECT u.user_id, u.name, u.geo_position_lat, u.geo_position_long, u.orientation, co.title
    FROM user AS u
    LEFT JOIN const_orientation AS co ON u.orientation = co.id";

    $result = $dbh->query($query);

if (!$result) {
    // Handle the database query error here (e.g., log the error)
    $error_message = "Error executing the database query: " . $dbh->errorInfo()[2];

    // Return an error response as JSON
    $response_data = [
        'error' => $error_message
    ];
} else {
    // Initialize an empty array to store user data
    $user_data = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // Fetch user photo using User::getPhotoDefault
        $user_photo = User::getPhotoDefault($row['user_id'], "m");
    
        // Append user photo to user data
        $row['photo_url'] = $user_photo;
    
        // Add user data to the array
        $user_data[] = $row;
    }

    // Reorganize the user_data array to bring the logged-in user to the 0th position
    $loggedInUserId = $g_user['user_id']; // Replace with your session variable or user ID retrieval method

    // Find the index of the logged-in user in the array
    $loggedInUserIndex = array_search($loggedInUserId, array_column($user_data, 'user_id'));

    if ($loggedInUserIndex !== false) {
        // Move the logged-in user data to the 0th position
        $loggedInUserData = $user_data[$loggedInUserIndex];
        array_splice($user_data, $loggedInUserIndex, 1);
        array_unshift($user_data, $loggedInUserData);
    }

    // Define the $user_id variable (replace with your actual user ID)
    $user_id = $loggedInUserId; // or set it to the appropriate user ID
    
    foreach( $user_data as $user )
    {
        // Append the queries from $filp_sql to fetch additional data
        $user_id = $user['user_id']; // Get the user ID for the current user
        $filp_sql = "SELECT ui.user_id";
        $keyword = "";
        $where = "";
        $select_add = "";
        $from_add = " FROM userinfo AS ui ";
        
        $select_add .= " , vi.title AS sexuality";
        $from_add .= " LEFT JOIN var_sexuality  vi ON ui.income   = vi.id ";
        $where .= " or vi.title like '%" . $keyword . "%'";
    
        $select_add .= " , vs.title AS status_title";
        $from_add .= " LEFT JOIN var_status      vs ON ui.status   = vs.id ";
        $where .= " or vs.title LIKE '%" . $keyword . "%'";
    
        $select_add .= " , v_smoking.title AS smoking_title";
        $from_add .= " LEFT JOIN var_smoking  v_smoking ON ui.smoking   = v_smoking.id ";
        $where .= " or v_smoking.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_drinking.title AS drinking_title";
        $from_add .= " LEFT JOIN var_drinking  v_drinking ON ui.drinking   = v_drinking.id ";
        $where .= " or v_drinking.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_education.title AS education_title";
        $from_add .= " LEFT JOIN var_education  v_education ON ui.education   = v_education.id ";
        $where .= " or v_education.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_height.title AS height_title, v_height.value_cm AS height_value_cm, v_height.value_f AS height_value_f";
        $from_add .= " LEFT JOIN var_height  v_height ON ui.height   = v_height.id ";
        $where .= " or v_height.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_body.title AS body_title";
        $from_add .= " LEFT JOIN var_body  v_body ON ui.body   = v_body.id ";
        $where .= " or v_body.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_hair.title AS hair_title";
        $from_add .= " LEFT JOIN var_hair  v_hair ON ui.hair   = v_hair.id ";
        $where .= " or v_hair.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_eye.title AS eye_title";
        $from_add .= " LEFT JOIN var_eye  v_eye ON ui.eye   = v_eye.id ";
        $where .= " or v_eye.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_ethnicity.title AS ethnicity_title";
        $from_add .= " LEFT JOIN var_ethnicity  v_ethnicity ON ui.ethnicity   = v_ethnicity.id ";
        $where .= " or v_ethnicity.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_first_date.title AS first_date_title";
        $from_add .= " LEFT JOIN var_first_date  v_first_date ON ui.first_date   = v_first_date.id ";
        $where .= " or v_first_date.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_live_where.title AS live_where_title";
        $from_add .= " LEFT JOIN var_live_where  v_live_where ON ui.live_where = v_live_where.id ";
        $where .= " or v_live_where.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_living_with.title AS living_with_title";
        $from_add .= " LEFT JOIN var_living_with  v_living_with ON ui.living_with = v_living_with.id ";
        $where .= " or v_living_with.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_appearance.title AS appearance_title";
        $from_add .= " LEFT JOIN var_appearance  v_appearance ON ui.appearance   = v_appearance.id ";
        $where .= " or v_appearance.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_age_preference.title AS age_preference_title";
        $from_add .= " LEFT JOIN var_age_preference  v_age_preference ON ui.age_preference   = v_age_preference.id ";
        $where .= " or v_age_preference.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_humor.title AS humor_title";
        $from_add .= " LEFT JOIN var_humor  v_humor ON ui.humor   = v_humor.id ";
        $where .= " or v_humor.title like '%" . $keyword . "%'";
    
        $select_add .= " , v_can_you_host.title AS can_you_host_title";
        $from_add .= " LEFT JOIN var_can_you_host  v_can_you_host ON ui.can_you_host   = v_can_you_host.id ";
        $where .= " or v_can_you_host.title like '%" . $keyword . "%'";
    
        // Append the rest of your queries here
    
        $filp_sql .= $select_add . $from_add . " WHERE ui.user_id = " . $user_id;
        $row1 = DB::row($filp_sql);
    
        // Find the index of the user with the same user_id in $user_data
        $userIndex = array_search($user_id, array_column($user_data, 'user_id'));
    
        if ($userIndex !== false && $row1 != null) {
            // Merge the additional data into the user's data
            $user_data[$userIndex] = array_merge($user_data[$userIndex], $row1);
        }
        
    }
}

// Close the database connection if needed
$dbh = null;

// Return user_data as JSON
header('Content-Type: application/json');
echo json_encode($user_data);
exit;
?>
