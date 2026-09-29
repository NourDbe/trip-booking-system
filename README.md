# Trip Booking System

A REST API for an intercity trip booking system built with Laravel.

The system allows customers to browse available trips, view available seats, create bookings, confirm or cancel bookings, and prevents the same seat from being booked more than once.

This project was built as an API-only Laravel application with no Blade-based user interface.

---

## Features

- View upcoming trips
- View trip details
- View available seats for each trip
- Create customers
- Create bookings
- Confirm bookings
- Cancel bookings
- Prevent double booking
- Make cancelled seats available again
- Support Regular and VIP trip pricing
- Consistent JSON API responses
- Custom exception handling
- Proper HTTP status codes
- Automated feature tests

---

## Tech Stack

- PHP
- Laravel
- MySQL
- Eloquent ORM
- REST API
- PHPUnit / Laravel Testing

---

## Project Architecture

The project follows a simple layered architecture:

```text
API Route
    ↓
Controller
    ↓
Service
    ↓
Eloquent Model
    ↓
Database
```

Controllers are intentionally kept lightweight.

Business logic such as booking creation, seat availability, pricing, booking confirmation, cancellation, and double-booking prevention is handled inside service classes.

The controllers do not contain the core business logic.

---

## API-Only Application

All application endpoints are defined inside:

```text
routes/api.php
```

The project does not use Blade views for the booking system.

The main application is designed to be consumed later by a frontend or mobile application.

---

## Database Design

The main entities are:

```text
City
Trip
Seat
Customer
Booking
```

### Relationships

```text
City
 ├── has many origin trips
 └── has many destination trips

Trip
 └── has many seats

Seat
 ├── belongs to a trip
 └── has many bookings

Customer
 └── has many bookings

Booking
 ├── belongs to a customer
 └── belongs to a seat
```

A booking reaches its trip through its seat:

```text
Booking
   ↓
Seat
   ↓
Trip
```

This avoids storing the same trip relationship twice inside the booking table and keeps the database design normalized.

---

## Main Database Tables

### cities

Stores available cities.

Important fields:

```text
id
name
timestamps
```

---

### trips

Stores trip information.

Important fields:

```text
id
origin_city_id
destination_city_id
departure_at
base_price
type
timestamps
```

`origin_city_id` and `destination_city_id` reference the `cities` table.

---

### seats

Stores seats belonging to a specific trip.

Important fields:

```text
id
trip_id
seat_number
timestamps
```

The combination of:

```text
trip_id + seat_number
```

is unique, so the same seat number cannot be duplicated inside the same trip.

---

### customers

Stores customer information.

Important fields:

```text
id
name
phone
timestamps
```

The phone number is unique.

---

### bookings

Stores booking history.

Important fields:

```text
id
customer_id
seat_id
status
price
timestamps
```

The booking price is stored at booking time so that changing a trip price later does not modify previous bookings.

---

## Booking Status

Booking statuses are represented using a PHP Enum:

```text
Pending
Confirmed
Cancelled
```

The enum is located at:

```text
app/Enums/BookingStatus.php
```

Pending and Confirmed bookings keep the seat reserved.

Cancelled bookings do not block the seat, so the seat can be booked again.

---

## Trip Type

Trip types are represented using:

```text
app/Enums/TripType.php
```

The supported types are:

```text
Regular
VIP
```

Trip type is used to determine the appropriate pricing behavior.

---

## Preventing Double Booking

One of the main business rules is preventing two customers from booking the same seat.

Booking creation runs inside a database transaction:

```php
DB::transaction(...)
```

The requested seat is locked using:

```php
lockForUpdate()
```

Before creating the booking, the application checks whether the seat already has an active booking with one of these statuses:

```text
pending
confirmed
```

Cancelled bookings do not block the seat.

### Example Flow

```text
Request A
   ↓
Lock Seat
   ↓
Check active booking
   ↓
Create booking
   ↓
Commit transaction
   ↓
Unlock Seat
```

If another request tries to book the same seat at the same time:

