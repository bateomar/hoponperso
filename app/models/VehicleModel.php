<?php

namespace App\Models;

use PDO;
use PDOException;

class VehicleModel
{
    private $pdo;

    public function __construct(PDO $pdoInstance)
    {
        $this->pdo = $pdoInstance;
    }

    /**
     * Find all vehicles belonging to a specific driver.
     * @param int $driverId
     * @return array List of vehicles or empty array.
     */
    public function findByDriverId(int $driverId): array
    {
        if (!$this->pdo) return [];
        try {
            $sql = "SELECT * FROM vehicles WHERE driver_id = :driver_id ORDER BY is_default DESC, make ASC, model ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VehicleModel::findByDriverId Error: " . $e->getMessage());
            return [];
        }
    }

     /**
     * Find a specific vehicle by its ID and driver ID (for security).
     * @param int $vehicleId
     * @param int $driverId
     * @return array|false Vehicle data or false if not found/not owned.
     */
    public function findByIdAndDriver(int $vehicleId, int $driverId)
    {
        if (!$this->pdo) return false;
        try {
            $sql = "SELECT * FROM vehicles WHERE id = :id AND driver_id = :driver_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VehicleModel::findByIdAndDriver Error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Add a new vehicle for a driver.
     * @param int $driverId
     * @param array $data Vehicle data (make, model, color, year, seats_available, etc.)
     * @return int|false Inserted vehicle ID or false on failure.
     */
    public function add(int $driverId, array $data)
    {
        if (!$this->pdo) return false;
        $sql = "INSERT INTO vehicles (driver_id, make, model, color, year, type, license_plate, seats_available, is_default)
                VALUES (:driver_id, :make, :model, :color, :year, :type, :license_plate, :seats_available, :is_default)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            $stmt->bindParam(':make', $data['make']);
            $stmt->bindParam(':model', $data['model']);
            $stmt->bindParam(':color', $data['color']);
            $stmt->bindParam(':year', $data['year']); // Ensure this is handled correctly (null or year)
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':license_plate', $data['license_plate']);
            $stmt->bindParam(':seats_available', $data['seats_available'], PDO::PARAM_INT);
            $stmt->bindParam(':is_default', $data['is_default'], PDO::PARAM_INT); // Bind as int

            if ($stmt->execute()) {
                return (int)$this->pdo->lastInsertId();
            } else {
                 error_log("VehicleModel::add execute() failed. Error: " . implode(", ", $stmt->errorInfo()));
                 return false;
            }
        } catch (PDOException $e) {
            error_log("VehicleModel::add Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing vehicle.
     * @param int $vehicleId
     * @param int $driverId (for verification)
     * @param array $data Vehicle data
     * @return bool True on success, false on failure.
     */
    public function update(int $vehicleId, int $driverId, array $data): bool
    {
         if (!$this->pdo) return false;
        // Similar to updateProfile, build SET clause dynamically from allowed fields
        $allowedFields = ['make', 'model', 'color', 'year', 'type', 'license_plate', 'seats_available', 'is_default'];
        $updateFields = [];
        $updateValues = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "`" . $field . "` = :" . $field;
                $updateValues[':' . $field] = ($field === 'is_default') ? (int)$data[$field] : $data[$field]; // Cast boolean/flag to int
                 if ($field === 'year' && empty($data[$field])) $updateValues[':' . $field] = null; // Handle empty year
            }
        }

        if (empty($updateFields)) return false; // Nothing to update

        $updateValues[':id'] = $vehicleId;
        $updateValues[':driver_id'] = $driverId; // For WHERE clause verification

        $sql = "UPDATE vehicles SET " . implode(', ', $updateFields) . " WHERE id = :id AND driver_id = :driver_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($updateValues);
        } catch (PDOException $e) {
            error_log("VehicleModel::update Error: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Delete a vehicle owned by the driver.
     * @param int $vehicleId
     * @param int $driverId
     * @return bool
     */
    public function delete(int $vehicleId, int $driverId): bool
    {
         if (!$this->pdo) return false;
         $sql = "DELETE FROM vehicles WHERE id = :id AND driver_id = :driver_id";
         try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            return $stmt->execute();
         } catch (PDOException $e) {
             error_log("VehicleModel::delete Error: " . $e->getMessage());
             return false;
         }
    }

     /**
      * Set a specific vehicle as the default, unsetting others for the user.
      * @param int $vehicleId
      * @param int $driverId
      * @return bool
      */
      public function setDefault(int $vehicleId, int $driverId): bool
      {
          if (!$this->pdo) return false;
          try {
              $this->pdo->beginTransaction();

              // Unset default for all other vehicles of this driver
              $sqlUnset = "UPDATE vehicles SET is_default = 0 WHERE driver_id = :driver_id AND id != :vehicle_id";
              $stmtUnset = $this->pdo->prepare($sqlUnset);
              $stmtUnset->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
              $stmtUnset->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);
              $stmtUnset->execute();

              // Set default for the specified vehicle
              $sqlSet = "UPDATE vehicles SET is_default = 1 WHERE id = :vehicle_id AND driver_id = :driver_id_check";
              $stmtSet = $this->pdo->prepare($sqlSet);
              $stmtSet->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);
              $stmtSet->bindParam(':driver_id_check', $driverId, PDO::PARAM_INT);
              $stmtSet->execute();

              return $this->pdo->commit();

          } catch (PDOException $e) {
              $this->pdo->rollBack();
              error_log("VehicleModel::setDefault Error: " . $e->getMessage());
              return false;
          }
      }
}