<?php
require_once 'app/core/Model.php';

/**
 * User Model
 * 
 * Handles database operations for users
 */
class UserModel extends Model
{
    protected $table = 'users';
    
    /**
     * Find a user by email
     *
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function findByEmail($email)
    {
        return $this->findOneBy('email', $email);
    }
    
    /**
     * Find a user with their ratings
     *
     * @param int $userId User ID
     * @return array|false User data with ratings or false if not found
     */
    public function findWithRatings($userId)
    {
        $query = "
            SELECT u.*,
                COALESCE(AVG(r.score), 0) AS avg_rating,
                COUNT(r.id) AS rating_count
            FROM users u
            LEFT JOIN ratings r ON u.id = r.rated_user_id
            WHERE u.id = ?
            GROUP BY u.id
        ";
        
        return $this->db->fetchOne($query, [$userId]);
    }
    
    /**
     * Get detailed user profile
     *
     * @param int $userId User ID
     * @return array User profile data
     */
    public function getDetailedProfile($userId)
    {
        // Get user with ratings
        $user = $this->findWithRatings($userId);
        
        if (!$user) {
            return false;
        }
        
        // Get user's vehicles
        $query = "SELECT * FROM vehicles WHERE user_id = ?";
        $vehicles = $this->db->fetchAll($query, [$userId]);
        
        // Get number of trips as driver
        $query = "SELECT COUNT(*) as trip_count FROM trips WHERE driver_id = ?";
        $tripsAsDriver = $this->db->fetchOne($query, [$userId]);
        
        // Get number of bookings as passenger
        $query = "SELECT COUNT(*) as booking_count FROM bookings WHERE user_id = ?";
        $tripsAsPassenger = $this->db->fetchOne($query, [$userId]);
        
        // Get last 5 ratings received
        $query = "
            SELECT r.*, 
                u.first_name, 
                u.last_name,
                u.profile_picture_url
            FROM ratings r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.rated_user_id = ?
            ORDER BY r.created_at DESC
            LIMIT 5
        ";
        $recentRatings = $this->db->fetchAll($query, [$userId]);
        
        // Add all data to user profile
        $user['vehicles'] = $vehicles;
        $user['trips_as_driver'] = $tripsAsDriver['trip_count'] ?? 0;
        $user['trips_as_passenger'] = $tripsAsPassenger['booking_count'] ?? 0;
        $user['recent_ratings'] = $recentRatings;
        
        return $user;
    }
    
    /**
     * Register a new user
     *
     * @param array $userData User data (first_name, last_name, email, password, etc.)
     * @return int|false The ID of the newly created user or false on failure
     */
    public function register($userData)
    {
        // Validate email is unique
        $existingUser = $this->findByEmail($userData['email']);
        if ($existingUser) {
            return false;
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Set created_at timestamp
        $userData['created_at'] = date('Y-m-d H:i:s');
        
        // Create user
        return $this->create($userData);
    }
    
    /**
     * Authenticate a user by email and password
     *
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     *
     * @param int $userId User ID
     * @param array $profileData Profile data
     * @return int Number of affected rows
     */
    public function updateProfile($userId, $profileData)
    {
        // Don't allow updating email to an already taken one
        if (isset($profileData['email'])) {
            $existingUser = $this->findByEmail($profileData['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                return 0;
            }
        }
        
        // Don't allow direct password update (use updatePassword method)
        if (isset($profileData['password'])) {
            unset($profileData['password']);
        }
        
        return $this->update($userId, $profileData);
    }
    
    /**
     * Update user password
     *
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool Success or failure
     */
    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        // Get user with password
        $query = "SELECT * FROM users WHERE id = ?";
        $user = $this->db->fetchOne($query, [$userId]);
        
        if (!$user) {
            return false;
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $result = $this->update($userId, ['password' => $hashedPassword]);
        
        return $result > 0;
    }
}