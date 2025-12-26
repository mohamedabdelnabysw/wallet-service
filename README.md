# Wallet Service

A Laravel-based Wallet Service application.

## Prerequisites

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Postman](https://www.postman.com/) (for API testing)

## Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd wallet-service
   ```

2. **Environment Setup**
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

3. **Start the Application**
   Build and start the Docker containers:
   ```bash
   docker-compose up -d --build
   ```

4. **Install Dependencies**
   Install PHP dependencies:
   ```bash
   docker-compose exec wallet composer install
   ```

5. **Application Setup**
   Generate the application key:
   ```bash
   docker-compose exec wallet php artisan key:generate
   ```

   Run database migrations:
   ```bash
   docker-compose exec wallet php artisan migrate
   ```

   Seed the database:
   ```bash
   docker-compose exec wallet php artisan db:seed
   ```

   The application will be accessible at `http://localhost:8025`.

## API Documentation

A Postman collection is included in the repository: `wallet_service.postman_collection.json`.

1. Import the collection into Postman.
2. The collection uses a `base_url` variable. Ensure it is set to `http://localhost:8025/api`.

---
