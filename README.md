# E-commerce API Documentation

## 🚀 Overview
This is a comprehensive e-commerce API built with Laravel that provides a robust foundation for building modern e-commerce applications. The API offers a complete set of features for managing products, orders, payments, users, and more.

## 📊 API Statistics
- Total Endpoints: 92 endpoints
- Authentication: Laravel Sanctum
- Base URL: `https://laravel-cloud.com/api`
- Rate Limits: 60 requests/minute (authenticated), 30 requests/minute (unauthenticated)

## 🔑 Authentication
All protected endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:
```http
Authorization: Bearer {your_token}
```

## ✨ Key Features

### 1. User Management
- Complete user authentication system
- Profile management
- Address management
- Order history tracking
- Password reset functionality

### 2. Product Management
- Comprehensive product catalog
- Product variants and stock management
- Categories and tags system
- Product reviews and comments
- Product search and filtering
- Best-selling products tracking
- Product file attachments

### 3. Order Management
- Complete order lifecycle management
- Order status tracking
- Order cancellation
- Order items management
- Real-time order notifications

### 4. Payment System
- Multiple payment method support
- Payment status tracking
- Refund processing
- Webhook integration
- Payment analytics

### 5. Stock Management
- Real-time stock tracking
- Low stock alerts
- Stock analytics
- Out-of-stock management

### 6. Admin Dashboard
- Sales analytics
- Order analytics
- Real-time notifications
- Dashboard summary

### 7. Address Management
- Multiple address support
- Default address selection
- Separate shipping and billing addresses
- Address validation

### 8. Contact System
- Contact form submission
- FAQ management
- Contact status tracking
- Admin response system

### 9. Coupon System
- Coupon creation and management
- Coupon validation
- Usage tracking
- Expiration management

## 📝 API Endpoints

### User Management
#### Public Endpoints
```http
POST /auth/register
POST /auth/login
POST /auth/logout
POST /auth/forgot-password
POST /auth/reset-password
```

#### Protected Endpoints
```http
GET /user
PUT /user
GET /user/orders
GET /user/addresses
POST /user/addresses
PUT /user/addresses/{id}
DELETE /user/addresses/{id}
```

### Product Management
#### Public Endpoints
```http
GET /products
GET /products/{id}
GET /products/search
GET /products/best-selling
GET /products/suggest/{id}
GET /products/categories
GET /products/tags
GET /products/category/{categoryId}
GET /products/tag/{tagId}
GET /products/{productId}/comments
GET /products/{productId}/reviews
GET /products/{productId}/files
GET /products/{productId}/variants
GET /products/saved
GET /products/{productId}/is-saved
```

#### Protected Endpoints
```http
POST /products
PUT /products/{id}
DELETE /products/{id}
POST /products/{productId}/comments
DELETE /products/{productId}/comments/{commentId}
POST /products/{productId}/reviews
POST /products/{productId}/files
DELETE /products/{productId}/files/{fileId}
POST /products/{productId}/variants
PUT /products/{productId}/variants/{variantId}
DELETE /products/{productId}/variants/{variantId}
PATCH /products/{productId}/variants/{variantId}/stock
POST /products/tags
DELETE /products/tags/{tagId}
POST /products/categories
DELETE /products/categories/{categoryId}
POST /products/{productId}/tags
POST /products/{productId}/categories
POST /products/{productId}/save
```

### Order Management
#### Protected Endpoints
- `GET /orders` - List user orders
- `POST /orders` - Create new order
- `GET /orders/{id}` - Get order details
- `PUT /orders/{id}` - Update order
- `POST /orders/{id}/cancel` - Cancel order
- `PATCH /orders/{id}/status` - Update order status
- `GET /orders/{orderId}/items` - Get order items
- `GET /order-items/{id}` - Get order item details

### Payment Management
#### Protected Endpoints
- `GET /payments` - List payments
- `POST /payments` - Create payment
- `GET /payments/{id}` - Get payment details
- `GET /payments/{id}/status` - Get payment status
- `PATCH /payments/{id}/status` - Update payment status
- `POST /payments/{id}/refund` - Process refund
- `POST /payments/webhook` - Handle payment webhooks

### Stock Management
#### Protected Endpoints
- `GET /stock/highest` - Get products with highest stock
- `GET /stock/lowest` - Get products with lowest stock
- `GET /stock/out-of-stock` - Get out-of-stock products

### Admin Dashboard
#### Protected Endpoints (Admin Only)
- `GET /admin/summary` - Get dashboard summary
- `GET /admin/sales-analytics` - Get sales analytics
- `GET /admin/order-analytics` - Get order analytics
- `GET /admin/order-notifications` - Get real-time order notifications