```text
Request B
   ↓
Wait for Seat Lock
   ↓
Check active booking again
   ↓
Existing booking found
   ↓
409 Conflict
```

This protects the application against double booking and race conditions.

---

## Service Layer

The project uses service classes to keep business logic outside controllers.

Main services include:

```text
BookingService
TripService
CustomerService
```

`BookingService` is responsible for:

- Creating bookings
- Preventing double booking
- Retrieving available seats
- Confirming bookings
- Cancelling bookings
- Choosing the correct pricing behavior

---

## Service Container

The project uses Laravel's Service Container for dependency injection.

The pricing abstraction is:

```text
PricingCalculatorInterface
```

located at:

```text
app/Contracts/PricingCalculatorInterface.php
```

The default implementation is registered using `bind()`:

```php
$this->app->bind(
    PricingCalculatorInterface::class,
    RegularPricingCalculator::class
);
```

This means classes depend on the abstraction instead of manually creating a concrete pricing calculator.

---

## Contextual Binding

Regular and VIP trips require different pricing behavior.

Both pricing implementations use the same interface:

```text
PricingCalculatorInterface
```

However, `VipTripPricingService` requires a different implementation.

Laravel Contextual Binding is used:

```php
$this->app
    ->when(VipTripPricingService::class)
    ->needs(PricingCalculatorInterface::class)
    ->give(VipPricingCalculator::class);
```

The resulting behavior is:

```text
RegularTripPricingService
        ↓
RegularPricingCalculator
```

and:

```text
VipTripPricingService
        ↓
VipPricingCalculator
```

This gives Contextual Binding a real purpose inside the application instead of using it only as a demonstration.

---

## Pricing

Regular trips use the base trip price.

Example:

```text
Base Price: 100.00
Final Price: 100.00
```

VIP trips use a 25% increase.

Example:

```text
Base Price: 120.00

120 × 1.25 = 150.00
```

The final calculated price is stored in the booking.

---

## Why No Repository Pattern?

The project intentionally does not use the Repository Pattern.

Laravel Eloquent already provides the Active Record pattern for database interaction.

For this project there is no changing data source or complex repeated data-access logic that justifies adding another Repository abstraction.

Instead:

```text
Controller
    ↓
Service
    ↓
Eloquent
```

Eloquent is used inside the service layer, while controllers remain focused on handling HTTP requests and responses.

This avoids unnecessary abstraction and keeps the architecture simple.

---

## Interface, Trait and Enum Usage

### Interfaces

```text
PricingCalculatorInterface
```

Used to define a common contract for pricing implementations.

---

### Traits

```text
HasApiResponse
```

Used by controllers to return consistent JSON responses.

---

### Enums

```text
BookingStatus
TripType
```

Used to avoid uncontrolled string values for booking statuses and trip types.

---

## API Response Format

Successful API responses follow a consistent structure:

```json
{
    "success": true,
    "message": "Booking created successfully.",
    "data": {}
}
```

Error responses follow this structure:

```json
{
    "success": false,
    "message": "Validation failed.",
    "data": null,
    "errors": {}
}
```

---

## HTTP Status Codes

The API uses appropriate HTTP status codes, including:

```text
200 OK
201 Created
404 Not Found
409 Conflict
422 Unprocessable Content
```

Examples:

```text
Successful request          → 200
Booking created             → 201
Resource not found          → 404
Seat already booked         → 409
Validation failed           → 422
```

---

## Exception Handling

Business errors are handled using custom exceptions.

Examples include:

```text
SeatAlreadyBookedException
SeatNotFoundException
TripNotFoundException
BookingNotFoundException
InvalidBookingStatusException
```

API errors are returned as JSON responses instead of Laravel HTML error pages.

Validation errors and unknown API routes also return structured JSON responses.

---

# API Endpoints

## Trips

### Get Upcoming Trips

```http
GET /api/trips
```

Returns available upcoming trips.

---

### Get Trip Details

```http
GET /api/trips/{tripId}
```

