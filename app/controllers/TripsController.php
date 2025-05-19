<?php
require_once 'app/core/Controller.php';
require_once 'app/models/TripModel.php';
require_once 'app/models/UserModel.php';

/**
 * Trips Controller
 */
class TripsController extends Controller
{
    private $tripModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tripModel = new TripModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Handle legacy show trip URLs (ride_details.php?id=X)
     */
    public function legacyShow()
    {
        $id = $this->query('id');
        if ($id) {
            $this->redirect('/trips/' . $id);
        } else {
            $this->redirect('/trips');
        }
    }
    
    /**
     * Display the trips search/listing page
     */
    public function index()
    {
        $departure = $this->query('departure');
        $arrival = $this->query('arrival');
        $date = $this->query('date');
        
        // If search parameters are provided, search for trips
        if ($departure || $arrival || $date) {
            $trips = $this->tripModel->searchTrips($departure, $arrival, $date);
        } else {
            // Otherwise, get all trips
            $trips = $this->tripModel->findAllWithDriverAndVehicle();
        }
        
        $this->view('home/index', [
            'trips' => $trips,
            'departure' => $departure,
            'arrival' => $arrival,
            'date' => $date
        ]);
    }
    
    /**
     * Display a trip's details
     * 
     * @param int $id Trip ID
     */
    public function show($id)
    {
        $trip = $this->tripModel->findByIdWithDetails($id);
        
        if (!$trip) {
            $this->setFlash('error', 'Trajet non trouvé.');
            $this->redirect('/trips');
            return;
        }
        
        // Calculate carbon footprint
        $passengers = $trip['seats_offered'] > 0 ? $trip['seats_offered'] : 1;
        $carbonFootprint = $this->tripModel->calculateCarbonFootprint($id, $passengers);
        
        $this->view('trip/show', [
            'trip' => $trip,
            'carbon' => $carbonFootprint
        ]);
    }
    
