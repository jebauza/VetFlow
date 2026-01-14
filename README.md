<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

# VetFlow - Backend API

## 📖 Description

**VetFlow** is an all-in-one web platform designed to modernize veterinary clinic management. It allows you to easily handle the full flow of care: from scheduling appointments and surgeries to managing vaccines, doctors’ schedules, and online payments.

*   **Smart scheduling**: Easily organize appointments, vaccines, and surgeries.
*   **Doctor management**: Control your team’s schedules and availability in real time.
*   **Digital medical history**: Centralize information for every pet.
*   **Integrated payments**: Process charges and simplify the client experience.
*   **Reports and analytics**: Clear metrics for operational decisions.

This repository contains the backend REST API for VetFlow, built with **Laravel 11**. It is designed with a modular architecture and features a secure JWT-based authentication system, as well as a comprehensive roles and permissions system.

## ✨ Key Features

*   **Modular Architecture**: The code is organized into modules (Users, Roles, Authentication) to facilitate maintenance and scalability.
*   **JWT Authentication**: Secure and stateless authentication system using `php-open-source-saver/jwt-auth`.
*   **Roles and Permissions**: Role-based access control (RBAC) implemented with the popular `spatie/laravel-permission` package.
*   **API CRUDs**: Complete endpoints for managing Users and Roles.
*   **Automated Documentation**: Automatic API documentation generation with `rakutentech/laravel-request-docs`.
*   **Docker Environment**: Ready-to-use setup with Laravel Sail for fast and consistent local development.
*   **Database**: Ready to work with PostgreSQL, leveraging its advanced features.

## 🛠️ Tech Stack

*   Laravel 11
*   PostgreSQL
*   Laravel Sail (Docker)
*   JWT-Auth for Laravel
*   Spatie Laravel Permission
*   Laravel Request Docs

## 🚀 Getting Started

Follow these steps to set up the project in your local environment.

### Prerequisites

*   Docker
*   Composer

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://your-repository.git
    cd project-name
    ```

2.  **Copy the environment file:**
    ```bash
    cp .env.example .env
    ```
    *Make sure to configure the environment variables in the `.env` file if necessary (although with Sail, the default database settings usually work out of the box).*

3.  **Start the containers with Sail:**
    ```bash
    ./vendor/bin/sail up -d
    ```

4.  **Install Composer dependencies:**
    ```bash
    ./vendor/bin/sail composer install
    ```

5.  **Generate the application key:**
    ```bash
    ./vendor/bin/sail artisan key:generate
    ```

6.  **Generate the JWT secret:**
    ```bash
    ./vendor/bin/sail artisan jwt:secret
    ```

7.  **Run migrations and initial data load:**
    ```bash
    ./vendor/bin/sail artisan app:init --all
    ```

Done! The API should now be running at `http://localhost`.

## 📚 API Documentation

The API documentation is automatically generated and available at the following URL:

http://localhost/request-docs

From there, you can explore all endpoints, view validation rules, and test requests directly.

## 🧪 Testing

To run the automated test suite, use the following command:

```bash
./vendor/bin/sail artisan test
```

## 🧪 Telescope

http://localhost:8080/telescope

## 📄 License

This project is licensed under the MIT License. See the LICENSE.md file for details.
