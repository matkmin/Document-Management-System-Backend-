# Document Management System (DMS) Backend

## Project Description
This is the backend for a Document Management System (DMS) built with Laravel. It provides a robust API for managing documents, departments, and user roles, serving as the core infrastructure for the DMS frontend.

## Features
- **User Authentication**: Secure login and registration using Laravel Sanctum.
- **Role-Based Access Control (RBAC)**: Distinct roles for Admin, Manager, and Employee managed via Spatie Permission.
- **Document Management**: Full CRUD operations for documents (Upload, View, Update, Delete).
- **Categorization**: Organize documents by categories (e.g., Policy, Report) and departments.
- **File Handling**: Secure file storage and download capabilities.

## Tech Stack
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Database**: SQLite (Default for development) / MySQL compatible
- **Testing**: PHPUnit

## Setup and Installation

1.  **Clone the repository**
    ```bash
    git clone <repository-url>
    cd DMS-BACKEND
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Environment Configuration**
    Copy the example environment file and configure your database settings:
    ```bash
    cp .env.example .env
    ```

4.  **Generate App Key**
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations and Seeders**
    This will set up the database structure and populate it with test data (users, roles, departments).
    ```bash
    php artisan migrate --seed
    ```

6.  **Serve the Application**
    ```bash
    php artisan serve
    ```
    The API will be available at `http://localhost:8000`.

## Running Tests
To ensure the application is functioning correctly, you can run the included feature tests. These tests cover user authentication, document management, and RBAC scenarios.

```bash
php artisan test
```

## Test Credentials
The database seeder creates the following default users for testing. All passwords are `password`.

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@dms.com` | `password` |
| **Manager (Human Resources)** | `manager_humanresources@dms.com` | `password` |
| **Manager (Finance)** | `manager_finance@dms.com` | `password` |
| **Manager (IT)** | `manager_informationtechnology@dms.com` | `password` |
| **Employee** | `employee0@dms.com` | `password` |

## API Endpoint Documentation

Base URL: `/api`

### 1. Authentication

#### Login
- **URL**: `/login`
- **Method**: `POST`
- **Body**:
  ```json
  {
      "email": "admin@dms.com",
      "password": "password"
  }
  ```
- **Response**:
  ```json
  {
      "access_token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
      "token_type": "Bearer"
  }
  ```

#### Register
- **URL**: `/register`
- **Method**: `POST`
- **Body**:
  ```json
  {
      "name": "John Doe",
      "email": "john@dms.com",
      "password": "password",
      "password_confirmation": "password",
      "department_id": 1
  }
  ```

#### Logout
- **URL**: `/logout`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer <token>`
- **Response**: `200 OK`

---

### 2. Documents

**Headers**: All document endpoints require `Authorization: Bearer <token>`

#### List Documents
- **URL**: `/documents`
- **Method**: `GET`
- **Query Parameters**:
  - `search`: (string) Search by title or description
  - `category_id`: (int) Filter by category
  - `department_id`: (int) Filter by department
  - `sort_by`: `created_at` (default), `title`
  - `sort_direction`: `desc` (default), `asc`
- **Example**: `/api/documents?search=report&category_id=2`
- **Response**:
  ```json
  {
      "current_page": 1,
      "data": [
          {
              "id": 1,
              "title": "Annual Report",
              "access_level": "public",
              "created_at": "2024-01-01T12:00:00.000000Z",
              "uploader": { "id": 1, "name": "Admin" },
              "category": { "id": 2, "title": "Report" }
          }
      ],
      "total": 15
  }
  ```

#### Upload Document
- **URL**: `/documents`
- **Method**: `POST`
- **Content-Type**: `multipart/form-data`
- **Body**:
  - `title`: (string) "Q1 Financial Report"
  - `description`: (string, optional) "Confidential"
  - `file`: (file) [The document file PDF/DOCX]
  - `document_category_id`: (int) 1
  - `department_id`: (int) 1
  - `access_level`: `public` | `department` | `private`

#### Download Document
- **URL**: `/documents/{id}/download`
- **Method**: `GET`
- **Response**: Binary file stream

#### Delete Document
- **URL**: `/documents/{id}`
- **Method**: `DELETE`
- **Response**:
  ```json
  { "message": "Document deleted successfully" }
  ```

---

### 3. Master Data

#### List Categories
- **URL**: `/categories`
- **Method**: `GET`
- **Response**: List of categories (`id`, `title`, `description`)

#### List Departments
- **URL**: `/departments`
- **Method**: `GET`
- **Response**: List of departments (`id`, `name`)

## Postman Usage Guide

### 1. Setup Authorization
Most endpoints require a Bearer Token.
1.  **Login**: Send a `POST` request to `http://localhost:8000/api/login` with your email and password.
2.  **Copy Token**: Copy the `access_token` from the response.
3.  **Configure Postman**:
    - Go to the **Authorization** tab of your request (or Collection).
    - Select **Type**: `Bearer Token`.
    - Paste your token into the **Token** field.

### 2. Uploading a Document (Multipart)
Resulting in an endpoint like `POST /api/documents`:
1.  Set **Method** to `POST`.
2.  Set **URL** to `http://localhost:8000/api/documents`.
3.  Go to the **Body** tab.
4.  Select **form-data**.
5.  Add the following keys:
    - `title` (Text): e.g., "Project Plan"
    - `document_category_id` (Text): e.g., "1"
    - `department_id` (Text): e.g., "1"
    - `access_level` (Text): e.g., "public"
    - `file` (File): **Important**: Hover over the "Key" field type (default is Text) and change it to **File**. Then select your file (PDF/DOCX) in the "Value" column.
6.  Click **Send**.

### 3. Downloading a Document
1.  Set **Method** to `GET`.
2.  Set **URL** to `http://localhost:8000/api/documents/{id}/download` (replace `{id}` with actual ID).
3.  Click **Send**.
4.  In the response pane, Postman may show binary garbage. Click the **"Save Response"** (down arrow) button to save the file to your computer to verify it opens correctly.

### 4. Deleting a Document
1.  Set **Method** to `DELETE`.
2.  Set **URL** to `http://localhost:8000/api/documents/{id}`.
3.  Click **Send**.
4.  You should receive a `200 OK` with `{"message": "Document deleted successfully"}`.
