<?php
require_once 'app/core/Controller.php';
require_once 'app/models/FaqModel.php';

/**
 * FAQ Controller
 */
class FaqController extends Controller
{
    private $faqModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->faqModel = new FaqModel();
    }
    
    /**
     * Display FAQ index with categories
     */
    public function index()
    {
        $categories = $this->faqModel->getAllCategories();
        $faqsByCategory = $this->faqModel->getAllGroupedByCategory();
        
        $this->view('faq/index', [
            'categories' => $categories,
            'faqsByCategory' => $faqsByCategory
        ]);
    }
    
    /**
     * Display FAQs for a specific category
     * 
     * @param string $category Category name
     */
    public function category($category)
    {
        $faqs = $this->faqModel->getByCategory($category);
        $allCategories = $this->faqModel->getAllCategories();
        
        $this->view('faq/category', [
            'category' => $category,
            'faqs' => $faqs,
            'allCategories' => $allCategories
        ]);
    }
    
    /**
     * Search FAQs
     */
    public function search()
    {
        $keyword = $this->query('q');
        
        if (!$keyword) {
            $this->redirect('/faq');
            return;
        }
        
        $results = $this->faqModel->search($keyword);
        $allCategories = $this->faqModel->getAllCategories();
        
        $this->view('faq/search', [
            'keyword' => $keyword,
            'results' => $results,
            'allCategories' => $allCategories
        ]);
    }
    
    /**
     * Display a specific FAQ
     * 
     * @param int $id FAQ ID
     */
    public function show($id)
    {
        $faq = $this->faqModel->findById($id);
        
        if (!$faq) {
            $this->setFlash('error', 'FAQ non trouvée.');
            $this->redirect('/faq');
            return;
        }
        
        $relatedFaqs = $this->faqModel->getByCategory($faq['category']);
        
        // Remove current FAQ from related
        foreach ($relatedFaqs as $key => $related) {
            if ($related['id'] == $id) {
                unset($relatedFaqs[$key]);
                break;
            }
        }
        
        // Limit to 5 related FAQs
        $relatedFaqs = array_slice($relatedFaqs, 0, 5);
        
        $this->view('faq/show', [
            'faq' => $faq,
            'relatedFaqs' => $relatedFaqs
        ]);
    }
    
    /**
     * Display FAQ admin form
     */
    public function admin()
    {
        // Check if user is an admin
        $isAdmin = $this->getSession('is_admin');
        if (!$isAdmin) {
            $this->setFlash('error', "Vous n'avez pas les droits pour accéder à cette page.");
            $this->redirect('/faq');
            return;
        }
        
        $faqs = $this->faqModel->findAll();
        $categories = $this->faqModel->getAllCategories();
        
        $this->view('faq/admin', [
            'faqs' => $faqs,
            'categories' => $categories
        ]);
    }
    
    /**
     * Add a new FAQ
     */
    public function add()
    {
        // Check if user is an admin
        $isAdmin = $this->getSession('is_admin');
        if (!$isAdmin) {
            $this->json(['success' => false, 'message' => "Vous n'avez pas les droits pour effectuer cette action."]);
            return;
        }
        
        $question = $this->input('question');
        $answer = $this->input('answer');
        $category = $this->input('category');
        
        if (!$question || !$answer || !$category) {
            $this->json(['success' => false, 'message' => 'Tous les champs sont requis.']);
            return;
        }
        
        $faqId = $this->faqModel->addFaq($question, $answer, $category);
        
        if ($faqId) {
            $this->json(['success' => true, 'message' => 'FAQ ajoutée avec succès.', 'id' => $faqId]);
        } else {
            $this->json(['success' => false, 'message' => "Une erreur s'est produite lors de l'ajout de la FAQ."]);
        }
    }
    
    /**
     * Update an existing FAQ
     */
    public function updateFaq()
    {
        // Check if user is an admin
        $isAdmin = $this->getSession('is_admin');
        if (!$isAdmin) {
            $this->json(['success' => false, 'message' => "Vous n'avez pas les droits pour effectuer cette action."]);
            return;
        }
        
        $id = $this->input('id');
        $question = $this->input('question');
        $answer = $this->input('answer');
        $category = $this->input('category');
        
        if (!$id || !$question || !$answer || !$category) {
            $this->json(['success' => false, 'message' => 'Tous les champs sont requis.']);
            return;
        }
        
        $result = $this->faqModel->updateFaq($id, $question, $answer, $category);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'FAQ mise à jour avec succès.']);
        } else {
            $this->json(['success' => false, 'message' => "Une erreur s'est produite lors de la mise à jour de la FAQ."]);
        }
    }
    
    /**
     * Delete a FAQ
     */
    public function delete()
    {
        // Check if user is an admin
        $isAdmin = $this->getSession('is_admin');
        if (!$isAdmin) {
            $this->json(['success' => false, 'message' => "Vous n'avez pas les droits pour effectuer cette action."]);
            return;
        }
        
        $id = $this->input('id');
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de FAQ requis.']);
            return;
        }
        
        $result = $this->faqModel->delete($id);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'FAQ supprimée avec succès.']);
        } else {
            $this->json(['success' => false, 'message' => "Une erreur s'est produite lors de la suppression de la FAQ."]);
        }
    }
    
    /**
     * API to get FAQs in JSON format
     */
    public function api()
    {
        $category = $this->query('category');
        $q = $this->query('q');
        
        if ($q) {
            $faqs = $this->faqModel->search($q);
        } elseif ($category) {
            $faqs = $this->faqModel->getByCategory($category);
        } else {
            $faqs = $this->faqModel->findAll();
        }
        
        $this->json([
            'success' => true,
            'count' => count($faqs),
            'faqs' => $faqs
        ]);
    }
}