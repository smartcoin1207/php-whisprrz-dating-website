<?php

//senior-dev-1019 2024-10-30

$host = "localhost";
$dbname = "eric_whisprrz_new";
$username = "eric_cui";
$password = "nnsscc123456!#";

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT m.*, partyhouz_open.partyhou_ids, partyhouz_open.open_partyhouz_id, partyhouz_open.resets 
    		FROM partyhouz_partyhou AS m 
    		LEFT JOIN partyhouz_open ON FIND_IN_SET(m.partyhou_id, partyhouz_open.partyhou_ids) 
    		WHERE m.is_open_partyhouz = 1 
    		AND partyhouz_open.is_disabled = 0 
    		AND TIMESTAMPDIFF(MINUTE, m.partyhou_datetime , NOW()) >= partyhouz_open.resets";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
    	$partyhou_id = $row['partyhou_id'];
    	$open_partyhouz_id = $row['open_partyhouz_id']; 
    	$partyhou_ids = explode(',', $row['partyhou_ids']);
    	$resets = $row['resets'];
    	if($partyhou_id == end($partyhou_ids)) {
    		unset($row['partyhou_id']);
    		unset($row['open_partyhouz_id']);
    		unset($row['partyhou_ids']);
    		unset($row['resets']);

 			$originalDatetime = new DateTime($row['partyhou_datetime']);
    		$originalDatetime->modify('+' . $resets . ' minutes');

	        // Update the row with the adjusted datetime
	        $row['partyhou_datetime'] = $originalDatetime->format('Y-m-d H:i:s');
	        $columns = implode(", ", array_keys($row));
	        $placeholders = ":" . implode(", :", array_keys($row));

	        $sql = "INSERT INTO partyhouz_partyhou ($columns) VALUES ($placeholders)";

	        $insertStmt = $pdo->prepare($sql);
	        
	        $insertStmt->execute($sql);

	        $new_partyhou_id = $pdo->lastInsertId();
	    	if($new_partyhou_id){
	    		array_push($partyhou_ids, $new_partyhou_id);
	    		if( count($partyhou_ids) > 20 ) {
	    			$partyhou_ids = array_slice($partyhou_ids, 10);
	    		}
	    		$partyhou_ids = implode(",", $partyhou_ids);
	            $updateSql = "UPDATE partyhouz_open 
	                          SET partyhou_ids = :partyhou_ids
	                          WHERE open_partyhouz_id = :open_partyhouz_id";

	            $updateStmt = $pdo->prepare($updateSql);
	            $updateStmt->bindParam(':partyhou_ids', $partyhou_ids);
	            $updateStmt->bindParam(':open_partyhouz_id', $open_partyhouz_id);
	            $updateStmt->execute();
	        }
    	}
    }
	echo "All Tables updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;

