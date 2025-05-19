<?php

namespace App\Models;

use PDO;
use PDOException;

class BookingModel
{
    private $pdo;

    public function __construct(PDO $pdoInstance)
    {
        $this->pdo = $pdoInstance;
    }

    /**
     * Check if a user has already booked a specific trip.
     * @param int $passengerId
     * @param int $tripId
     * @return bool True if already booked, false otherwise.
     */
    public function hasBooking(int $passengerId, int $tripId): bool
    {
        if (!$this->pdo) return true; // Fail safe - prevent double booking if DB fails
        try {
            $sql = "SELECT id FROM bookings WHERE passenger_id = :passenger_id AND trip_id = :trip_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':passenger_id', $passengerId, PDO::PARAM_INT);
            $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("BookingModel::hasBooking Error: " . $e->getMessage());
            return true; // Fail safe
        }
    }

    /**
     * Create a new booking.
     * @param int $passengerId
     * @param int $tripId
     * @param int $seats // Number of seats to book (usually 1)
     * @param string $initialStatus // e.g., 'pending_confirmation' or 'confirmed'
     * @return int|false Inserted booking ID or false on failure.
     */
    public function createBooking(int $passengerId, int $tripId, int $seats = 1, string $initialStatus = 'pending_confirmation')
    {
        if (!$this->pdo) return false;
        $sql = "INSERT INTO bookings (passenger_id, trip_id, seats_booked, status, booking_date)
                VALUES (:passenger_id, :trip_id, :seats, :status, NOW())";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':passenger_id', $passengerId, PDO::PARAM_INT);
            $stmt->bindParam(':trip_id', $tripId, PDO::PARAM_INT);
            $stmt->bindParam(':seats', $seats, PDO::PARAM_INT);
            $stmt->bindParam(':status', $initialStatus);

            if ($stmt->execute()) {
                return (int)$this->pdo->lastInsertId();
            } else {
                error_log("BookingModel::createBooking execute() failed. Error: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
             // Handle potential unique constraint violation if double booking attempted despite check
             if ($e->getCode() == '23000') {
                 error_log("BookingModel::createBooking Error: Attempted double booking for passenger {$passengerId} on trip {$tripId}.");
                 // Return specific code or just false
                 return false;
             }
            error_log("BookingModel::createBooking Error: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Fetch all bookings for a specific passenger, along with trip and driver details.
     * @param int $passengerId
     * @param string $filter 'upcoming', 'past', or 'all'
     * @return array List of bookings.
     */
    public function findByPassengerIdWithDetails(int $passengerId, string $filter = 'all'): array
    {
        if (!$this->pdo) return [];

        // Base SQL query
        $sql = "SELECT
                    b.id AS booking_id,
                    b.status AS booking_status,
                    b.booking_date,
                    b.seats_booked AS booking_seats_count,
                    t.id AS trip_id,
                    t.departure_location,
                    t.arrival_location,
                    t.departure_time,
                    t.price AS trip_price,
                    t.status AS trip_status,
                    u.id AS driver_id,
                    u.first_name AS driver_first_name,
                    u.last_name AS driver_last_name,
                    u.profile_picture_url AS driver_avatar
                FROM bookings b
                JOIN trips t ON b.trip_id = t.id
                JOIN users u ON t.driver_id = u.id
                WHERE b.passenger_id = :passenger_id";

        // Apply filters based on trip timing
        if ($filter === 'upcoming') {
            $sql .= " AND t.departure_time >= NOW() AND b.status NOT IN ('cancelled_passenger', 'cancelled_driver', 'completed', 'no_show')";
            $sql .= " ORDER BY t.departure_time ASC";
        } elseif ($filter === 'past') {
            $sql .= " AND (t.departure_time < NOW() OR b.status IN ('completed', 'cancelled_passenger', 'cancelled_driver', 'no_show'))";
            $sql .= " ORDER BY t.departure_time DESC";
        } else { // 'all'
            $sql .= " ORDER BY t.departure_time DESC";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':passenger_id', $passengerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BookingModel::findByPassengerIdWithDetails Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find a specific booking by its ID and passenger ID (for security).
     * @param int $bookingId
     * @param int $passengerId
     * @return array|false Booking data or false.
     */
    public function findByIdAndPassenger(int $bookingId, int $passengerId)
    {
        if (!$this->pdo) return false;
        try {
            $sql = "SELECT b.*, t.departure_time, t.driver_id
                    FROM bookings b
                    JOIN trips t ON b.trip_id = t.id
                    WHERE b.id = :booking_id AND b.passenger_id = :passenger_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmt->bindParam(':passenger_id', $passengerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BookingModel::findByIdAndPassenger Error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Update the status of a booking.
     * @param int $bookingId
     * @param string $newStatus
     * @return bool
     */
    public function updateBookingStatus(int $bookingId, string $newStatus): bool
    {
        if (!$this->pdo) return false;
        $sql = "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :booking_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("BookingModel::updateBookingStatus Error: " . $e->getMessage());
            return false;
        }
    }
}