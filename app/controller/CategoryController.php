
<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/CategoryModel.php';


class CategoryController
{
    private $categoryModel;

    public function __construct($conn)
    {
        $this->categoryModel = new CategoryModel($conn);
    }


    // ==========================================
    // GET CATEGORIES
    // ==========================================
    public function getCategories()
    {
        header('Content-Type: application/json');

        $categories = $this->categoryModel->getCategories();

        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);

        exit();
    }


    // ==========================================
    // GET DEPARTMENTS
    // ==========================================
    public function getDepartments()
    {
        header('Content-Type: application/json');

        $departments = $this->categoryModel->getDepartments();

        echo json_encode([
            'success' => true,
            'departments' => $departments
        ]);

        exit();
    }


    // ==========================================
    // GET CATEGORY BY ID
    // ==========================================
    public function getCategory()
    {
        header('Content-Type: application/json');

        $categoryId = isset($_GET['category_id'])
            ? (int) $_GET['category_id']
            : 0;

        if ($categoryId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category ID.'
            ]);
            exit();
        }

        $category = $this->categoryModel->getCategoryById($categoryId);

        if (!$category) {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'category' => $category
        ]);

        exit();
    }


    // ==========================================
    // CREATE CATEGORY
    // ==========================================
    public function createCategory()
    {
        $categoryName = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $departmentId = isset($_POST['department_id'])
            ? (int) $_POST['department_id']
            : 0;

        $keywords = trim($_POST['keywords'] ?? '');

        $defaultPriority = trim($_POST['default_priority'] ?? 'Medium');


        // VALIDATION
        if (empty($categoryName) || $departmentId <= 0) {
            $this->redirectWithError(
                'Please fill in all required fields.'
            );
        }

        if (!in_array(
            $defaultPriority,
            ['Low', 'Medium', 'High', 'Emergency'],
            true
        )) {
            $this->redirectWithError(
                'Invalid default priority.'
            );
        }


        // CHECK DUPLICATE CATEGORY
        if ($this->categoryModel->categoryExists($categoryName)) {
            $this->redirectWithError(
                'This category already exists.'
            );
        }


        // CREATE CATEGORY
        $success = $this->categoryModel->createCategory(
            $categoryName,
            $description,
            $departmentId,
            $keywords,
            $defaultPriority
        );

        if ($success) {
            $this->redirectWithSuccess(
                'Category created successfully.'
            );
        }

        $this->redirectWithError(
            'Failed to create category.'
        );
    }


    // ==========================================
    // UPDATE CATEGORY
    // ==========================================
    public function updateCategory()
    {
        header('Content-Type: application/json');

        $categoryId = isset($_POST['category_id'])
            ? (int) $_POST['category_id']
            : 0;

        $categoryName = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $departmentId = isset($_POST['department_id'])
            ? (int) $_POST['department_id']
            : 0;

        $keywords = trim($_POST['keywords'] ?? '');

        $defaultPriority = trim($_POST['default_priority'] ?? '');


        // VALIDATION
        if ($categoryId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category ID.'
            ]);
            exit();
        }

        if (empty($categoryName) || $departmentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);
            exit();
        }

        if (!in_array(
            $defaultPriority,
            ['Low', 'Medium', 'High', 'Emergency'],
            true
        )) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid default priority.'
            ]);
            exit();
        }


        // CHECK CATEGORY EXISTS
        $currentCategory = $this->categoryModel->getCategoryById(
            $categoryId
        );

        if (!$currentCategory) {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found.'
            ]);
            exit();
        }


        // CHECK DUPLICATE CATEGORY
        if (
            $this->categoryModel->categoryExists(
                $categoryName,
                $categoryId
            )
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'This category already exists.'
            ]);
            exit();
        }


        // UPDATE CATEGORY
        $success = $this->categoryModel->updateCategory(
            $categoryId,
            $categoryName,
            $description,
            $departmentId,
            $keywords,
            $defaultPriority
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Category updated successfully.'
                : 'Failed to update category.'
        ]);

        exit();
    }


    // ==========================================
    // UPDATE CATEGORY STATUS
    // ==========================================
    public function updateStatus()
    {
        header('Content-Type: application/json');

        $categoryId = isset($_POST['category_id'])
            ? (int) $_POST['category_id']
            : 0;

        $status = trim($_POST['status'] ?? '');

        if ($categoryId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category ID.'
            ]);
            exit();
        }

        if (!in_array($status, ['Active', 'Inactive'], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category status.'
            ]);
            exit();
        }

        $success = $this->categoryModel->updateCategoryStatus(
            $categoryId,
            $status
        );

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Category status updated successfully.'
                : 'Failed to update category status.'
        ]);

        exit();
    }


    // ==========================================
    // DELETE CATEGORY
    // ==========================================
    public function deleteCategory()
    {
        header('Content-Type: application/json');

        $categoryId = isset($_POST['category_id'])
            ? (int) $_POST['category_id']
            : 0;

        if ($categoryId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category ID.'
            ]);
            exit();
        }

        $success = $this->categoryModel->deleteCategory(
            $categoryId
        );

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Category deleted successfully.'
                : 'Failed to delete category.'
        ]);

        exit();
    }


    // ==========================================
    // REDIRECT WITH SUCCESS
    // ==========================================
    private function redirectWithSuccess($message)
    {
        $_SESSION['success_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=manage_categories'
        );

        exit();
    }


    // ==========================================
    // REDIRECT WITH ERROR
    // ==========================================
    private function redirectWithError($message)
    {
        $_SESSION['error_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=manage_categories'
        );

        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new CategoryController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'get_categories':
        $controller->getCategories();
        break;

    case 'get_category':
        $controller->getCategory();
        break;

    case 'get_departments':
        $controller->getDepartments();
        break;

    case 'create_category':
        $controller->createCategory();
        break;

    case 'update_category':
        $controller->updateCategory();
        break;

    case 'updateStatus':
        $controller->updateStatus();
        break;

    case 'delete':
        $controller->deleteCategory();
        break;

    default:
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}
