# SPMS Backend

## Overview
The SPMS (Security and Property Management System) Backend is a Laravel-based application designed to manage visitor and vehicle check-ins and check-outs, as well as track goods associated with these visits. This system aims to streamline the process of managing entries and exits in a secure environment.

## Features
- **Visitor Management**: Register and manage visitors, including their details and visit purposes.
- **Vehicle Management**: Track vehicles entering and exiting the premises, along with driver information.
- **Goods Tracking**: Manage goods items associated with visits, including creation, updating, and deletion of goods records.
- **Check-In and Check-Out**: Efficiently handle the check-in and check-out processes for both visitors and vehicles.

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```
   cd spms-backend
   ```
3. Install dependencies:
   ```
   composer install
   ```
4. Set up your environment file:
   ```
   cp .env.example .env
   ```
5. Generate the application key:
   ```
   php artisan key:generate
   ```
6. Run migrations to set up the database:
   ```
   php artisan migrate
   ```

## API Endpoints
- **Visitors**
  - `POST /api/v1/admin/visitors`: Register a new visitor.
  - `GET /api/v1/admin/visitors`: List all visitors.

- **Vehicles**
  - `POST /api/v1/admin/drivers`: Register a new driver.
  - `GET /api/v1/admin/drivers`: List all drivers.

- **Goods Tracking**
  - `POST /api/v1/security/goods`: Create a new goods item.
  - `GET /api/v1/security/goods`: List all goods items.
  - `PUT /api/v1/security/goods/{id}`: Update an existing goods item.
  - `DELETE /api/v1/security/goods/{id}`: Delete a goods item.

## Usage
To use the API, send requests to the specified endpoints with the required parameters. Ensure that you handle authentication and authorization as needed.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.