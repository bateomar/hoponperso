<?php
require_once 'app/core/Model.php';

/**
 * FAQ Model
 * 
 * Handles database operations for frequently asked questions
 */
class FaqModel extends Model
{
    protected $table = 'faqs';
    
    /**
     * Get all FAQs grouped by category
     *
     * @return array FAQs grouped by category
     */
    public function getAllGroupedByCategory()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY category, id";
        $faqs = $this->db->fetchAll($query);
        
        $grouped = [];
        foreach ($faqs as $faq) {
            $category = $faq['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $faq;
        }
        
        return $grouped;
    }
    
    /**
     * Search FAQs by keyword
     *
     * @param string $keyword Search term
     * @return array Matching FAQs
     */
    public function search($keyword)
    {
        $query = "
            SELECT * FROM {$this->table}
            WHERE question LIKE ? OR answer LIKE ?
            ORDER BY category, id
        ";
        
        $params = ["%$keyword%", "%$keyword%"];
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get FAQs by category
     *
     * @param string $category Category name
     * @return array FAQs in the specified category
     */
    public function getByCategory($category)
    {
        return $this->findBy('category', $category);
    }
    
    /**
     * Get all unique categories
     *
     * @return array List of unique categories
     */
    public function getAllCategories()
    {
        $query = "SELECT DISTINCT category FROM {$this->table} ORDER BY category";
        $result = $this->db->fetchAll($query);
        
        $categories = [];
        foreach ($result as $row) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
    
    /**
     * Add a new FAQ
     *
     * @param string $question The question
     * @param string $answer The answer
     * @param string $category The category
     * @return int|false The ID of the newly created FAQ or false on failure
     */
    public function addFaq($question, $answer, $category)
    {
        $data = [
            'question' => $question,
            'answer' => $answer,
            'category' => $category,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    /**
     * Update an existing FAQ
     *
     * @param int $id FAQ ID
     * @param string $question The question
     * @param string $answer The answer
     * @param string $category The category
     * @return int Number of affected rows
     */
    public function updateFaq($id, $question, $answer, $category)
    {
        $data = [
            'question' => $question,
            'answer' => $answer,
            'category' => $category,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $data);
    }
}