    /**
     * Display the trip creation form
     */
    public function create()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour créer un trajet.');
            $this->redirect('/login');
            return;
        }
        
        // Get user's vehicles
        $query = "SELECT * FROM vehicles WHERE user_id = ?";
        $vehicles = $this->tripModel->db->fetchAll($query, [$userId]);
        
        $this->view('trip/create', [
            'vehicles' => $vehicles
        ]);
    }
    
    /**
     * Process the trip creation form
     */
    public function store()
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour créer un trajet.');
            $this->redirect('/login');
            return;
        }
        
        // Validate form data
        $departure = $this->input('departure_location');
        $arrival = $this->input('arrival_location');
        $departureTime = $this->input('departure_time');
        $arrivalTime = $this->input('arrival_time_estimated');
        $price = $this->input('price');
        $seats = $this->input('seats_offered');
        $vehicleId = $this->input('vehicle_id');
        $details = $this->input('trip_details');
        
        if (!$departure || !$arrival || !$departureTime || !$price || !$seats || !$vehicleId) {
            $this->setFlash('error', 'Tous les champs requis doivent être remplis.');
            $this->redirect('/trips/create');
            return;
        }
        
        // Prepare trip data
        $tripData = [
            'driver_id' => $userId,
            'vehicle_id' => $vehicleId,
            'departure_location' => $departure,
            'arrival_location' => $arrival,
            'departure_time' => $departureTime,
            'arrival_time_estimated' => $arrivalTime,
            'price' => $price,
            'seats_offered' => $seats,
            'seats_booked' => 0,
            'status' => 'active',
            'trip_details' => $details,
            'allow_instant_booking' => $this->input('allow_instant_booking') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add coordinates if provided
        if ($this->input('departure_latitude') && $this->input('departure_longitude')) {
            $tripData['departure_latitude'] = $this->input('departure_latitude');
            $tripData['departure_longitude'] = $this->input('departure_longitude');
        }
        
        if ($this->input('arrival_latitude') && $this->input('arrival_longitude')) {
            $tripData['arrival_latitude'] = $this->input('arrival_latitude');
            $tripData['arrival_longitude'] = $this->input('arrival_longitude');
        }
        
        // Create the trip
        $tripId = $this->tripModel->create($tripData);
        
        if ($tripId) {
            $this->setFlash('success', 'Votre trajet a été créé avec succès.');
            $this->redirect('/trips/' . $tripId);
        } else {
            $this->setFlash('error', "Une erreur s'est produite lors de la création du trajet.");
            $this->redirect('/trips/create');
        }
    }
    
    /**
     * Display the trip edit form
     * 
     * @param int $id Trip ID
     */
    public function edit($id)
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour modifier un trajet.');
            $this->redirect('/login');
            return;
        }
        
        // Get the trip
        $trip = $this->tripModel->findById($id);
        
        if (!$trip) {
            $this->setFlash('error', 'Trajet non trouvé.');
            $this->redirect('/trips');
            return;
        }
        
        // Check if the logged-in user is the owner of the trip
        if ($trip['driver_id'] != $userId) {
            $this->setFlash('error', "Vous n'êtes pas autorisé à modifier ce trajet.");
            $this->redirect('/trips/' . $id);
            return;
        }
        
        // Get user's vehicles
        $query = "SELECT * FROM vehicles WHERE user_id = ?";
        $vehicles = $this->tripModel->db->fetchAll($query, [$userId]);
        
        $this->view('trip/edit', [
            'trip' => $trip,
            'vehicles' => $vehicles
        ]);
    }
    
    /**
     * Process the trip update form
     * 
     * @param int $id Trip ID
     */
    public function update($id)
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour modifier un trajet.');
            $this->redirect('/login');
            return;
        }
        
        // Get the trip
        $trip = $this->tripModel->findById($id);
        
        if (!$trip) {
            $this->setFlash('error', 'Trajet non trouvé.');
            $this->redirect('/trips');
            return;
        }
        
        // Check if the logged-in user is the owner of the trip
        if ($trip['driver_id'] != $userId) {
            $this->setFlash('error', "Vous n'êtes pas autorisé à modifier ce trajet.");
            $this->redirect('/trips/' . $id);
            return;
        }
        
        // Validate form data
        $departure = $this->input('departure_location');
        $arrival = $this->input('arrival_location');
        $departureTime = $this->input('departure_time');
        $arrivalTime = $this->input('arrival_time_estimated');
        $price = $this->input('price');
        $seats = $this->input('seats_offered');
        $vehicleId = $this->input('vehicle_id');
        $details = $this->input('trip_details');
        
        if (!$departure || !$arrival || !$departureTime || !$price || !$seats || !$vehicleId) {
            $this->setFlash('error', 'Tous les champs requis doivent être remplis.');
            $this->redirect('/trips/' . $id . '/edit');
            return;
        }
        
        // Prepare trip data
        $tripData = [
            'vehicle_id' => $vehicleId,
            'departure_location' => $departure,
            'arrival_location' => $arrival,
            'departure_time' => $departureTime,
            'arrival_time_estimated' => $arrivalTime,
            'price' => $price,
            'seats_offered' => $seats,
            'trip_details' => $details,
            'allow_instant_booking' => $this->input('allow_instant_booking') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Add coordinates if provided
        if ($this->input('departure_latitude') && $this->input('departure_longitude')) {
            $tripData['departure_latitude'] = $this->input('departure_latitude');
            $tripData['departure_longitude'] = $this->input('departure_longitude');
        }
        
        if ($this->input('arrival_latitude') && $this->input('arrival_longitude')) {
            $tripData['arrival_latitude'] = $this->input('arrival_latitude');
            $tripData['arrival_longitude'] = $this->input('arrival_longitude');
        }
        
        // Update the trip
        $result = $this->tripModel->update($id, $tripData);
        
        if ($result) {
            $this->setFlash('success', 'Votre trajet a été mis à jour avec succès.');
            $this->redirect('/trips/' . $id);
        } else {
            $this->setFlash('error', "Une erreur s'est produite lors de la mise à jour du trajet.");
            $this->redirect('/trips/' . $id . '/edit');
        }
    }
    
    /**
     * Cancel a trip
     * 
     * @param int $id Trip ID
     */
    public function cancel($id)
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vous devez être connecté.']);
            return;
        }
        
        // Get the trip
        $trip = $this->tripModel->findById($id);
        
        if (!$trip) {
            $this->json(['success' => false, 'message' => 'Trajet non trouvé.']);
            return;
        }
        
        // Check if the logged-in user is the owner of the trip
        if ($trip['driver_id'] != $userId) {
            $this->json(['success' => false, 'message' => "Vous n'êtes pas autorisé à annuler ce trajet."]);
            return;
        }
        
        // Update trip status to cancelled
        $result = $this->tripModel->update($id, [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Le trajet a été annulé avec succès.']);
        } else {
            $this->json(['success' => false, 'message' => "Une erreur s'est produite lors de l'annulation du trajet."]);
        }
    }
    
    /**
     * Search for trips (AJAX)
     */
    public function search()
    {
        $departure = $this->query('departure');
        $arrival = $this->query('arrival');
        $date = $this->query('date');
        $minPrice = $this->query('min_price');
        $maxPrice = $this->query('max_price');
        $minTime = $this->query('min_time');
        $maxTime = $this->query('max_time');
        $sortBy = $this->query('sort_by', 'departure_time');
        $sortDir = $this->query('sort_dir', 'ASC');
        
        // Base search
        $trips = $this->tripModel->searchTrips($departure, $arrival, $date);
        
        // Additional filters
        if ($minPrice !== null || $maxPrice !== null || $minTime !== null || $maxTime !== null) {
            $filteredTrips = [];
            
            foreach ($trips as $trip) {
                // Price filter
                if ($minPrice !== null && floatval($trip['price']) < floatval($minPrice)) {
                    continue;
                }
                if ($maxPrice !== null && floatval($trip['price']) > floatval($maxPrice)) {
                    continue;
                }
                
                // Time filter
                if ($minTime !== null || $maxTime !== null) {
                    $departureHour = date('H:i', strtotime($trip['departure_time']));
                    
                    if ($minTime !== null && $departureHour < $minTime) {
                        continue;
                    }
                    if ($maxTime !== null && $departureHour > $maxTime) {
                        continue;
                    }
                }
                
                $filteredTrips[] = $trip;
            }
            
            $trips = $filteredTrips;
        }
        
        // Sort results
        usort($trips, function($a, $b) use ($sortBy, $sortDir) {
            if ($sortDir === 'DESC') {
                return $a[$sortBy] < $b[$sortBy] ? 1 : -1;
            } else {
                return $a[$sortBy] > $b[$sortBy] ? 1 : -1;
            }
        });
        
        // Return JSON response
        $this->json([
            'success' => true,
            'count' => count($trips),
            'trips' => $trips
        ]);
    }
    
    /**
     * Map visualization for a trip
     * 
     * @param int $id Trip ID
     */
    public function map($id)
    {
        $trip = $this->tripModel->findByIdWithDetails($id);
        
        if (!$trip) {
            $this->setFlash('error', 'Trajet non trouvé.');
            $this->redirect('/trips');
            return;
        }
        
        $this->view('trip/map', [
            'trip' => $trip
        ]);
    }
    
    /**
     * Share a trip with trusted contacts
     * 
     * @param int $id Trip ID
     */
    public function share($id)
    {
        // Check if user is logged in
        $userId = $this->getSession('user_id');
        if (!$userId) {
            $this->setFlash('error', 'Vous devez être connecté pour partager un trajet.');
            $this->redirect('/login');
            return;
        }
        
        $trip = $this->tripModel->findById($id);
        
        if (!$trip) {
            $this->setFlash('error', 'Trajet non trouvé.');
            $this->redirect('/trips');
            return;
        }
        
        // Get trusted contacts
        $query = "SELECT * FROM contacts_confiance WHERE utilisateur_id = ?";
        $contacts = $this->tripModel->db->fetchAll($query, [$userId]);
        
        $this->view('trip/share', [
            'trip' => $trip,
            'contacts' => $contacts
        ]);
    }
}