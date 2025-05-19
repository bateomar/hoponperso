<?php
require_once 'app/core/Controller.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/TripModel.php';

/**
 * Users Controller
 * 
 * Handles user profile and account management
 */
class UsersController extends Controller
{
    private $userModel;
    private $tripModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->tripModel = new TripModel();
    }
    
    /**
     * Display user profile
     */
    public function profile()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setSession('redirect_url', '/profile');
            $this->setFlash('error', 'Vous devez être connecté pour accéder à votre profil.');
            $this->redirect('/login');
            return;
        }
        
        // Get user profile data
        $user = $this->userModel->getDetailedProfile($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Profil utilisateur non trouvé.');
            $this->redirect('/');
            return;
        }
        
        // Get user's trips as driver
        $tripsAsDriver = $this->tripModel->findTripsByDriver($userId);
        
        // Get user's trips as passenger
        $tripsAsPassenger = $this->tripModel->findUpcomingTripsForPassenger($userId);
        
        $this->view('profile/index', [
            'user' => $user,
            'tripsAsDriver' => $tripsAsDriver,
            'tripsAsPassenger' => $tripsAsPassenger
        ]);
    }
    
    /**
     * Display edit profile form
     */
    public function edit()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setSession('redirect_url', '/profile/edit');
            $this->setFlash('error', 'Vous devez être connecté pour modifier votre profil.');
            $this->redirect('/login');
            return;
        }
        
        // Get user data
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Profil utilisateur non trouvé.');
            $this->redirect('/');
            return;
        }
        
        $this->view('profile/edit', [
            'user' => $user
        ]);
    }
    
    /**
     * Process profile update form
     */
    public function update()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour modifier votre profil.');
            $this->redirect('/login');
            return;
        }
        
        // Get user data
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Profil utilisateur non trouvé.');
            $this->redirect('/');
            return;
        }
        
        // Validate form data
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $phone = $this->input('phone');
        $bio = $this->input('bio');
        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');
        
        // Basic validation
        if (!$firstName || !$lastName || !$email) {
            $this->setFlash('error', 'Les champs nom, prénom et email sont requis.');
            $this->redirect('/profile/edit');
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Format d\'email invalide.');
            $this->redirect('/profile/edit');
            return;
        }
        
        // Prepare profile data
        $profileData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'bio' => $bio,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle profile picture upload if provided
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/avatars/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = 'user_' . $userId . '_' . time() . '.png';
            $uploadFile = $uploadDir . $fileName;
            
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $this->setFlash('error', 'Le format de l\'image n\'est pas accepté. Utilisez JPG, PNG ou GIF.');
                $this->redirect('/profile/edit');
                return;
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
                $profileData['profile_picture_url'] = $uploadFile;
            } else {
                $this->setFlash('error', 'Erreur lors de l\'upload de l\'image.');
                $this->redirect('/profile/edit');
                return;
            }
        }
        
        // Update profile
        $result = $this->userModel->updateProfile($userId, $profileData);
        
        // Handle password change if provided
        if ($currentPassword && $newPassword) {
            // Validate new password length
            if (strlen($newPassword) < 8) {
                $this->setFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
                $this->redirect('/profile/edit');
                return;
            }
            
            // Validate password confirmation
            if ($newPassword !== $confirmPassword) {
                $this->setFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                $this->redirect('/profile/edit');
                return;
            }
            
            // Update password
            $passwordResult = $this->userModel->updatePassword($userId, $currentPassword, $newPassword);
            
            if (!$passwordResult) {
                $this->setFlash('error', 'Le mot de passe actuel est incorrect.');
                $this->redirect('/profile/edit');
                return;
            }
        }
        
        // Update session
        $this->setSession('user_name', $firstName . ' ' . $lastName);
        $this->setSession('user_email', $email);
        
        $this->setFlash('success', 'Votre profil a été mis à jour avec succès.');
        $this->redirect('/profile');
    }
    
    /**
     * Display public profile of a user
     * 
     * @param int $id User ID
     */
    public function show($id)
    {
        // Get user profile data
        $user = $this->userModel->getDetailedProfile($id);
        
        if (!$user) {
            $this->setFlash('error', 'Profil utilisateur non trouvé.');
            $this->redirect('/');
            return;
        }
        
        // Get user's active trips as driver
        $query = "
            SELECT t.*, 
                v.model AS vehicle_model, 
                v.brand AS vehicle_brand
            FROM trips t
            LEFT JOIN vehicles v ON t.vehicle_id = v.id
            WHERE t.driver_id = ? AND t.departure_time > NOW() AND t.status = 'active'
            ORDER BY t.departure_time ASC
            LIMIT 5
        ";
        $upcomingTrips = $this->tripModel->db->fetchAll($query, [$id]);
        
        $this->view('profile/show', [
            'user' => $user,
            'upcomingTrips' => $upcomingTrips,
            'isOwnProfile' => ($this->getSession('user_id') == $id)
        ]);
    }
    
    /**
     * Display user's vehicle management page
     */
    public function vehicles()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setSession('redirect_url', '/profile/vehicles');
            $this->setFlash('error', 'Vous devez être connecté pour gérer vos véhicules.');
            $this->redirect('/login');
            return;
        }
        
        // Get user's vehicles
        $query = "SELECT * FROM vehicles WHERE user_id = ?";
        $vehicles = $this->userModel->db->fetchAll($query, [$userId]);
        
        $this->view('profile/vehicles', [
            'vehicles' => $vehicles
        ]);
    }
    
    /**
     * Add a new vehicle
     */
    public function addVehicle()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vous devez être connecté.']);
            return;
        }
        
        // Validate form data
        $brand = $this->input('brand');
        $model = $this->input('model');
        $year = $this->input('year');
        $color = $this->input('color');
        $seats = $this->input('seats');
        $features = $this->input('features');
        
        if (!$brand || !$model || !year || !$color || !$seats) {
            $this->json(['success' => false, 'message' => 'Tous les champs requis doivent être remplis.']);
            return;
        }
        
        // Prepare vehicle data
        $vehicleData = [
            'user_id' => $userId,
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'seats' => $seats,
            'comfort_features' => $features,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert vehicle
        $result = $this->userModel->db->insert('vehicles', $vehicleData);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Véhicule ajouté avec succès.', 'id' => $result]);
        } else {
            $this->json(['success' => false, 'message' => 'Une erreur s\'est produite lors de l\'ajout du véhicule.']);
        }
    }
    
    /**
     * Update a vehicle
     */
    public function updateVehicle()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vous devez être connecté.']);
            return;
        }
        
        $vehicleId = $this->input('id');
        
        // Check if vehicle belongs to user
        $query = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
        $vehicle = $this->userModel->db->fetchOne($query, [$vehicleId, $userId]);
        
        if (!$vehicle) {
            $this->json(['success' => false, 'message' => 'Véhicule non trouvé ou non autorisé.']);
            return;
        }
        
        // Validate form data
        $brand = $this->input('brand');
        $model = $this->input('model');
        $year = $this->input('year');
        $color = $this->input('color');
        $seats = $this->input('seats');
        $features = $this->input('features');
        
        if (!$brand || !$model || !year || !$color || !$seats) {
            $this->json(['success' => false, 'message' => 'Tous les champs requis doivent être remplis.']);
            return;
        }
        
        // Prepare vehicle data
        $vehicleData = [
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'seats' => $seats,
            'comfort_features' => $features,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update vehicle
        $result = $this->userModel->db->update('vehicles', $vehicleData, 'id = ?', [$vehicleId]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Véhicule mis à jour avec succès.']);
        } else {
            $this->json(['success' => false, 'message' => 'Une erreur s\'est produite lors de la mise à jour du véhicule.']);
        }
    }
    
    /**
     * Delete a vehicle
     */
    public function deleteVehicle()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vous devez être connecté.']);
            return;
        }
        
        $vehicleId = $this->input('id');
        
        // Check if vehicle belongs to user
        $query = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
        $vehicle = $this->userModel->db->fetchOne($query, [$vehicleId, $userId]);
        
        if (!$vehicle) {
            $this->json(['success' => false, 'message' => 'Véhicule non trouvé ou non autorisé.']);
            return;
        }
        
        // Check if vehicle is used in any trips
        $query = "SELECT COUNT(*) as trip_count FROM trips WHERE vehicle_id = ?";
        $tripCount = $this->userModel->db->fetchOne($query, [$vehicleId]);
        
        if ($tripCount && $tripCount['trip_count'] > 0) {
            $this->json(['success' => false, 'message' => 'Ce véhicule est utilisé dans des trajets et ne peut pas être supprimé.']);
            return;
        }
        
        // Delete vehicle
        $result = $this->userModel->db->delete('vehicles', 'id = ?', [$vehicleId]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Véhicule supprimé avec succès.']);
        } else {
            $this->json(['success' => false, 'message' => 'Une erreur s\'est produite lors de la suppression du véhicule.']);
        }
    }
}