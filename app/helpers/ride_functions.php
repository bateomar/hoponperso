<?php
require_once 'db_connect.php';

/**
 * Get all rides with optional filters
 * 
 * @param array $filters Associative array of filters
 * @param string $sort Sort order (price_asc, price_desc, newest)
 * @param string $departure_city Departure city search term
 * @param string $arrival_city Arrival city search term
 * @param string $departure_date Optional departure date filter (YYYY-MM-DD)
 * @param string $departure_time Optional departure time filter (HH:MM)
 * @return array|bool Returns array of rides or false on failure
 */
function getRides($filters = [], $sort = 'newest', $departure_city = '', $arrival_city = '', $departure_date = '', $departure_time = '') {
    try {
        $db = connectDB();
        
        if (!$db) {
            return false;
        }
        
        $query = "SELECT t.*, 
                  u.first_name, u.last_name,
                  (SELECT AVG(r.score) FROM ratings r WHERE r.target_id = u.id) as driver_rating
                  FROM trips t
                  JOIN users u ON t.driver_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Add departure city filter if provided
        if (!empty($departure_city)) {
            $query .= " AND t.departure_location LIKE :departure_city";
            $params[':departure_city'] = '%' . $departure_city . '%';
        }
        
        // Add arrival city filter if provided
        if (!empty($arrival_city)) {
            $query .= " AND t.arrival_location LIKE :arrival_city";
            $params[':arrival_city'] = '%' . $arrival_city . '%';
        }
        
        // Add departure date filter if provided
        if (!empty($departure_date)) {
            // Filter by date part of the datetime field
            $query .= " AND DATE(t.departure_time) = :departure_date";
            $params[':departure_date'] = $departure_date;
        }
        
        // Add departure time filter if provided (with a tolerance of ±2 hours)
        if (!empty($departure_time)) {
            // Parse the provided time
            list($hours, $minutes) = explode(':', $departure_time);
            $time_seconds = ($hours * 3600) + ($minutes * 60);
            
            // Calculate time range (±2 hours)
            $min_time = max(0, $time_seconds - 7200); // 2 hours before
            $max_time = min(86400, $time_seconds + 7200); // 2 hours after
            
            $min_time_formatted = sprintf('%02d:%02d:00', floor($min_time / 3600), floor(($min_time % 3600) / 60));
            $max_time_formatted = sprintf('%02d:%02d:59', floor($max_time / 3600), floor(($max_time % 3600) / 60));
            
            // Filter by time part of the datetime field
            $query .= " AND TIME(t.departure_time) BETWEEN :min_time AND :max_time";
            $params[':min_time'] = $min_time_formatted;
            $params[':max_time'] = $max_time_formatted;
        }
        
        // Apply filters
        if (!empty($filters['min_price'])) {
            $query .= " AND t.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND t.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['min_rating'])) {
            $query .= " AND (SELECT AVG(r.score) FROM ratings r WHERE r.target_id = u.id) >= :min_rating";
            $params[':min_rating'] = $filters['min_rating'];
        }
        
        if (!empty($filters['passengers'])) {
            $query .= " AND (t.seats_offered - t.seats_booked) >= :passengers";
            $params[':passengers'] = $filters['passengers'];
        }
        
        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY t.price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY t.price DESC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY t.departure_time ASC";
                break;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $rides = $stmt->fetchAll();
        
        // Format time and duration for display
        foreach ($rides as &$ride) {
            $departure = new DateTime($ride['departure_time']);
            
            // Use arrival_time_estimated if available, otherwise calculate it
            if (!empty($ride['arrival_time_estimated'])) {
                $arrivalTime = new DateTime($ride['arrival_time_estimated']);
            } else {
                // Calculate random duration
                $durationMinutes = rand(30, 180);
                $arrivalTime = clone $departure;
                $arrivalTime->add(new DateInterval('PT' . $durationMinutes . 'M'));
            }
            
            $ride['formatted_departure'] = $departure->format('H:i');
            $ride['formatted_arrival'] = $arrivalTime->format('H:i');
            
            // Calculate duration in minutes
            $interval = $departure->diff($arrivalTime);
            $durationMinutes = ($interval->h * 60) + $interval->i;
            
            // Calculate hours and minutes
            $hours = floor($durationMinutes / 60);
            $minutes = $durationMinutes % 60;
            
            $ride['duration'] = sprintf('%dh%02dmin', $hours, $minutes);
            
            // Map field names to our application's expected structure
            $ride['depart'] = $ride['departure_location'];
            $ride['destination'] = $ride['arrival_location'];
            $ride['date_heure_depart'] = $ride['departure_time'];
            $ride['prix'] = $ride['price'];
            $ride['nombre_places_disponibles'] = $ride['seats_offered'] - $ride['seats_booked'];
            
            // Add for backward compatibility
            $ride['departure_city'] = $ride['departure_location'];
            $ride['arrival_city'] = $ride['arrival_location'];
            $ride['price'] = $ride['price'];
            $ride['available_seats'] = $ride['seats_offered'] - $ride['seats_booked'];
        }
        
        return $rides;
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get cities for autocomplete
 * 
 * @return array|bool Returns array of unique cities or false on failure
 */
function getCities() {
    try {
        $db = connectDB();
        
        if (!$db) {
            return false;
        }
        
        $query = "SELECT DISTINCT departure_location as city FROM trips 
                  UNION 
                  SELECT DISTINCT arrival_location as city FROM trips";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $cities = [];
        while ($row = $stmt->fetch()) {
            $cities[] = $row['city'];
        }
        
        return $cities;
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get minimum and maximum price for price filter
 * 
 * @return array|bool Returns min and max price or false on failure
 */
function getPriceRange() {
    try {
        $db = connectDB();
        
        if (!$db) {
            return false;
        }
        
        $query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM trips";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'min' => (int)$result['min_price'],
                'max' => (int)$result['max_price']
            ];
        }
        
        return ['min' => 0, 'max' => 100]; // Default values
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return ['min' => 0, 'max' => 100]; // Default values
    }
}
?>