### Address Management
#### Protected Endpoints
- `GET /addresses` - List all addresses
- `GET /addresses/{id}` - Get address details
- `POST /addresses` - Create new address
- `PUT /addresses/{id}` - Update address
- `DELETE /addresses/{id}` - Delete address
- `GET /addresses/default` - Get default address
- `POST /addresses/{id}/set-default` - Set as default address
- `GET /addresses/shipping` - Get shipping addresses
- `GET /addresses/billing` - Get billing addresses

### Contact Management
#### Public Endpoints
- `POST /contacts` - Submit contact form
- `GET /contacts/faq` - Get frequently asked questions

#### Protected Endpoints
- `GET /contacts` - List all contacts
- `GET /contacts/{id}` - Get contact details
- `PUT /contacts/{id}` - Update contact
- `DELETE /contacts/{id}` - Delete contact
- `PATCH /contacts/{id}/status` - Update contact status
- `GET /contacts/status/{status}` - Get contacts by status

### Coupon Management
#### Public Endpoints
- `GET /coupons/active` - Get active coupons
- `GET /coupons/{code}` - Get coupon by code

#### Protected Endpoints
- `GET /coupons` - List all coupons
- `GET /coupons/{id}` - Get coupon details
- `POST /coupons` - Create new coupon
- `PUT /coupons/{id}` - Update coupon
- `DELETE /coupons/{id}` - Delete coupon
- `POST /coupons/validate` - Validate coupon
- `POST /orders/{id}/use-coupon` - Apply coupon to order
- `POST /coupons/{id}/activate` - Activate coupon
- `POST /coupons/{id}/deactivate` - Deactivate coupon
- `GET /coupons/expired` - Get expired coupons
- `GET /coupons/upcoming` - Get upcoming coupons
- `GET /coupons/usage/{id}` - Get coupon usage history

## 🔄 Response Format
All API responses follow a consistent format:

```json
{
    "status": true/false,
    "message": "Response message",
    "data": {
        // Response data
    },
    "meta": {
        // Pagination or additional metadata
    }
}
```

## ⚠️ Error Handling
Error responses follow this format:
```json
{
    "status": false,
    "message": "Error message",
    "error": "Detailed error information"
}
```

## 📋 Usage Examples

### 1. User Registration
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### 2. Product Creation
```http
POST /products
Authorization: Bearer {your_token}
Content-Type: application/json

{
    "name": "Product Name",
    "description": "Product Description",
    "price": 99.99,
    "stock": 100,
    "category_id": 1,
    "tags": [1, 2, 3]
}
```

### 3. Order Creation
```http
POST /orders
Authorization: Bearer {your_token}
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "variant_id": 1
        }
    ],
    "shipping_address_id": 1,
    "billing_address_id": 1,
    "payment_method": "credit_card"
}
```

## 🔍 Filtering and Pagination
Most list endpoints support these query parameters:
- `page`: Page number
- `per_page`: Items per page
- `limit`: Maximum items to return
- `search`: Search term
- `sort`: Sort field
- `order`: Sort order (asc/desc)
- `status`: Filter by status
- `date_from`: Filter by start date
- `date_to`: Filter by end date

## 🛡️ Security Features
- HTTPS encryption
- CSRF protection
- Rate limiting
- Input validation
- Token-based authentication
- Role-based access control

## 💾 Caching
The API implements caching for:
- Product listings
- Category and tag lists
- Best-selling products
- Dashboard analytics
- Stock information

## 📤 File Upload
Supported file types:
- Images (jpg, png, gif)
- Documents (pdf, doc, docx)
- Maximum file size: 10MB

## 🧪 Testing
The API includes comprehensive test coverage:
- Unit tests
- Integration tests
- API tests
- Performance tests

## 📞 Support
For API support and assistance:
- Email: support@your-domain.com
- Documentation: https://your-domain.com/docs
- Status: https://status.your-domain.com

## 🔄 Real-time Features
The API provides real-time updates for:
- Order status changes
- Payment status updates
- Stock level changes
- New order notifications

## 📈 Webhooks
The system supports webhooks for:
- Payment status updates
- Order status changes
- Stock level changes

## 🚀 Getting Started
1. Register for an API key
2. Set up authentication
3. Review the documentation
4. Start integrating the API

## 📚 Additional Resources
- API Documentation: https://your-domain.com/docs
- SDK Downloads: https://your-domain.com/sdk
- Example Applications: https://your-domain.com/examples
- API Status: https://status.your-domain.com
