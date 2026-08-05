# Filipino Cook Book API
# Description
The Filipino Cookbook API offers secure, token-protected access to a database of Filipino recipes, ingredients, food categories, and regional origins. It processes client requests and returns lightweight JSON responses for simple front-end integration.
# Main Features
-  Retrieve Filipino foods
-  Retrieve food categories
-  Retrieve food origins
-  Retrieve ingredients
-  View the details of a specific food
-  Authenticate requests using a token
-  Return information in JSON format
# Technologies Used 
- PHP
- Slim Framework
- MySQL
- Composer
- JSON
- Apache (XAMPP)
- Thunder Client/Postman
- Git
- GitHub

# Installation Instructions
1. Clone the repository.
   - git clone https://github.com/username/filipino-cookbook-api-centino.git
   - cd filipino-cookbook-api-centino
2. Open File in Vs Code
3. Open Terminal and install dependencies, type "composer install" then enter.
4. After installing dependencies run your local server for thunder client checking.
   - php -S localhost:8000 -t public
# Set up the database
   1.  Open SQLyog(or your choice of database) and connect to your local MySQL instance (localhost, user: root).
   2.  Create a new database named filipino_cookbook_api.
   3.  Open and execute the provided filipino_foods_relational.sql file in the sqlyog.
   4.  Update the database credentials in publix/index.php with your own database credentials.
   5.  Start services: Ensure Apache and MySQL modules are running in the XAMPP Control Panel.
# Base Url
   http://localhost/filipino-cookbook-api/public/api 
   
   Change the filipino-cookbook-api if that it not the name you put in you api.
# Authentication
   Protected endpoints require an authorization token supplied in the request headers:
   - Authorization : Bearer PUT_YOUR_ACCESS_TOKEN_HERE
   
# Endpoint Documentation 
### Endpoint Reference

| Endpoint | Method | Description | Required Headers |
| --- | --- | --- | --- |
| `/api/foods` | **GET** | Retrieves all available Filipino foods. | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Accept: application/json` |
| `/api/foods/{id}` | **GET** | Retrieves details for a specific food item by its ID. | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Accept: application/json` |
| `/api/foods/search` | **GET** | Searches for foods by name (`name=adobo`). | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Accept: application/json` |
| `/api/categories` | **GET** | Retrieves all food categories. | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Accept: application/json` |
| `/api/ingredients` | **GET** | Retrieves a list of all available ingredients. | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Accept: application/json` |
| `/api/foods` | **POST** | Adds a new food item to the database. | `Authorization: Bearer YOUR_ACCESS_TOKEN` <br> `Content-Type: application/json` |

# Sample Responses
1. Sample Request GET http://localhost:8000/api/categories
 
200 OK response
```json
{
  "status": "success",
  "data": [
    {
      "category_id": 1,
      "category_name": "Appetizer"
    },
    {
      "category_id": 2,
      "category_name": "Dessert"
    },
    {
      "category_id": 3,
      "category_name": "Grilled Dish"
    },
    {
      "category_id": 4,
      "category_name": "Main Dish"
    },
    {
      "category_id": 5,
      "category_name": "Noodle Dish"
    },
    {
      "category_id": 6,
      "category_name": "Soup"
    },
    {
      "category_id": 7,
      "category_name": "Vegetable Dish"
    }
  ]
}
```
 404 Error response
```json {
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```
# HTTP Status Codes

| Status Code | Meaning |
|---|---|
| 200 | Request completed successfully |
| 400 | Invalid request or parameter |
| 401 | Missing or invalid authentication |
| 403 | Access is not allowed |
| 404 | Requested resource was not found |
| 429 | Too many requests |
| 500 | Internal server error |
