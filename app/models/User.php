<?php

namespace App\Models;

use PDO;
use PDOException;
use DateTime; // Make sure DateTime class is available

class User
{
    private $pdo;

    public function __construct(PDO $pdoInstance)
    {
        $this->pdo = $pdoInstance;
        if (!($this->pdo instanceof PDO)) {
            $errorMessage = "CRITICAL: User model constructed without a valid PDO instance.";
            error_log($errorMessage);
            // Consider throwing an exception in a real application
        }
    }

    /**
     * Creates a new user with default profile values.
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $hashedPassword
     * @return bool|string True on success, 'email_exists' if email is taken, false on other failure.
     */
    public function create($firstName, $lastName, $email, $hashedPassword)
    {
        if (!$this->pdo) {
            error_log("User::create() - PDO connection not available.");
            return false;
        }

        // Define defaults according to the English schema
        $phoneNumber = null;
        $birthDate = null;
        $gender = null;
        $is_driver = 0; // Default to not being a driver
        $is_admin = 0;
        $profile_picture_url = null; // Define variable before binding
        $bio = null;                 // Define variable before binding
        $pref_smokes = 'Not specified';
        $pref_pets = 'Not specified';
        $pref_music = 'Not specified';
        $pref_talk = 'Not specified';
        $is_email_verified = 0;
        $is_phone_verified = 0;
        $account_status = 'active';

        try {
            // Check if email exists first
            $checkSql = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $this->pdo->prepare($checkSql);
            if (!$checkStmt) {
                 error_log("User::create() - Failed to prepare email check. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
                 return false;
            }
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();
            if ($checkStmt->fetch()) {
                return 'email_exists';
            }

            // Prepare the INSERT statement with all relevant columns
            $sql = 'INSERT INTO users (
                        first_name, last_name, email, password, phone_number, birth_date, gender,
                        is_driver, is_admin, registration_date, profile_picture_url, bio,
                        pref_smokes, pref_pets, pref_music, pref_talk,
                        is_email_verified, is_phone_verified, account_status
                    ) VALUES (
                        :first_name, :last_name, :email, :password, :phone_number, :birth_date, :gender,
                        :is_driver, :is_admin, NOW(), :profile_picture_url, :bio,
                        :pref_smokes, :pref_pets, :pref_music, :pref_talk,
                        :is_email_verified, :is_phone_verified, :account_status
                    )';

            $stmt = $this->pdo->prepare($sql);

            if (!$stmt) {
                 error_log("User::create() - Failed to prepare INSERT statement. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
                 return false;
            }

            // Bind all parameters
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone_number', $phoneNumber);
            $stmt->bindParam(':birth_date', $birthDate);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':is_driver', $is_driver, PDO::PARAM_INT);
            $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
            $stmt->bindParam(':profile_picture_url', $profile_picture_url); // Defined above
            $stmt->bindParam(':bio', $bio);                         // Defined above
            $stmt->bindParam(':pref_smokes', $pref_smokes);
            $stmt->bindParam(':pref_pets', $pref_pets);
            $stmt->bindParam(':pref_music', $pref_music);
            $stmt->bindParam(':pref_talk', $pref_talk);
            $stmt->bindParam(':is_email_verified', $is_email_verified, PDO::PARAM_INT);
            $stmt->bindParam(':is_phone_verified', $is_phone_verified, PDO::PARAM_INT);
            $stmt->bindParam(':account_status', $account_status);

            $success = $stmt->execute();

            if ($success) {
                 error_log("Successfully created user: {$email}");
                 return true;
            } else {
                 $errorInfo = $stmt->errorInfo();
                 error_log("User::create() execute() failed for email {$email}. SQLSTATE[{$errorInfo[0]}] Error Code [{$errorInfo[1]}]: {$errorInfo[2]}");
                 return false;
            }

        } catch (PDOException $e) {
            error_log("User::create() PDOException for email {$email}: Code[{$e->getCode()}] Message[{$e->getMessage()}]");
            if ($e->getCode() == '23000') {
                 return 'email_exists';
            }
            return false;
        } catch (\Throwable $e) {
            error_log("User::create() General Error for email {$email}: Code[{$e->getCode()}] Message[{$e->getMessage()}]");
            return false;
        }
    }

