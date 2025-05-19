<?php

namespace App\Models;

use PDO;
use PDOException;

class RatingModel
{
    private $pdo;

    public function __construct(PDO $pdoInstance)
    {
        $this->pdo = $pdoInstance;
    }

    /**
     * Find ratings received by a specific user.
     * Joins with users table to get rater's name.
     * @param int $targetUserId The user whose ratings are being fetched.
     * @param int $limit Max number of ratings to return.
     * @param int $offset Starting offset for pagination.
     * @return array List of ratings or empty array.
     */
    public function findByTargetId(int $targetUserId, int $limit = 10, int $offset = 0): array
    {
        if (!$this->pdo) return [];
        try {
            // Select rating details and the first name of the rater
            $sql = "SELECT r.*, u.first_name as rater_first_name
                    FROM ratings r
                    JOIN users u ON r.rater_id = u.id
                    WHERE r.target_id = :target_id
                    ORDER BY r.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':target_id', $targetUserId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("RatingModel::findByTargetId Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the total count of ratings received by a user.
     * @param int $targetUserId
     * @return int
     */
     public function getRatingsCount(int $targetUserId): int
     {
        if (!$this->pdo) return 0;
         try {
             $sql = "SELECT COUNT(*) FROM ratings WHERE target_id = :target_id";
             $stmt = $this->pdo->prepare($sql);
             $stmt->bindParam(':target_id', $targetUserId, PDO::PARAM_INT);
             $stmt->execute();
             return (int)$stmt->fetchColumn();
         } catch (PDOException $e) {
             error_log("RatingModel::getRatingsCount Error: " . $e->getMessage());
             return 0;
         }
     }

    /**
     * Calculate the average rating score for a user.
     * @param int $targetUserId
     * @return float
     */
     public function getAverageRating(int $targetUserId): float
     {
         if (!$this->pdo) return 0.0;
         try {
             $sql = "SELECT AVG(score) FROM ratings WHERE target_id = :target_id";
             $stmt = $this->pdo->prepare($sql);
             $stmt->bindParam(':target_id', $targetUserId, PDO::PARAM_INT);
             $stmt->execute();
             $avg = $stmt->fetchColumn();
             return $avg ? (float)$avg : 0.0; // Return float or 0.0 if no ratings
         } catch (PDOException $e) {
             error_log("RatingModel::getAverageRating Error: " . $e->getMessage());
             return 0.0;
         }
     }
}