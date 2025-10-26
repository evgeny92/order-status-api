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
git clone https://github.com/evgeny92/order-tracking-api.git
cd order-tracking-api

# 2. Copy .env file
cp .env.example .env

# 3. Run containers
docker compose up -d

# 4. Install dependencies
docker compose exec php composer install

# 5. Run migrations
docker compose exec php php artisan migrate

# 6. Start the queue
docker compose exec php php artisan queue:work

# 7. Check tests
docker compose exec php php artisan test

The project will be available at:
👉 http://localhost:8002