    // --- START OF findByEmail METHOD ---
    /**
     * Finds a user by email for login purposes.
     * @param string $email
     * @return array|false User data or false if not found/error.
     */
    public function findByEmail(string $email)
    {
        if (!$this->pdo) {
            error_log("User::findByEmail() - PDO connection not available.");
            return false;
        }
        try {
            // Select columns needed for login and session setting
            $sql = "SELECT id, first_name, last_name, email, password, is_admin, account_status
                    FROM users
                    WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                 error_log("User::findByEmail() - Failed to prepare statement. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
                 return false;
            }
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Returns array if found, false otherwise
        } catch (PDOException $e) {
            error_log("User::findByEmail() PDOException for email {$email}: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
             error_log("User::findByEmail() General Error for email {$email}: " . $e->getMessage());
            return false;
        }
    }
    // --- END OF findByEmail METHOD ---


    /**
     * Finds a user by ID and fetches profile data, including password for verification.
     * @param int $id The user ID.
     * @return array|false User data as an associative array, or false if not found/error.
     */
    public function findById(int $id)
    {
        if (!$this->pdo) {
            error_log("User::findById() - PDO connection not available.");
            return false;
        }
        try {
            // --- ADD 'password' TO THIS SELECT LIST ---
            $sql = "SELECT
                        id, first_name, last_name, email, password, /* <--- ADDED HERE */
                        phone_number, birth_date, gender,
                        is_driver, is_admin, registration_date, profile_picture_url, bio,
                        pref_smokes, pref_pets, pref_music, pref_talk,
                        is_email_verified, is_phone_verified, account_status, last_login
                    FROM users
                    WHERE id = :id
                    LIMIT 1";
            // ---------------------------------------

            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                 error_log("User::findById() - Failed to prepare statement. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
                 return false;
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate age (keep this logic)
            if ($user && !empty($user['birth_date'])) {
                 try {
                     $birthDate = new DateTime($user['birth_date']);
                     $today = new DateTime('today');
                     $user['age'] = $birthDate->diff($today)->y;
                 } catch (\Exception $e) {
                     $user['age'] = null;
                     error_log("User::findById() - Could not parse birth_date '{$user['birth_date']}' for user ID {$id}: " . $e->getMessage());
                 }
            } else if ($user) {
                 $user['age'] = null;
            }

            return $user;

        } catch (PDOException $e) {
            error_log("User::findById() PDOException for ID {$id}: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log("User::findById() General Error for ID {$id}: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Updates the user's profile picture URL.
     * @param int $id User ID
     * @param string|null $newPictureUrl The web-accessible path to the new picture, or null to remove.
     * @return bool True on success, false on failure.
     */
    public function updateProfilePicture(int $id, ?string $newPictureUrl): bool
    {
         if (!$this->pdo) {
            error_log("User::updateProfilePicture() - PDO connection not available.");
            return false;
         }
         $sql = "UPDATE users SET profile_picture_url = :picture_url WHERE id = :id";
         try {
             $stmt = $this->pdo->prepare($sql);
             if (!$stmt) {
                  error_log("User::updateProfilePicture() - Failed to prepare statement. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
                  return false;
             }
             // Bind null explicitly if $newPictureUrl is null
             if ($newPictureUrl === null) {
                 $stmt->bindValue(':picture_url', null, PDO::PARAM_NULL);
             } else {
                 $stmt->bindParam(':picture_url', $newPictureUrl, PDO::PARAM_STR);
             }
             $stmt->bindParam(':id', $id, PDO::PARAM_INT);
             $success = $stmt->execute();

             if ($success) {
                  error_log("Profile picture updated successfully for user ID: {$id}");
                  return true;
             } else {
                  $errorInfo = $stmt->errorInfo();
                  error_log("User::updateProfilePicture() execute() failed for user ID {$id}. SQLSTATE[{$errorInfo[0]}] Error Code [{$errorInfo[1]}]: {$errorInfo[2]}");
                  return false;
             }
         } catch (PDOException $e) {
             error_log("User::updateProfilePicture() PDOException for ID {$id}: " . $e->getMessage());
             return false;
         } catch (\Throwable $e) {
              error_log("User::updateProfilePicture() General Error for ID {$id}: " . $e->getMessage());
              return false;
         }
    }

    /**
 * Updates user profile data.
 * @param int $id User ID
 * @param array $data Associative array of allowed data to update
 * @return bool True on success, false on failure.
 */
 public function updateProfile(int $id, array $data): bool
 {
     if (!$this->pdo) {
        error_log("User::updateProfile() - PDO connection not available.");
        return false;
     }

     // Whitelist allowed fields to prevent mass assignment issues
     $allowedFields = ['first_name', 'last_name', 'phone_number', 'birth_date', 'gender', 'bio', 'pref_smokes', 'pref_pets', 'pref_music', 'pref_talk'];
     $updateFields = [];
     $updateValues = [];

     foreach ($allowedFields as $field) {
         if (isset($data[$field])) {
             $updateFields[] = "`" . $field . "` = :" . $field; // Build SET clause part
             $updateValues[':' . $field] = ($data[$field] === '') ? null : $data[$field]; // Allow setting to null if empty string passed, adjust if needed
         }
     }

     if (empty($updateFields)) {
         error_log("User::updateProfile() - No valid fields provided for update for user ID {$id}.");
         return false; // Nothing to update
     }

     // Add the user ID to the values for the WHERE clause
     $updateValues[':id'] = $id;

     $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

     try {
         $stmt = $this->pdo->prepare($sql);
         if (!$stmt) {
              error_log("User::updateProfile() - Failed to prepare statement. SQL: $sql. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
              return false;
         }
         $success = $stmt->execute($updateValues); // Execute with the prepared values array

         if ($success) {
             error_log("Profile updated successfully for user ID: {$id}");
             return true;
         } else {
             $errorInfo = $stmt->errorInfo();
             error_log("User::updateProfile() execute() failed for user ID {$id}. SQLSTATE[{$errorInfo[0]}] Error Code [{$errorInfo[1]}]: {$errorInfo[2]}");
             return false;
         }
     } catch (PDOException $e) {
         error_log("User::updateProfile() PDOException for ID {$id}: " . $e->getMessage());
         return false;
     } catch (\Throwable $e) {
          error_log("User::updateProfile() General Error for ID {$id}: " . $e->getMessage());
         return false;
     }
 }
// Add inside User class in app/models/User.php
/**
 * Updates only the user's password hash.
 * @param int $id User ID
 * @param string $newHashedPassword The new, already hashed password.
 * @return bool True on success, false on failure.
 */
public function updatePassword(int $id, string $newHashedPassword): bool
{
     if (!$this->pdo) {
        error_log("User::updatePassword() - PDO connection not available.");
        return false;
     }
     $sql = "UPDATE users SET password = :password WHERE id = :id";
     try {
         $stmt = $this->pdo->prepare($sql);
         if (!$stmt) {
              error_log("User::updatePassword() - Failed to prepare statement. PDO Error: " . implode(", ", $this->pdo->errorInfo()));
              return false;
         }
         $stmt->bindParam(':password', $newHashedPassword);
         $stmt->bindParam(':id', $id, PDO::PARAM_INT);
         $success = $stmt->execute();

         if ($success) {
              error_log("Password updated successfully for user ID: {$id}");
              return true;
         } else {
              $errorInfo = $stmt->errorInfo();
              error_log("User::updatePassword() execute() failed for user ID {$id}. SQLSTATE[{$errorInfo[0]}] Error Code [{$errorInfo[1]}]: {$errorInfo[2]}");
              return false;
         }
     } catch (PDOException $e) {
         error_log("User::updatePassword() PDOException for ID {$id}: " . $e->getMessage());
         return false;
     } catch (\Throwable $e) {
          error_log("User::updatePassword() General Error for ID {$id}: " . $e->getMessage());
          return false;
     }
}
    

} // End of User class
?>