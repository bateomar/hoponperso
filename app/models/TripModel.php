<?php

namespace App\Models;

use PDO;
use PDOException;

class TripModel
{
    private $pdo;

    public function __construct(PDO $pdoInstance)
    {
        $this->pdo = $pdoInstance;
        if (!($this->pdo instanceof PDO)) {
            $errorMessage = "CRITICAL: TripModel constructed without a valid PDO instance.";
            error_log($errorMessage);
            // Optional: throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * Fetches details for a specified number of the most popular upcoming trip routes.
     * Popularity is based on the count of trips for a given departure/arrival pair.
     * It then fetches one sample upcoming trip for each of these popular routes.
     *
     * @param int $limit Number of popular routes to fetch details for.
     * @return array An array of trip details.
     */
    public function getPopularRoutesDetails(int $limit = 2): array
    {
        $finalTripDetails = [];
        if (!$this->pdo) {
            error_log("TripModel::getPopularRoutesDetails() - PDO connection not available.");
            return $finalTripDetails;
        }

        try {
            // 1. Find the most popular (most frequent) departure_location/arrival_location pairs
            //    for future, scheduled trips.
            $popularPairsSql = "
                SELECT
                    departure_location,
                    arrival_location,
                    COUNT(*) AS occurrences
                FROM
                    trips
                WHERE
                    status = 'scheduled' AND departure_time > NOW()
                GROUP BY
                    departure_location, arrival_location
                ORDER BY
                    occurrences DESC
                LIMIT :limit_pairs";

            $stmtPairs = $this->pdo->prepare($popularPairsSql);
            $stmtPairs->bindParam(':limit_pairs', $limit, PDO::PARAM_INT);
            $stmtPairs->execute();
            $popularRoutes = $stmtPairs->fetchAll(PDO::FETCH_ASSOC);

            if (empty($popularRoutes)) {
                return []; // No popular routes found
            }

            // 2. For each popular pair, fetch the details of ONE upcoming sample trip.
            //    This trip should also be scheduled and in the future.
            //    We order by departure_time to get the soonest one as the sample.
            $sampleTripSql = "
                SELECT
                    t.id,
                    t.departure_location,
                    t.arrival_location,
                    t.departure_time,
                    t.price,
                    t.seats_offered,
                    t.seats_booked,
                    (t.seats_offered - t.seats_booked) AS seats_truly_available,
                    u.first_name AS driver_first_name
                    /* Add any other fields you want to display on the homepage card */
                FROM
                    trips t
                JOIN
                    users u ON t.driver_id = u.id
                WHERE
                    t.departure_location = :departure_location
                    AND t.arrival_location = :arrival_location
                    AND t.status = 'scheduled'
                    AND t.departure_time > NOW()
                ORDER BY
                    t.departure_time ASC
                LIMIT 1";

            $stmtSampleTrip = $this->pdo->prepare($sampleTripSql);

            foreach ($popularRoutes as $route) {
                $stmtSampleTrip->bindParam(':departure_location', $route['departure_location']);
                $stmtSampleTrip->bindParam(':arrival_location', $route['arrival_location']);
                $stmtSampleTrip->execute();
                $sampleTrip = $stmtSampleTrip->fetch(PDO::FETCH_ASSOC);

                if ($sampleTrip) {
                    // Check if there are actually seats available for this sample trip
                    if ($sampleTrip['seats_truly_available'] > 0) {
                        $finalTripDetails[] = $sampleTrip;
                    }
                }
            }

        } catch (PDOException $e) {
            error_log("TripModel::getPopularRoutesDetails PDOException: " . $e->getMessage());
            return []; // Return empty array on error
        } catch (\Throwable $e) {
             error_log("TripModel::getPopularRoutesDetails General Error: " . $e->getMessage());
             return [];
        }
        return $finalTripDetails;
    }


    /**
     * Fetch detailed information for a single trip, including driver and vehicle info.
     * @param int $tripId
     * @return array|false Trip details or false if not found.
     */
    public function findByIdWithDetails(int $tripId)
    {
        if (!$this->pdo) return false;
        try {
            // Join trips with users (driver) and vehicles (optional)
            $sql = "SELECT
                       t.*,
                       u.id AS driver_id, u.first_name AS driver_first_name, u.last_name AS driver_last_name,
                       u.profile_picture_url AS driver_avatar,
                       u.pref_smokes, u.pref_pets, u.pref_music, u.pref_talk, /* Driver preferences */
                       v.make AS vehicle_make, v.model AS vehicle_model, v.color AS vehicle_color, v.type AS vehicle_type
                    FROM trips t
                    JOIN users u ON t.driver_id = u.id
                    LEFT JOIN vehicles v ON u.id = v.driver_id AND v.is_default = 1 -- Join only default vehicle
                    WHERE t.id = :trip_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);
            $stmt->execute();
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            // Optionally fetch driver's average rating here as well
            if ($trip) {
                 $ratingModel = new RatingModel($this->pdo); // Assuming RatingModel exists
                 $trip['driver_average_rating'] = $ratingModel->getAverageRating($trip['driver_id']);
                 $trip['driver_ratings_count'] = $ratingModel->getRatingsCount($trip['driver_id']);
            }

            return $trip; // Returns array or false
        } catch (PDOException $e) {
            error_log("TripModel::findByIdWithDetails Error: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Increment the booked seats count for a trip.
     * @param int $tripId
     * @param int $seatsToAdd Number of seats booked (usually 1)
     * @return bool True on success, false on failure.
     */
    public function incrementBookedSeats(int $tripId, int $seatsToAdd = 1): bool
    {
        if (!$this->pdo) return false;
        // Important: Include WHERE clause to prevent booking more seats than offered
        $sql = "UPDATE trips
                SET seats_booked = seats_booked + :seats_to_add
                WHERE id = :trip_id AND (seats_booked + :seats_to_add_check <= seats_offered)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':seats_to_add', $seatsToAdd, PDO::PARAM_INT);
            $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);
            $stmt->bindParam(':seats_to_add_check', $seatsToAdd, PDO::PARAM_INT); // Use separate param for check

            $success = $stmt->execute();
            // Check if any row was actually updated (means seats were available)
            if ($success && $stmt->rowCount() > 0) {
                return true;
            } else {
                // Either the execute failed or no rows were updated (likely because seats were full)
                if (!$success) {
                    error_log("TripModel::incrementBookedSeats execute() failed. Error: " . implode(", ", $stmt->errorInfo()));
                } else {
                     error_log("TripModel::incrementBookedSeats did not update rows for trip ID {$tripId} - likely full or race condition.");
                }
                return false;
            }
        } catch (PDOException $e) {
            error_log("TripModel::incrementBookedSeats Error: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Update the status of a trip (e.g., to 'full').
     * @param int $tripId
     * @param string $newStatus
     * @return bool
     */
     public function updateStatus(int $tripId, string $newStatus): bool
     {
         if (!$this->pdo) return false;
         $sql = "UPDATE trips SET status = :status WHERE id = :trip_id";
         try {
             $stmt = $this->pdo->prepare($sql);
             $stmt->bindParam(':status', $newStatus);
             $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);
             return $stmt->execute();
         } catch (PDOException $e) {
              error_log("TripModel::updateStatus Error: " . $e->getMessage());
              return false;
         }
     }

     /**
     * Get all available trips with optional filters and sorting.
     * @param array $filters Associative array of filters (e.g., ['max_price' => 50, 'min_driver_rating' => 4, 'min_seats' => 2])
     * @param string $orderBy SQL ORDER BY clause part (e.g., "t.price ASC", "driver_avg_rating DESC")
     * @return array List of trips.
     */
    public function getAllAvailableTrips(array $filters = [], string $orderBy = 't.departure_time ASC'): array
    {
        if (!$this->pdo) return [];

        $sql = "SELECT
                    t.*,
                    u.first_name AS driver_first_name,
                    u.last_name AS driver_last_name,
                    u.profile_picture_url AS driver_avatar,
                    (SELECT AVG(r.score) FROM ratings r WHERE r.target_id = u.id) AS driver_avg_rating,
                    (SELECT COUNT(r.id) FROM ratings r WHERE r.target_id = u.id) AS driver_ratings_count,
                    (t.seats_offered - t.seats_booked) AS seats_truly_available
                FROM trips t
                JOIN users u ON t.driver_id = u.id
                WHERE t.status = 'scheduled' AND t.departure_time > NOW()"; // Only future, scheduled trips

        $params = [];

        // --- Apply Filters ---
        if (!empty($filters['max_price'])) {
            $sql .= " AND t.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        if (!empty($filters['min_seats'])) {
            // Filter by (seats_offered - seats_booked) >= min_seats
            $sql .= " AND (t.seats_offered - t.seats_booked) >= :min_seats";
            $params[':min_seats'] = (int)$filters['min_seats'];
        }
        // For driver rating, we need a subquery or HAVING clause if performance allows
        // Using HAVING here. Be mindful of performance on very large datasets without proper indexing.
        if (!empty($filters['min_driver_rating'])) {
             $sql .= " HAVING driver_avg_rating >= :min_driver_rating"; // Applied after GROUP BY (implicit here)
             $params[':min_driver_rating'] = (float)$filters['min_driver_rating'];
        }
        // Add more filters: departure_location, arrival_location, date_range, etc.
        if (!empty($filters['departure_location'])) {
            $sql .= " AND t.departure_location LIKE :departure_location";
            $params[':departure_location'] = '%' . $filters['departure_location'] . '%';
        }
        if (!empty($filters['arrival_location'])) {
            $sql .= " AND t.arrival_location LIKE :arrival_location";
            $params[':arrival_location'] = '%' . $filters['arrival_location'] . '%';
        }
         if (!empty($filters['departure_date'])) {
            $sql .= " AND DATE(t.departure_time) = :departure_date";
            $params[':departure_date'] = $filters['departure_date'];
        }


        // Add Order By
        if (!empty($orderBy)) {
            // Sanitize orderBy to prevent SQL injection if it comes from user input
            // For now, assuming it's from a controlled list
            $sql .= " ORDER BY " . $orderBy;
        } else {
            $sql .= " ORDER BY t.departure_time ASC"; // Default order
        }

        // --- DEBUG: Show SQL and Params ---
        // echo "<pre>SQL: " . htmlspecialchars($sql) . "</pre>";
        // echo "<pre>Params: "; print_r($params); echo "</pre>";
        // --- END DEBUG ---

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params); // Execute with named parameters
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("TripModel::getAllAvailableTrips Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a new trip.
     * @param array $data Trip data.
     * @return int|false Inserted trip ID or false on failure.
     */
    public function createTrip(array $data)
    {
        if (!$this->pdo) return false;
        $sql = "INSERT INTO trips (
                    driver_id, vehicle_id, departure_location, arrival_location,
                    departure_time, arrival_time_estimated, price, seats_offered,
                    trip_details, allow_instant_booking, status
                ) VALUES (
                    :driver_id, :vehicle_id, :departure_location, :arrival_location,
                    :departure_time, :arrival_time_estimated, :price, :seats_offered,
                    :trip_details, :allow_instant_booking, :status
                )";
        try {
            $stmt = $this->pdo->prepare($sql);
            // Bind parameters
            $stmt->bindParam(':driver_id', $data['driver_id'], PDO::PARAM_INT);
            $stmt->bindParam(':vehicle_id', $data['vehicle_id'], PDO::PARAM_INT); // Can be null
            $stmt->bindParam(':departure_location', $data['departure_location']);
            $stmt->bindParam(':arrival_location', $data['arrival_location']);
            $stmt->bindParam(':departure_time', $data['departure_time']);
            $stmt->bindParam(':arrival_time_estimated', $data['arrival_time_estimated']); // Can be null
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':seats_offered', $data['seats_offered'], PDO::PARAM_INT);
            $stmt->bindParam(':trip_details', $data['trip_details']); // Can be null
            $stmt->bindParam(':allow_instant_booking', $data['allow_instant_booking'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return (int)$this->pdo->lastInsertId();
            } else {
                error_log("TripModel::createTrip execute() failed. Error: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("TripModel::createTrip Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrement the booked seats count for a trip.
     * @param int $tripId
     * @param int $seatsToRelease Number of seats released (usually 1)
     * @return bool True on success, false on failure.
     */
    public function decrementBookedSeats(int $tripId, int $seatsToRelease = 1): bool
    {
        if (!$this->pdo) return false;
        // Ensure seats_booked doesn't go below zero
        $sql = "UPDATE trips
                SET seats_booked = GREATEST(0, seats_booked - :seats_to_release)
                WHERE id = :trip_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':seats_to_release', $seatsToRelease, PDO::PARAM_INT);
            $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);

            $success = $stmt->execute();
            // If seats were decremented and trip was 'full', set it back to 'scheduled'
            // (This might need more nuanced logic if you have other statuses like 'pending')
            if ($success) {
                $trip = $this->findByIdWithDetails($tripId); // Re-fetch to check status and counts
                if ($trip && $trip['status'] === 'full' && ($trip['seats_offered'] - $trip['seats_booked']) > 0) {
                    $this->updateStatus($tripId, 'scheduled');
                }
            }
            return $success;
        } catch (PDOException $e) {
            error_log("TripModel::decrementBookedSeats Error: " . $e->getMessage());
            return false;
        }
    }


    


}
?>