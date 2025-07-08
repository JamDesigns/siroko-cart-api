# **E-commerce Cart and Checkout API**
This repository implements the basic functionality for a shopping cart and checkout system in an e-commerce platform. It includes features to manage products in the cart, process checkout, and generate orders. The architecture is designed with **Hexagonal Architecture** (ports and adapters) and **Domain-Driven Design (DDD)** principles, ensuring scalability and flexibility.

## ***Project Overview***
The goal of this project is to develop the **cart** and **checkout** system of an e-commerce platform. Users can add, update, or remove products from the cart, and then process the checkout to generate an order. The system is designed as a **decoupled API** that will later integrate with the user interface. The code is built using principles of **CQRS** (Command Query Responsibility Segregation) and **DDD** (Domain-Driven Design), ensuring a clean, scalable, and easily maintainable architecture.

## ***OpenAPI Specification***
The OpenAPI specification is not available as a yaml file, but the following endpoints are implemented in the API:

### **Cart Operations**
**POST /cart/{cartId}/items**:

Adds a product to the cart.

Request body: {"product": "product\_id", "quantity": 2, "unitPrice": 100}

Success: Product added to the cart.

Error: Cart not found or invalid input.

**PATCH /cart/{cartId}/items/{productId}**:

Updates the quantity of an existing product in the cart.

Request body: {"quantity": 3}

Success: Product quantity updated.

Error: Product not found in the cart.

**DELETE /cart/{cartId}/items/{productId}**:

Removes a product from the cart.

Success: Product removed from the cart.

Error: Product not found in the cart.

**GET /cart/{cartId}**:

Retrieves all the items in the cart.

Success: Returns all items in the cart.

Error: Cart not found.

### **Checkout Operations**
**POST /checkout/{cartId}**:

Processes the checkout and generates an order from the cart.

Success: Order created and confirmation returned.

Error: Cart is empty or cart not found.

### **Domain Events**
**ProductAddedToCart**: Dispatched when a product is added to the cart.

**ProductUpdatedInCart**: Dispatched when a product's quantity is updated in the cart.

**ProductRemovedFromCart**: Dispatched when a product is removed from the cart.

**CartEmptied**: Dispatched when the cart is emptied.

**OrderCreated**: Dispatched when an order is successfully created from the cart.

## ***Domain Model***
The domain model follows **Domain-Driven Design (DDD)** principles and consists of the following key entities:

**Cart**: Represents the shopping cart, which contains a collection of items.

**Product**: Represents a product in the cart.

**Order**: Represents a completed purchase that is generated from the cart.

### ***Domain Events***
**ProductAddedToCart**: Triggered when a product is added to the cart.

**ProductUpdatedInCart**: Triggered when the quantity of a product in the cart is updated.

**ProductRemovedFromCart**: Triggered when a product is removed from the cart.

**CartEmptied**: Triggered when the cart is emptied.

**OrderCreated**: Triggered when an order is successfully created from the cart.

## ***Technologies Used***
**PHP 8.4**: The main programming language used for the API.

**Symfony**: Framework used to handle the application logic.

**Doctrine**: ORM used for database persistence.

**PHPUnit**: For testing and ensuring code quality.

**Docker**: Used to containerize the application and manage dependencies.

## ***Setup Instructions***
### **Setting Up with Docker**

1. **Clone the repository**:

First, clone the repository to your local machine.

\```bash

git clone <https://github.com/JamDesigns/siroko-cart-api.git>

cd siroko-cart-api

2. **Install PHP dependencies**:

Run composer install to install the necessary PHP dependencies defined in composer.json.

\```bash

composer install

3. **Create and start containers**:

Use docker-compose to set up the environment. This will spin up the necessary services (e.g., database, application server).

\```bash

docker-compose up --build

This will build the containers and start the services. Make sure Docker is installed and running on your system.

If you face any issues, ensure you have Docker and Docker Compose installed on your system.

4. **Access the application**:

Once the containers are running, the application should be accessible via the exposed ports as defined in the docker-compose.yml file.

You can interact with the API via the following endpoint (depending on your configuration):

\```bash

http://localhost:8000

### ***Running Tests***

To ensure the code is working correctly and the logic is covered, you can run the tests using the following command:

\```bash

composer test

This will execute all unit and integration tests using **PHPUnit**, ensuring that everything works as expected.


## ***Architecture***

This system is designed following **Hexagonal Architecture**, where the core business logic (the domain) is decoupled from the external frameworks and infrastructure (like Symfony). The key components include:

**Domain**: Contains the core logic, including entities, aggregates, and domain events.

**Application**: Handles use cases and command handlers, where the business logic is executed.

**Infrastructure**: Deals with repositories, database interactions, and external dependencies.

The system also follows **CQRS** (Command Query Responsibility Segregation) principles, which separates read and write operations to better manage scalability and performance.


## ***Git Workflow***

**Feature Branches**: Each new feature is developed in its own feature branch.

**Pull Requests (PR)**: Once the feature is complete, open a PR to merge it into the master branch.

**Commit Messages**: Commit messages are written in the **imperative mood** and are kept concise and descriptive.


## ***Notes***

This is a **basic prototype** focused on the core functionality for the cart and checkout system. It is built with scalability in mind but additional features such as payment processing, user authentication, and advanced cart functionalities can be added later.

-----
**Siroko Senior Code Challenge**\
*Developed by: José Angel Mosquera Rodríguez*
