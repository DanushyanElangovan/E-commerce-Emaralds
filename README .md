# Emeralds: Saree E-commerce Store

A full-stack online store for sarees and ethnic wear, built with **PHP**, **MySQL** and **Bootstrap 5**. Customers can browse, search and buy; staff manage products, stock, orders and support tickets from an admin area with role-based access.

![Home page](docs/home.png)

## Features

**Storefront**
- Product catalogue with categories, search and product detail pages
- Cart, wishlist, checkout and order history
- Customer accounts with profile editing and product reviews
- Support ticket system with replies and attachments

**Admin and staff**
- Dashboard with order, product and user statistics
- Product, category, inventory and user management (CRUD)
- Reports and ticket handling
- Roles: System admin, CEO, inventory manager, sales manager, customer care

**Security**
- Passwords hashed with `password_hash` / `password_verify`
- PDO prepared statements throughout
- Session-based authentication with role checks on protected pages

## Screenshots

| Product page | Admin dashboard |
|---|---|
| ![Product](docs/product.png) | ![Admin](docs/admin.png) |

![Shop](docs/shop.png)

## Tech stack

| Layer | Tools |
|---|---|
| Backend | PHP 8, PDO |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Dev setup | Docker Compose (Apache + MariaDB) |

## Run it locally

You need [Docker Desktop](https://www.docker.com/products/docker-desktop).

```bash
git clone https://github.com/DanushyanElangovan/E-commerce-Emaralds.git
cd E-commerce-Emaralds
docker compose up
```

Open **http://localhost:8080/ecommerce/** (the first start takes about a minute while the database imports).

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin@123` |
| Customer | `demo` | `Demo@1234` |

To reset the database, run `docker compose down -v` and start again.

Without Docker: copy `ecommerce/` into XAMPP's `htdocs`, then import `ecommerce/if0_38952671_ecommerce.sql` into a database named `ecommerce_db`.

## Project structure

```
ecommerce/
├── index.php, shop.php, product.php, ...   storefront pages
├── customer/                               cart, checkout, orders, wishlist, tickets
├── admin/                                  dashboard, products, inventory, users, reports
├── includes/                               header, footer, DB connection, helpers
└── assets/                                 CSS, JS, product images
```

## About

Built as a full-stack PHP and MySQL project. The sample database contains demo data only.
