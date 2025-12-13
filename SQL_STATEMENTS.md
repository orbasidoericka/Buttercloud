# SQL Statements - Buttercloud Bakery (ACID Implementation)

This document contains all SQL statements used in the Buttercloud Bakery shopping application, demonstrating ACID properties (Atomicity, Consistency, Isolation, Durability).

---

## **Transaction Control Statements**

### Start Transaction (ATOMICITY + DURABILITY)
```sql
BEGIN;
-- or
START TRANSACTION;
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Begins a database transaction to ensure all operations succeed or fail together

### Commit Transaction (DURABILITY)
```sql
COMMIT;
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Persists all transaction changes to disk permanently

### Rollback Transaction (ATOMICITY)
```sql
ROLLBACK;
```
**Location:** `CheckoutController.php` - `process()` method (catch block)  
**Purpose:** Undoes all changes if any operation fails

---

## **SELECT Queries**

### Get All Products
```sql
SELECT * FROM products;
```
**Location:** `ShopController.php` - `index()` method  
**Purpose:** Display all available products on the shop page  
**Laravel Code:** `Product::all()`

### Get Single Product
```sql
SELECT * FROM products WHERE id = ? LIMIT 1;
```
**Location:** 
- `ShopController.php` - `cart()` method
- `CheckoutController.php` - `index()` method

**Purpose:** Retrieve specific product details  
**Laravel Code:** `Product::find($id)`

### Get Product with Exclusive Lock (ISOLATION)
```sql
SELECT * FROM products WHERE id = ? FOR UPDATE;
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Lock product row to prevent race conditions during checkout  
**ACID Property:** **ISOLATION** - Prevents concurrent transactions from modifying the same product  
**Laravel Code:** `Product::lockForUpdate()->find($productId)`

**How it prevents race conditions:**
1. Transaction A locks product row
2. Transaction B tries to read same product → **WAITS**
3. Transaction A completes (commits or rolls back)
4. Transaction B can now proceed
5. Result: No overselling, no negative stock

### Get Orders with Items (JOIN)
```sql
-- Main query (filtered by authenticated user)
SELECT * FROM orders 
WHERE user_id = ?
ORDER BY created_at DESC 
LIMIT 10 OFFSET 0;

