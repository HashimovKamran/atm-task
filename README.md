# Laravel ATM API

A RESTful API simulating basic Automated Teller Machine (ATM) operations, built with Laravel. This project allows users (simulating both standard API consumers and ATM devices) to authenticate, check balances, withdraw funds according to configurable banknote denominations, and view transaction history. Administrators can manage certain aspects like creating accounts and soft-deleting transactions.

## Features

*   **Dual Authentication:**
    *   **User Login:** Standard email/password authentication for regular API consumers.
    *   **ATM Login:** Card number/PIN authentication simulating physical ATM access.
*   **JWT Authentication:** Secure API access using `tymon/jwt-auth` with distinct token types (`user_session`, `atm_session`).
*   **Account Management:**
    *   View account balance and details.
    *   View transaction history with custom pagination.
*   **Withdrawal Logic:**
    *   Withdraw funds based on available balance.
    *   Dispenses cash using configurable banknote denominations (e.g., 100, 50, 20 AZN).
    *   Tracks physical banknote inventory in the database.
    *   Minimum/maximum withdrawal limits per transaction.
    *   Ensures withdrawal amount is a multiple of the smallest dispensable unit.
*   **Transaction History:** Logs all successful and failed withdrawal attempts.
*   **Admin Functionality:**
    *   Role-based access control (`admin` vs `customer`).
    *   Admin ability to soft-delete transactions.
    *   Admin ability to create new accounts (both user-linked and ATM-only).
*   **API Design:**
    *   RESTful principles.
    *   API Versioning (`/api/v1/...`).
    *   Layered Architecture (Controller -> Service -> Repository).
    *   Dependency Injection.
    *   Custom Exception Handling with standardized JSON responses.
    *   API Resources for consistent response formatting.
    *   Custom Pagination implementation.
*   **Database:** MySQL with Eloquent ORM and Migrations. Soft Deletes implemented for transactions.
*   **Testing:** Feature tests using PHPUnit and Laravel's testing utilities.

## Technologies Used

*   **PHP** (^8.1 or higher recommended for Enums)
*   **Laravel** (^8.0 - adaptable)
*   **MySQL** (Database)
*   **tymon/jwt-auth** (for JWT authentication)
*   **Composer** (for dependency management)
*   **Git** (for version control)

## Setup and Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/HashimovKamran/atm-task.git
    cd atm-task
    ```

2.  **Install Composer Dependencies:**
    ```bash
    composer install
    ```

3.  **Create Environment File:**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Generate the application key:
        ```bash
        php artisan key:generate
        ```

4.  **Configure Environment (`.env` file):**
    *   Set up your **MySQL** database connection details (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD). Ensure the database exists.
    *   Configure ATM settings (optional, defaults are in `config/atm.php`):
        ```dotenv
        ATM_CURRENCY=AZN
        ATM_MIN_WITHDRAWAL=5
        ATM_MAX_WITHDRAWAL_PER_TX=1000
        ATM_SMALLEST_UNIT=5
        ```
    *   Generate and set the JWT secret:
        ```bash
        php artisan jwt:secret
        ```
        *(Copy the generated secret into the `JWT_SECRET=` line in your `.env` file)*.
    *   Configure JWT TTL (optional, defaults in `config/jwt.php`):
        ```dotenv
        JWT_TTL=60 # Token lifetime in minutes
        JWT_REFRESH_TTL=20160 # Refresh lifetime in minutes (e.g., 2 weeks)
        ```

5.  **Run Database Migrations:**
    ```bash
    php artisan migrate
    ```

6.  **Seed the Database (Optional but Recommended):**
    *   This will create an admin user, sample customer users, ATM/User accounts, and initial banknote counts.
    *   Run the seeders:
        ```bash
        php artisan db:seed
        ```
    *   **Default Credentials (from Seeders):**
        *   Admin: `admin@example.com` / `password`
        *   Customer: `customer1@example.com` / `password`
        *   ATM Card: `1111222233334444` / `1234` (Verify in `AccountSeeder.php`)

7.  **Serve the Application (for local testing):**
    ```bash
    php artisan serve
    ```
    The API will typically be available at `http://127.0.0.1:8000/api/v1/...`.

## API Endpoints

All endpoints are prefixed with `/api/v1`.

**Authentication (`/auth`)**

*   `POST /auth/login` (User login: requires `email`, `password`)
*   `POST /auth/login/atm` (ATM login: requires `card_number`, `pin`)
*   `POST /auth/logout` (Requires valid JWT authentication)
*   `GET /auth/me` (Requires valid JWT authentication - returns User or Account info)

**Authenticated Actions (Require valid JWT)**

*   `GET /accounts/me` (Get account details associated with the token)
*   `GET /accounts/me/transactions` (Get transaction history - supports `index`, `size`, `from`, `type`, `start_date`, `end_date` query params)
*   `POST /withdrawals` (Initiate withdrawal - requires `amount` in body - Renamed from `/accounts/me/withdrawals` in the provided routes)

**Admin Actions (`/admin` - Require valid JWT & Admin Role)**

*   `POST /admin/accounts` (Create a new account)
*   `DELETE /admin/transactions/{transaction}` (Soft delete a transaction)

## Testing

1.  **Configure Testing Environment:**
    *   Create a `.env.testing` file.
    *   Configure it to use a **separate MySQL test database**. Enter the connection details (DB_DATABASE, DB_USERNAME, DB_PASSWORD etc.) for your test database in this file. **Do not use your development database for testing.**
        ```dotenv
        APP_ENV=testing
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1 # Or your test DB host
        DB_PORT=3306
        DB_DATABASE=your_test_db_name # Make sure this database exists
        DB_USERNAME=your_test_db_user
        DB_PASSWORD=your_test_db_password
        JWT_SECRET=a_different_testing_secret # Use a different secret for testing
        # Other testing specific env vars...
        ```
    *   Ensure `phpunit.xml` is configured to use the testing environment (it usually reads `.env.testing` by default if it exists).

2.  **Run Tests:**
    ```bash
    php artisan test
    ```
    *   To run tests from a specific file:
        ```bash
        php artisan test tests/Feature/Api/V1/AuthTest.php
        ```
    *   To run a specific test method:
        ```bash
        php artisan test --filter test_user_can_login_with_valid_credentials
        ```
