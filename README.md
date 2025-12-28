## Hall Management System

### Overview:

The Hall Management System is a standalone application designed to manage halls, rooms, and seat plans in an organized way. It provides administrators with tools to create halls, configure rooms, assign seats (e.g., 101-a, 101-b, etc.), and manage hall-related operations efficiently.

### Installation:

1. Prerequisites:

    Ensure you have a local development environment set up listed prerequisites:
    - PHP >= 8.2
    - php-curl
    - php-xml
    - php-mysql
    - php-mbstring
    - Laravel >= 10.0
    - MySql
    - Node >= 20.0
    - NPM >= 6.0
    - Composer >= 2.0
   
2. Clone the Repository:
     ```Bash
     git clone https://github.com/shagormh/hall.git
     ```  

3. Install Dependencies:
     ```Bash
     cd hall-seat-management
     composer install
     npm install
     ```
4. **Configuration:**

    - Database Configuration:

      Copy `env.example` file and Edit the `.env` file to configure your database connection details (`host, database name, username, password`).
      Consider using a secure environment variable management solution for production environments.

    - Application Configuration:

      Review the **config** directory for any additional application-specific configuration files.

5. **Database Migrations:**

   Run the following command to create the database tables:**
    ```bash
    php artisan migrate
    ```
6. **Run the seeder:**

   Run the following command to seed the database with sample data:
    ```bash
    php artisan db:seed
    ```
7. **Set the APP_ENV:**

    Edit the `.env` to set `APP_ENV`.
    For the production server, set APP_ENV=production
    For the staging server, set APP_ENV=staging
    For local or development server, set APP_ENV=local

8. **Start the Application:**

   Run the following command to start the application:
    ```bash
    php artisan serve --port=8000
    ```
   The application will be accessible at `http://localhost:8000`.
   
9. **Administrator Access:**  
   Log in with an administrator account to start managing halls, rooms, and seat plans. The dashboard provides tools for creating new rooms, auto-generating seat layouts, and keeping operations organized.

### Code Structure
- Have to strictly follow SOLID and DRY principles.
- Method should not have more than 15 lines of code with valid exception. Comment and line break will not be counted.
- Business logic will be on service class. There will be multiple services under namespaces if required


### Usage:

The Hall Management System is used by administrators to:
- Manage halls and rooms  
- Define and organize seat plans (e.g., 101-a, 101-b, 101-c, 101-d)  
- Keep hall operations structured and efficient  