-- Related items query
SELECT * FROM order_items 
WHERE order_id IN (?, ?, ...);
```
**Location:** `CheckoutController.php` - `history()` method  
**Purpose:** Display order history for current user only with pagination  
**Laravel Code:** `Order::with('items')->where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(10)`  
**Security:** Users can only view their own orders, not all orders

---

## **INSERT Queries**

### Create Order
```sql
INSERT INTO orders (
    order_number,
    user_id,
    customer_name, 
    contact_number, 
    total_amount, 
    status, 
    notes, 
    created_at, 
    updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Record new customer order linked to authenticated user  
**Laravel Code:** `Order::create([...])`  
**Note:** `user_id` is NOT NULL and links to the authenticated user who placed the order

**Example Values:**
```sql
INSERT INTO orders VALUES (
    'ORD-20251213-001',    -- order_number
    1,                     -- user_id (NOT NULL)
    'Ericka A. Orbasido',  -- customer_name
    '09123456789',         -- contact_number
    375.00,                -- total_amount
    'completed',           -- status
    NULL,                  -- notes
    '2025-12-13 15:30:00', -- created_at
    '2025-12-13 15:30:00'  -- updated_at
);
```

### Create Order Items
```sql
INSERT INTO order_items (
    order_id,
    product_id,
    product_name,
    price,
    quantity,
    subtotal,
    created_at,
    updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?);
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Record individual items in an order (snapshot of product at purchase time)  
**Laravel Code:** `$order->items()->create($item)`

**Example Values:**
```sql
INSERT INTO order_items VALUES (
    1,                     -- order_id
    1,                     -- product_id
    'Butter Croissant',    -- product_name (snapshot)
    75.00,                 -- price (snapshot)
    3,                     -- quantity
    225.00,                -- subtotal (price × quantity)
    '2025-12-13 15:30:00', -- created_at
    '2025-12-13 15:30:00'  -- updated_at
);
```

---

## **UPDATE Queries**

### Reduce Product Stock (ATOMIC)
```sql
UPDATE products 
SET stock = stock - ?, 
    updated_at = ? 
WHERE id = ?;
```
**Location:** `CheckoutController.php` - `process()` method  
**Purpose:** Atomically decrement product stock after purchase  
**ACID Properties:** 
- **ATOMICITY:** Combined with transaction - only executes if checkout succeeds
- **CONSISTENCY:** Ensures stock never goes negative (validated before update)
- **ISOLATION:** Protected by `FOR UPDATE` lock

**Laravel Code:** `$product->decrement('stock', $quantity)`

**Example:**
```sql
-- Reduce Butter Croissant stock by 3
UPDATE products 
SET stock = stock - 3,
    updated_at = '2025-12-13 15:30:00'
WHERE id = 1;
```

---

## **Complete ACID Transaction Example**

### Successful Checkout Flow

```sql
-- 1. START TRANSACTION (ATOMICITY + DURABILITY)
BEGIN;

-- 2. LOCK PRODUCT ROWS (ISOLATION - prevents race conditions)
SELECT * FROM products WHERE id = 1 FOR UPDATE;
SELECT * FROM products WHERE id = 2 FOR UPDATE;
-- Other concurrent transactions will WAIT here until this transaction completes

-- 3. VALIDATE STOCK (CONSISTENCY)
-- Application checks: if (stock < requested_quantity) throw exception;
-- If validation fails, transaction will ROLLBACK

-- 4. REDUCE STOCK (ATOMIC operation within transaction)
UPDATE products 
SET stock = stock - 3, updated_at = '2025-12-13 15:30:00' 
WHERE id = 1;

UPDATE products 
SET stock = stock - 2, updated_at = '2025-12-13 15:30:00' 
WHERE id = 2;

-- 5. CREATE ORDER
INSERT INTO orders (
    order_number, user_id, customer_name, contact_number, 
    total_amount, status, notes, created_at, updated_at
) VALUES (
    'ORD-20251213-001',
    1,  -- user_id (NOT NULL - from auth()->id())
    'Ericka A. Orbasido',
    '09123456789',
    375.00,
    'completed',
    NULL,
    '2025-12-13 15:30:00',
    '2025-12-13 15:30:00'
);
-- Get last insert ID: 1

-- 6. CREATE ORDER ITEMS
INSERT INTO order_items (
    order_id, product_id, product_name, price, quantity, subtotal, created_at, updated_at
) VALUES (
    1, 1, 'Butter Croissant', 75.00, 3, 225.00, '2025-12-13 15:30:00', '2025-12-13 15:30:00'
);

INSERT INTO order_items (
    order_id, product_id, product_name, price, quantity, subtotal, created_at, updated_at
) VALUES (
    1, 2, 'Chocolate Danish', 75.00, 2, 150.00, '2025-12-13 15:30:00', '2025-12-13 15:30:00'
);

-- 7. COMMIT (DURABILITY - all changes are permanently saved)
COMMIT;

-- Result: 
-- ✓ Stock reduced
-- ✓ Order created
-- ✓ Order items recorded
-- ✓ Data persists even if server crashes after this point
```

### Failed Checkout Flow (Insufficient Stock)

```sql
-- 1. START TRANSACTION
BEGIN;

-- 2. LOCK PRODUCT
SELECT * FROM products WHERE id = 1 FOR UPDATE;
-- Returns: id=1, stock=2

-- 3. APPLICATION VALIDATES STOCK
-- Customer wants 5, but only 2 available
-- Application throws Exception: "Insufficient stock"

-- 4. ROLLBACK (ATOMICITY - undo all changes)
ROLLBACK;

-- Result:
-- ✓ No stock was reduced
-- ✓ No order was created
-- ✓ Database remains in consistent state
-- ✓ Other waiting transactions can now proceed
```

---

## **ACID Properties Demonstrated**

### 1. ATOMICITY (All or Nothing)
```sql
BEGIN;
    UPDATE products SET stock = stock - 3 WHERE id = 1;
    INSERT INTO orders (...) VALUES (...);
    INSERT INTO order_items (...) VALUES (...);
COMMIT; -- All succeed together

-- OR if any fails:
ROLLBACK; -- All fail together (nothing is saved)
```

### 2. CONSISTENCY (Valid State Transitions)
```sql
-- Business Rules Enforced:
-- ✓ Stock cannot be negative
-- ✓ Order total must equal sum of item subtotals
-- ✓ Contact number must be 11 digits starting with 09

-- Example validation:
SELECT stock FROM products WHERE id = 1; -- Returns: 5
-- Customer requests: 10
-- Application: REJECT (stock < requested)
-- No SQL executed - database remains consistent
```

### 3. ISOLATION (Concurrent Access Control)
```sql
-- Transaction A:
BEGIN;
SELECT * FROM products WHERE id = 1 FOR UPDATE; -- Locks row
-- stock = 1

-- Transaction B (concurrent):
BEGIN;
SELECT * FROM products WHERE id = 1 FOR UPDATE; -- WAITS for Transaction A

-- Transaction A:
UPDATE products SET stock = 0 WHERE id = 1;
COMMIT; -- Releases lock

-- Transaction B (now proceeds):
-- stock = 0
-- Customer requests: 1
-- Application: REJECT (out of stock)
ROLLBACK;

-- Result: No overselling!
```

### 4. DURABILITY (Permanent Storage)
```sql
COMMIT;
-- After COMMIT:
-- ✓ Data is written to disk
-- ✓ Survives server crashes
-- ✓ Survives power failures
-- ✓ Transaction logs ensure recovery
```

---

## **Race Condition Prevention Example**

### Scenario: Last Item in Stock

**Initial State:**
```sql
SELECT * FROM products WHERE id = 1;
-- Result: Butter Croissant, stock = 1
```

**Two Customers Checkout Simultaneously:**

#### Without `FOR UPDATE` (RACE CONDITION - BAD):
```sql
-- Customer A (Transaction 1):
SELECT * FROM products WHERE id = 1; -- stock = 1 ✓

-- Customer B (Transaction 2):
SELECT * FROM products WHERE id = 1; -- stock = 1 ✓ (PROBLEM!)

-- Transaction 1:
UPDATE products SET stock = 0 WHERE id = 1; -- ✓
COMMIT;

-- Transaction 2:
UPDATE products SET stock = 0 WHERE id = 1; -- ✓ (SHOULD HAVE FAILED!)
COMMIT;

-- Result: OVERSOLD! Both got the item, but only 1 was available
```

#### With `FOR UPDATE` (ISOLATION - CORRECT):
```sql
-- Customer A (Transaction 1):
SELECT * FROM products WHERE id = 1 FOR UPDATE; -- LOCKS row, stock = 1 ✓

-- Customer B (Transaction 2):
SELECT * FROM products WHERE id = 1 FOR UPDATE; -- WAITS...

-- Transaction 1:
UPDATE products SET stock = 0 WHERE id = 1; -- ✓
COMMIT; -- RELEASES LOCK

-- Transaction 2 (now proceeds):
-- stock = 0
-- Application: REJECT (out of stock)
ROLLBACK;

-- Result: CORRECT! Only Customer A got the item
```

---

## **Authentication & User Queries**

### User Login (Remember Me)
```sql
-- Authenticate user
SELECT * FROM users 
WHERE email = ? 
LIMIT 1;

-- Update remember token (if "Remember Me" is checked)
UPDATE users 
SET remember_token = ?, 
    updated_at = ? 
WHERE id = ?;
```
**Location:** `AuthController.php` - `login()` method  
**Purpose:** Authenticate user and optionally set remember token for persistent login  
**Laravel Code:** `Auth::attempt($credentials, $request->boolean('remember'))`

### User Registration
```sql
INSERT INTO users (
    name,
    email,
    password,
    created_at,
    updated_at
) VALUES (?, ?, ?, ?, ?);
```
**Location:** `AuthController.php` - `register()` method  
**Purpose:** Create new user account  
**Note:** Passwords are hashed using bcrypt before storage

---

## **Database Schema Changes**

### Tables Removed (Unnecessary)
The following tables have been dropped as they are not used in this application:
- `cache` - Not using cache driver
- `cache_locks` - Not using cache driver
- `failed_jobs` - Not using queue system
- `jobs` - Not using queue system
- `job_batches` - Not using queue system
- `sessions` - Using database sessions (table removed, using cookies instead)

### Columns Removed
- `users.email_verified_at` - Email verification not implemented

### Columns Modified
- `orders.user_id` - Changed from NULLABLE to NOT NULL
  - Foreign key changed from `onDelete('set null')` to `onDelete('cascade')`
  - All orders must be associated with a user
  - If user is deleted, their orders are also deleted

---

## **Query Performance Notes**

### Indexes Used
```sql
-- Primary keys (automatic indexes):
PRIMARY KEY (id) ON products
PRIMARY KEY (id) ON orders
PRIMARY KEY (id) ON order_items
PRIMARY KEY (id) ON users

-- Foreign keys (indexes for joins):
INDEX (order_id) ON order_items
INDEX (product_id) ON order_items
INDEX (user_id) ON orders
```

### Optimized Queries
- **Eager Loading:** `Order::with('items')` prevents N+1 query problem
- **Pagination:** `LIMIT 10 OFFSET 0` reduces memory usage
- **Locking:** `FOR UPDATE` only locks necessary rows

---

## **Summary**

| SQL Statement | ACID Property | Purpose |
|--------------|---------------|---------|
| `BEGIN;` | Atomicity | Start transaction |
| `COMMIT;` | Durability | Save changes permanently |
| `ROLLBACK;` | Atomicity | Undo all changes |
| `SELECT ... FOR UPDATE` | Isolation | Prevent race conditions |
| `UPDATE products SET stock = stock - ?` | Consistency | Atomic stock reduction |
| Stock validation | Consistency | Prevent negative stock |
| Transaction wrapper | All 4 | Ensure data integrity |