Returns details for a specific trip.

---

### Get Available Seats

```http
GET /api/trips/{tripId}/available-seats
```

Returns seats that do not currently have a Pending or Confirmed booking.

---

## Customers

### Create Customer

```http
POST /api/customers
```

Example request body:

```json
{
    "name": "Test Customer",
    "phone": "0999555555"
}
```

---

## Bookings

### Create Booking

```http
POST /api/trips/{tripId}/bookings
```

Example request:

```json
{
    "customer_id": 1,
    "seat_id": 1
}
```

A newly created booking starts with:

```text
pending
```

---

### Get Booking

```http
GET /api/bookings/{bookingId}
```

Returns booking information including customer, seat, trip, origin city, destination city, status, and price.

---

### Confirm Booking

```http
PATCH /api/bookings/{bookingId}/confirm
```

Only a Pending booking can be confirmed.

Flow:

```text
Pending
   ↓
Confirmed
```

---

### Cancel Booking

```http
PATCH /api/bookings/{bookingId}/cancel
```

Pending or Confirmed bookings can be cancelled.

After cancellation:

```text
Cancelled
   ↓
Seat becomes available again
```

---

## Validation

Laravel Form Requests are used for input validation.

Examples include:

```text
StoreBookingRequest
StoreCustomerRequest
```

The client cannot manually provide sensitive business values such as booking price.

The system calculates the price internally.

---

## Automated Tests

The project includes Feature Tests for the main booking workflow.

The tests verify:

```text
✓ Available seats are returned
✓ A booking can be created
✓ Double booking is prevented
✓ A booking can be confirmed
✓ Cancelling a booking makes the seat available again
✓ VIP bookings use VIP pricing
```

Run all tests with:

```bash
php artisan test
```

Current test result:

```text
7 passed
25 assertions
```

---

# Installation

## 1. Clone the Repository

```bash
git clone https://github.com/NourDbe/trip-booking-system.git
```

Enter the project directory:

```bash
cd trip-booking-system
```

---

## 2. Install Dependencies

```bash
composer install
```

---

## 3. Create Environment File

Copy:

```text
.env.example
```

to:

```text
.env
```

On Windows this can be done manually or using:

```bash
copy .env.example .env
```

---

## 4. Generate Application Key

```bash
php artisan key:generate
```

---

## 5. Configure Database

Create a MySQL database, for example:

```text
trip_booking_system
```

Then configure `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trip_booking_system
DB_USERNAME=root
DB_PASSWORD=
```

---

## 6. Run Migrations

```bash
php artisan migrate
```

Or run migrations with sample data:

```bash
php artisan migrate --seed
```

---

## 7. Start the Server

```bash
php artisan serve
```

The application normally runs at:

```text
http://127.0.0.1:8000
```

The API is available under:

```text
http://127.0.0.1:8000/api
```

---

## Sample Data

The project contains seed data for development and testing.

Sample data includes:

```text
Cities
Regular Trip
VIP Trip
Seats
Customers
```

To completely recreate the database with sample data:

```bash
php artisan migrate:fresh --seed
```

> Warning: `migrate:fresh` deletes all existing database data.

---

## Useful Commands

List API routes:

```bash
php artisan route:list --path=api
```

Run tests:

```bash
php artisan test
```

Clear Laravel caches:

```bash
php artisan optimize:clear
```

Run the server:

```bash
php artisan serve
```

---

## Main Concepts Demonstrated

This project demonstrates:

- Laravel REST API development
- Database normalization
- Foreign Keys
- Eloquent relationships
- Service Layer
- Dependency Injection
- Laravel Service Container
- `bind()`
- Contextual Binding
- Interfaces
- Traits
- Enums
- Custom Exceptions
- JSON error handling
- HTTP status codes
- Database Transactions
- Row Locking
- Race Condition prevention
- Double Booking prevention
- Feature Testing

---

## Repository

GitHub:

```text
https://github.com/NourDbe/trip-booking-system
```
