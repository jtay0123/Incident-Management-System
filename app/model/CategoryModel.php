
<?php

class CategoryModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET ALL CATEGORIES
    // ==========================================
    public function getCategories()
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.category_id,
                c.category_name,
                c.description,
                c.department_id,
                d.department_name,
                c.keywords,
                c.default_priority,
                c.status,
                c.created_at
            FROM categories c
            LEFT JOIN departments d
                ON c.department_id = d.department_id
            ORDER BY c.category_id DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        $stmt->close();

        return $categories;
    }


    // ==========================================
    // GET ONE CATEGORY BY ID
    // ==========================================
    public function getCategoryById($categoryId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.category_id,
                c.category_name,
                c.description,
                c.department_id,
                d.department_name,
                c.keywords,
                c.default_priority,
                c.status,
                c.created_at
            FROM categories c
            LEFT JOIN departments d
                ON c.department_id = d.department_id
            WHERE c.category_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $categoryId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $category = $result->fetch_assoc();

        $stmt->close();

        return $category ?: null;
    }


    // ==========================================
    // CHECK DUPLICATE CATEGORY
    // ==========================================
    public function categoryExists(
        $categoryName,
        $excludeCategoryId = 0
    ) {
        $stmt = $this->conn->prepare("
            SELECT category_id
            FROM categories
            WHERE category_name = ?
              AND category_id != ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $categoryName,
            $excludeCategoryId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // CREATE CATEGORY
    // ==========================================
    public function createCategory(
        $categoryName,
        $description,
        $departmentId,
        $keywords,
        $defaultPriority = 'Medium'
    ) {
        $status = 'Active';

        $stmt = $this->conn->prepare("
            INSERT INTO categories
            (
                category_name,
                description,
                department_id,
                status,
                created_at,
                keywords,
                default_priority
            )
            VALUES
            (?, ?, ?, ?, NOW(), ?, ?)
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssisss",
            $categoryName,
            $description,
            $departmentId,
            $status,
            $keywords,
            $defaultPriority
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE CATEGORY STATUS
    // ==========================================
    public function updateCategoryStatus(
        $categoryId,
        $status
    ) {
        $allowedStatuses = [
            'Active',
            'Inactive'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE categories
            SET status = ?
            WHERE category_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $status,
            $categoryId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE CATEGORY
    // ==========================================
    public function updateCategory(
        $categoryId,
        $categoryName,
        $description,
        $departmentId,
        $keywords,
        $defaultPriority = 'Medium'
    ) {
        $stmt = $this->conn->prepare("
            UPDATE categories
            SET
                category_name = ?,
                description = ?,
                department_id = ?,
                keywords = ?,
                default_priority = ?
            WHERE category_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssissi",
            $categoryName,
            $description,
            $departmentId,
            $keywords,
            $defaultPriority,
            $categoryId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // GET ALL DEPARTMENTS
    // ==========================================
    public function getDepartments()
    {
        $stmt = $this->conn->prepare("
            SELECT
                department_id,
                department_name
            FROM departments
            ORDER BY department_name ASC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $departments = [];

        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }

        $stmt->close();

        return $departments;
    }


    // ==========================================
    // DELETE CATEGORY
    // ==========================================
    public function deleteCategory($categoryId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM categories
            WHERE category_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "i",
            $categoryId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
}
