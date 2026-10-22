# PetAdopt (petvet) — Pet Adoption, Veterinary Appointment & Pet Shop Management System

**PetAdopt** is a complete **pet adoption and veterinary clinic management web application** built with plain **PHP and MySQL**. It connects three kinds of users — pet adopters, pet store owners, and veterinarians — in one system: browse and adopt pets, book vet appointments based on real vet schedules, and buy pet products from the built-in shop.

Whether you're learning PHP/MySQL web development or looking for a starting point for a pet-care platform, this project shows a full multi-role CRUD application with authentication, role-based dashboards, and a relational database schema.

## Features

- **Pet adoption listings** — public homepage listing available pets with name, age, type, breed, color, and health status
- **Three user roles** — *adopter*, *store owner*, and *vet*, each with its own dashboard and permissions
- **User authentication** — registration, login, and logout with session-based access control
- **Adoption workflow** — adopt a pet in one click; every adoption is recorded in an adoption history table
- **Vet appointment booking** — book appointments against each vet's weekly schedule (available days and time range), stored as structured data
- **Pet shop** — store owners list products and adopters can purchase them (`shop.php` / `purchase_product.php`)
- **Relational MySQL schema** — normalized tables for users, pets, vet schedules, appointments, and adoption history, with foreign keys and indexes (see `schema.sql`)
- **Clean PHP with PDO** — prepared-statement style database access via PDO, no framework required

## Tech Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML + Bulma CSS framework (via CDN)
- **Server:** Apache or Nginx (any standard PHP hosting / XAMPP / WAMP stack)

## Getting Started

### Prerequisites

- PHP 7.4+ with the PDO MySQL extension enabled
- MySQL or MariaDB
- A local server stack such as XAMPP, WAMP, Laragon, or PHP's built-in server

### Installation

1. Clone the repository into your web server's document root:

   ```bash
   git clone https://github.com/sh00von/petvet.git
   ```

2. Create the database and tables by importing the schema:

   ```bash
   mysql -u root -p < schema.sql
   ```

   This creates the `pet_adoption` database with all required tables.

3. Adjust the database credentials in `config.php` if yours differ from the defaults (`localhost` / `root` / no password).

4. Run the app — with PHP's built-in server:

   ```bash
   php -S localhost:8000
   ```

   Then visit `http://localhost:8000` in your browser.

## Usage

1. **Register** an account as an adopter, store owner, or vet.
2. **Store owners** add pets and products from their dashboard.
3. **Adopters** browse available pets on the homepage, adopt a pet, book a vet appointment, and shop for products.
4. **Vets** set their weekly availability so adopters can book appointments in their open slots.

## Project Structure

```text
petvet/
├── index.php            # public homepage — available pets
├── register.php         # user registration
├── login.php / logout.php
├── adopt_pet.php        # adoption handler
├── book_vet.php         # vet appointment booking
├── shop.php             # pet product shop
├── purchase_product.php # purchase handler
├── dashboard/           # role-based dashboards
├── functions.php        # shared helpers (auth, redirects)
├── config.php           # database connection (PDO)
└── schema.sql           # MySQL database schema
```

---

⭐ If this project helps you learn PHP/MySQL development or build a pet-care app, please give it a star! Issues, ideas, and pull requests are welcome.
