# 🧾 Order Tracking API — Laravel 12

**Purpose:** Technical assignment — API for order management and external status integration.

---

## 📖 Project Overview
This API provides functionality to create, update, and retrieve orders with tags and items.  
It also integrates with an external API to update order statuses, and uses **Laravel Events**, **Listeners**, and **Queued Jobs** to log notifications asynchronously.

**API Documentation:**  
👉 [View on Postman](https://documenter.getpostman.com/view/14049462/2sB3Wk14oG)

---

## 🚀 Features
- Create and manage **orders**, **tags**, and **order items**
- Retrieve full order details with relations
- Update order **status** and **tags**
- Event-driven notifications via **queues (jobs + listeners)**
- JSON-based responses and validation handling

---

## ⚙️ Tech Stack
- **Framework:** Laravel 12
- **Database:** MySQL 8
- **Queue:** Database
- **Containerization:** Docker + Docker Compose
- **Language:** PHP 8.3

---

## 🧩 Main Endpoints
| Method | Endpoint | Description |
|:-------|:----------|:-------------|
| 🟢 **POST** | `/api/v1/orders` | Create new order with tags and items |
| 🔵 **GET** | `/api/v1/orders` | List all orders (filter by status/tags) |
| 🔵 **GET** | `/api/v1/orders/{order_number}` | Get order details |
| 🟠 **POST** | `/api/v1/orders/status` | Update order status and/or tags |
| 🔵 **GET** | `/api/v1/orders/{order_number}/external` | Sync status from external API for TEST |


---

## ⚙️ Events & Queues
| Component | Purpose |
|------------|----------|
| **Event** | `OrderStatusChanged` — triggered after order status or tags change |
| **Listener** | `SendOrderUpdatedNotification` — handles event logic |
| **Job** | `SendOrderNotificationJob` — executes asynchronously and logs update message |

**Example log output:**
```log
[2025-10-26 11:10:26] local.INFO: Order updated {"order_id":2,"order_number":"ORD-4059","status":"shipped","tags":["New1","New2"],"updated_at":"2025-10-26 11:10:25"}
```


### 🧪 Test results

```bash
PASS  Tests\Feature\OrderCreateFeatureTest
✓ can create a new order                                               0.08s  
✓ order creation fails with empty fields                               0.01s  

PASS  Tests\Feature\OrderUpdateExternalFeatureTest
✓ updates order when external api returns success                      0.02s  
✓ return error when external api returns http error                    0.01s  
✓ logs exception when external api throws error                        0.01s  

PASS  Tests\Feature\OrderUpdateFeatureTest
✓ order updated event is dispatched when status and tags are changed   0.01s  

Tests:    6 passed (42 assertions)
Duration: 0.16s
```


## 🧱 Installation and launch

```bash
# 1. Clone the repository
git clone https://github.com/evgeny92/order-status-api.git
cd order-tracking-api

# 2. Copy .env file
cp .env.example .env

# 3. Build images
docker compose build

# 4. Run containers
docker compose up -d

# 5. Install dependencies
docker compose exec php composer install

# 6. Generate APP_KEY
docker compose exec php php artisan key:generate

# 7. Run migrations
docker compose exec php php artisan migrate

# 8. Start the queue
docker compose exec php php artisan queue:work

# 9. Check tests
docker compose exec php php artisan test

The project will be available at:
👉 http://localhost:8002

```

## ⚙️ Example .env configuration

```bash
APP_NAME="Order Status API"
APP_ENV=local
APP_KEY=      # generated via: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8002

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=order_status_api
DB_USERNAME=root
DB_PASSWORD=Pas123456

QUEUE_CONNECTION=database
SESSION_DRIVER=file
CACHE_STORE=file

# Sync user ID and group ID to avoid permission issues on development
UID=1000
GID=1000
