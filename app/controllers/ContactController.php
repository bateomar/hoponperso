<?php
require_once 'app/core/Controller.php';

/**
 * Contact Controller
 * 
 * Handles contact form and inquiries
 */
class ContactController extends Controller
{
    /**
     * Display contact form
     */
    public function index()
    {
        $this->view('contact/index');
    }
    
    /**
     * Process contact form submission
     */
    public function send()
    {
        // Validate form data
        $name = $this->input('name');
        $email = $this->input('email');
        $subject = $this->input('subject');
        $message = $this->input('message');
        
        // Basic validation
        if (!$name || !$email || !$subject || !$message) {
            $this->setFlash('error', 'Tous les champs sont requis.');
            $this->redirect('/contact');
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Format d\'email invalide.');
            $this->redirect('/contact');
            return;
        }
        
        // Save message to database
        $db = Database::getInstance();
        $result = $db->insert('contact_messages', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            // Send email notification
            // In a real application, you would send an actual email here
            // For demo purposes, we'll just log the message
            error_log("Contact form submission from {$name} ({$email}): {$subject}");
            
            $this->setFlash('success', 'Votre message a été envoyé. Nous vous répondrons dans les plus brefs délais.');
        } else {
            $this->setFlash('error', 'Une erreur s\'est produite lors de l\'envoi du message. Veuillez réessayer plus tard.');
        }
        
        $this->redirect('/contact');
    }
}