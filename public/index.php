<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Database Connection Helper
function getDB() {
    $host = "localhost";
    $user = "root"; 
    $password = "";    
    $dbname = "filipino_cookbook_api"; 
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $conn;
}

// Token-Based Security
$authMiddleware = function (Request $request, $handler) {
    $authHeader = $request->getHeaderLine('Authorization');
    $expectedToken = 'Bearer dmmmsu-cookbook-token-2026'; 

    if ($authHeader !== $expectedToken) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Unauthorized access. Valid API token is required." 
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); 
    }

    return $handler->handle($request);
};

// 1. Welcome Part
$app->get('/', function (Request $request, Response $response, $args) {
    $payload = json_encode([
        "message" => "Welcome to the Secured Filipino Cookbook API",
        "note" => "Use a valid Bearer token to access /api endpoints." 
    ]);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Group secured routes and apply auth middleware
$app->group('/api', function (RouteCollectorProxy $group) {

    // Helper function to fetch ingredients for a food item
    $getIngredients = function($db, $food_id) {
        $stmt = $db->prepare("SELECT i.ingredient_name FROM ingredients i JOIN food_ingredients fi ON i.ingredient_id = fi.ingredient_id WHERE fi.food_id = :food_id");
        $stmt->bindParam(':food_id', $food_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    };

    // 2. Get Randomly Selected Food
    $group->get('/foods/random', function (Request $request, Response $response) use ($getIngredients) {
        $db = getDB();
        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions 
                FROM foods f 
                JOIN categories c ON f.category_id = c.category_id 
                JOIN origins o ON f.origin_id = o.origin_id 
                ORDER BY RAND() LIMIT 1";
        $stmt = $db->query($sql);
        $food = $stmt->fetch();

        if ($food) {
            $food['ingredients'] = $getIngredients($db, $food['food_id']);
            $response->getBody()->write(json_encode($food));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "No food items found in the database."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    });

    // 3. Get Number of Foods Under Each Category
    $group->get('/categories/count', function (Request $request, Response $response) {
        $db = getDB();
        $sql = "SELECT c.category_id, c.category_name, COUNT(f.food_id) as total_foods 
                FROM categories c 
                LEFT JOIN foods f ON c.category_id = f.category_id 
                GROUP BY c.category_id, c.category_name";
        $stmt = $db->query($sql);
        $categoryCounts = $stmt->fetchAll();

        $response->getBody()->write(json_encode($categoryCounts));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 4. Get Foods by Region or Province (Origin)
    $group->get('/foods/origin/{origin_name}', function (Request $request, Response $response, $args) use ($getIngredients) {
        $originName = "%" . $args['origin_name'] . "%";
        $db = getDB();
        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions 
                FROM foods f 
                JOIN categories c ON f.category_id = c.category_id 
                JOIN origins o ON f.origin_id = o.origin_id 
                WHERE o.origin_name LIKE :origin_name";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':origin_name', $originName);
        $stmt->execute();
        $foods = $stmt->fetchAll();

        foreach ($foods as &$food) {
            $food['ingredients'] = $getIngredients($db, $food['food_id']);
        }

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 5. All Foods
    $group->get('/foods', function (Request $request, Response $response) use ($getIngredients) {
        $db = getDB();
        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions 
                FROM foods f 
                JOIN categories c ON f.category_id = c.category_id 
                JOIN origins o ON f.origin_id = o.origin_id
                ORDER by f.food_id ASC";
        $stmt = $db->query($sql);
        $foods = $stmt->fetchAll();

        foreach ($foods as &$food) {
            $food['ingredients'] = $getIngredients($db, $food['food_id']);
        }

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 6. Food by ID
    $group->get('/foods/{id}', function (Request $request, Response $response, $args) use ($getIngredients) {
        $id = $args['id'];
        $db = getDB();
        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions 
                FROM foods f 
                JOIN categories c ON f.category_id = c.category_id 
                JOIN origins o ON f.origin_id = o.origin_id 
                WHERE f.food_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $food = $stmt->fetch();

        if ($food) {
            $food['ingredients'] = $getIngredients($db, $food['food_id']);
            $response->getBody()->write(json_encode($food));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Food not found" 
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404); 
        }
    });

    // 7. Search Food by Name
    $group->get('/foods/search/{name}', function (Request $request, Response $response, $args) use ($getIngredients) {
        $name = "%" . $args['name'] . "%";
        $db = getDB();
        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions 
                FROM foods f 
                JOIN categories c ON f.category_id = c.category_id 
                JOIN origins o ON f.origin_id = o.origin_id 
                WHERE f.food_name LIKE :name";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $foods = $stmt->fetchAll();

        foreach ($foods as &$food) {
            $food['ingredients'] = $getIngredients($db, $food['food_id']);
        }

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 8. Get All Categories
    $group->get('/categories', function (Request $request, Response $response) {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll();
        $response->getBody()->write(json_encode($categories));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 9. Get All Ingredients
    $group->get('/ingredients', function (Request $request, Response $response) {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM ingredients");
        $ingredients = $stmt->fetchAll();
        $response->getBody()->write(json_encode($ingredients));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 10. Add New Food
    $group->post('/foods', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $db = getDB();

        try {
            $db->beginTransaction();

            $sql = "INSERT INTO foods (food_name, category_id, origin_id, instructions) VALUES (:food_name, :category_id, :origin_id, :instructions)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':food_name', $data['food_name']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':origin_id', $data['origin_id']);
            $stmt->bindParam(':instructions', $data['instructions']);
            $stmt->execute();

            $food_id = $db->lastInsertId();

            if (isset($data['ingredient_ids']) && is_array($data['ingredient_ids'])) {
                $sqlIng = "INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (:food_id, :ingredient_id)";
                $stmtIng = $db->prepare($sqlIng);
                foreach ($data['ingredient_ids'] as $ingredient_id) {
                    $stmtIng->bindParam(':food_id', $food_id);
                    $stmtIng->bindParam(':ingredient_id', $ingredient_id);
                    $stmtIng->execute();
                }
            }

            $db->commit();

            $response->getBody()->write(json_encode([
                "status" => "success",
                "message" => "Food added successfully." 
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201); 

        } catch (Exception $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Failed to add food: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

})->add($authMiddleware);

$app->run();
