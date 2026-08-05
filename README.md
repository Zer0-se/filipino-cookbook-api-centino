## Description
The Filipino Cookbook API offers secure, token-protected access to a database of Filipino recipes, ingredients, food categories, and regional origins. It processes client requests and returns lightweight JSON responses for simple front-end integration.

- **Intended Users:** Web and mobile application developer
- **Main Functions:** Fetch food items, view detailed dish information, and list categories, origins, and ingredients
- **Technologies Used:** PHP, Slim Framework, MySQL, Composer, JSON, Apache (XAMPP), Thunder Client, Git, GitHub

# How to run
1. Clone the repository
2. Open Terminal and install dependencies, type composer install then enter.
3. Set up the database
   a. Open SQLyog(or your choice of database) and connect to your local MySQL instance (localhost, user: root).
   b. Create a new database named filipino_cookbook_api.
   c. Open and execute the provided filipino_foods_relational.sql file in the sqlyog.
4. Copy config_sample.php to a new file name config.php
5. Start services: Ensure Apache and MySQL modules are running in the XAMPP Control Panel.

# Database Setup
Database Name: filipino_cookbook_api
SQL Schema File: filipino_foods_relational.sql
Entity Relationships:
categories → foods ← origins
foods → food_ingredients ← ingredients
