# E-commerce API Documentation

## 🚀 Overview
A comprehensive e-commerce API built with Laravel 12, featuring modern authentication, real-time notifications, payment processing, and a complete product management system. This API provides a robust foundation for building scalable e-commerce applications with advanced features like Google OAuth, SATIM payment integration, and real-time broadcasting.

## 📊 API Statistics
- **Framework**: Laravel 12.x
- **PHP Version**: ^8.2
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum
- **Real-time**: Laravel Reverb
- **Payment Gateway**: SATIM (Algerian Payment System)
- **Total Endpoints**: 80+ endpoints
- **Rate Limits**: 5 requests/minute (auth), 3 requests/minute (OTP)

## 🛠️ Technology Stack

### Backend
- **Laravel 12.x** - Modern PHP framework
- **Laravel Sanctum** - API authentication
- **Laravel Reverb** - Real-time broadcasting
- **Laravel Scout** - Search functionality
- **PostgreSQL** - Primary database
- **Redis** - Caching and sessions
- **SATIM PHP SDK** - Payment processing

### Frontend & Development
- **Vite** - Build tool
- **Laravel Sail** - Docker development environment
- **Laravel Pint** - Code styling
- **PHPUnit** - Testing framework

### Third-party Integrations
- **Google OAuth** - Social authentication
- **SATIM Payment Gateway** - Payment processing
- **Laravel Socialite** - Social login

## 🔑 Authentication

### Authentication Methods
1. **Email/Password Registration & Login**
2. **Google OAuth Integration**
3. **OTP-based Password Reset**
4. **Token-based API Authentication**

### Authentication Endpoints
```http
POST /Auth/signup          # User registration
POST /Auth/login           # User login
POST /Auth/GoogleAuth      # Google OAuth login
POST /Auth/send-otp        # Send OTP for password reset
POST /Auth/reset-password  # Reset password with OTP
POST /Auth/logout          # User logout
PATCH /Auth/update-password # Update password
GET /Auth/me               # Get current user
PUT /Auth/me               # Update user profile
```

### Authentication Headers
```http
Authorization: Bearer {your_token}
Content-Type: application/json
```

## ✨ Key Features

### 1. User Management
- Complete user authentication system with multiple methods
- Profile management with image upload
- Role-based access control (Admin/Client)
- OTP-based password reset
- Google OAuth integration
- Account deletion

### 2. Product Management
- Comprehensive product catalog with variants
- Product categories and tags system
- Product reviews and comments
- Product search and filtering
- Best-selling products tracking
- Product file attachments
- Saved products functionality
- Stock management with variants

### 3. Order Management
- Complete order lifecycle management
- Order status tracking with real-time updates
- Order cancellation
- Order items management
- Real-time order notifications
- Order analytics and reporting

### 4. Payment System
- SATIM payment gateway integration
- Payment status tracking
- Refund processing
- Payment analytics
- Receipt generation
- Multiple payment methods support

### 5. Stock Management
- Real-time stock tracking
- Low stock alerts
- Stock analytics
- Out-of-stock management
- Variant-based stock control

### 6. Admin Dashboard
- Sales analytics
- Order analytics
- Real-time notifications
- Dashboard summary
- User management
- Payment monitoring

### 7. Address Management
- Multiple address support
- Default address selection
- Address validation
- User-specific address management

### 8. Contact System
- Contact form submission
- Contact status tracking
- Admin response system
- Email notifications

### 9. Coupon System
- Coupon creation and management
- Coupon validation
- Usage tracking
- Expiration management
- Order-specific coupon application

### 10. Real-time Features
- WebSocket-based real-time updates
- Order status notifications
- Payment status updates
- Admin dashboard notifications
- Live stock updates

## 📝 API Endpoints

### Authentication Routes (`/Auth`)
```http
POST /signup              # User registration
POST /login               # User login
POST /GoogleAuth          # Google OAuth
POST /send-otp            # Send OTP
POST /reset-password      # Reset password
POST /logout              # Logout
PATCH /update-password    # Update password
GET /me                   # Get user profile
PUT /me                   # Update profile
GET /users/{id}           # Get user by ID
DELETE /users/{id}        # Delete user
POST /admin/send-message  # Admin send message
```

### Product Routes (`/api/products`)
#### Public Endpoints
```http
GET /                     # List products
GET /best-selling         # Best selling products
GET /search               # Search products
GET /suggest/{id}         # Product suggestions
GET /categories           # Get categories
GET /tags                 # Get tags
GET /category/{categoryId} # Products by category
GET /tag/{tagId}          # Products by tag
GET /{product}            # Get product details
GET /{product}/comments   # Get product comments
GET /{product}/reviews    # Get product reviews
GET /{product}/files      # Get product files
GET /{product}/variants   # Get product variants
```

#### Protected Endpoints
```http
GET /saved                # Get saved products
POST /                    # Create product
PUT /{product}            # Update product
DELETE /{product}         # Delete product
POST /{product}/comments  # Add comment
DELETE /{product}/comments/{comment} # Delete comment
POST /{product}/reviews   # Add review
POST /{product}/files     # Add file
DELETE /{product}/files/{file} # Delete file
POST /{product}/variants  # Add variant
PUT /{product}/variants/{variant} # Update variant
DELETE /{product}/variants/{variant} # Delete variant
PATCH /{product}/variants/{variant}/stock # Update stock
POST /tags                # Create tag
DELETE /tags/{tag}        # Delete tag
POST /categories          # Create category
DELETE /categories/{category} # Delete category
POST /{product}/tags      # Add tag to product
POST /{product}/categories # Add category to product
POST /{product}/save      # Toggle save product
GET /{productId}/is-saved # Check if saved
```

### Order Routes (`/api/orders`)
```http
GET /                     # List orders
POST /                    # Create order
GET /{order}              # Get order details
PUT /{order}              # Update order
POST /{order}/cancel      # Cancel order
PATCH /{order}/status     # Update order status
DELETE /{order}           # Delete order
```

### Payment Routes (`/api/payments`)
```http
GET /                     # List payments
POST /                    # Create payment
GET /{payment}            # Get payment details
PATCH /{payment}/status   # Update payment status
```

### Order Items Routes (`/api/order-items`)
```http
GET /orders/{orderId}/items # Get order items
GET /{orderitem}          # Get order item details
```

### Coupon Routes (`/api/coupons`)
```http
GET /                     # List coupons
GET /{coupon}             # Get coupon details
POST /                    # Create coupon
PUT /{coupon}             # Update coupon
DELETE /{coupon}          # Delete coupon
POST /validate            # Validate coupon
POST /orders/{order}/use-coupon # Apply coupon to order
```

### Address Routes (`/api/addresses`)
```http
GET /                     # Get user addresses
GET /{address}            # Get address details
POST /                    # Create address
PUT /{address}            # Update address
DELETE /                  # Delete address
```

### Contact Routes (`/api/contacts`)
```http
GET /                     # List contacts
POST /                    # Create contact
GET /{contact}            # Get contact details
PUT /{contact}            # Update contact
DELETE /{contact}         # Delete contact
PATCH /{contact}/primary  # Set as primary contact
```

### Admin Dashboard Routes (`/api/admin`)
```http
GET /summary              # Dashboard summary
GET /sales-analytics      # Sales analytics
GET /order-analytics      # Order analytics
GET /order-notifications  # Real-time order notifications
```

### Stock Management Routes (`/api/stock`)
```http
GET /highest              # Products with highest stock
GET /lowest               # Products with lowest stock
GET /out-of-stock         # Out of stock products
```

## 🔄 Response Format

### Success Response
```json
{
    "status": true,
    "message": "Operation successful",
    "data": {
        // Response data
    },
    "meta": {
        // Pagination or additional metadata
    }
}
```

### Error Response
```json
{
    "status": false,
    "message": "Error message",
    "error": "Detailed error information"
}
```

### Authentication Error
```json
{
    "success": false,
    "message": "Unauthenticated",
    "errors": "Invalid or expired authentication token"
}
```

## 📋 Usage Examples

### 1. User Registration
```http
POST /Auth/signup
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "profile_image": "file_upload"
}
```

### 2. Google OAuth Login
```http
POST /Auth/GoogleAuth
Content-Type: application/json

{
    "id_token": "google_id_token"
}
```

### 3. Product Creation
```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Product Name",
    "description": "Product Description",
    "price": 99.99,
    "category_id": 1,
    "tags": [1, 2, 3]
}
```

### 4. Order Creation
```http
POST /api/orders
Authorization: Bearer {token}
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
    "billing_address_id": 1
}
```

### 5. Payment Processing
```http
POST /api/payments
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": 1,
    "method": "satim"
}
```

## 🔍 Filtering and Pagination

### Query Parameters
- `page`: Page number
- `per_page`: Items per page (default: 15)
- `search`: Search term
- `sort_by`: Sort field
- `sort_direction`: Sort order (asc/desc)
- `status`: Filter by status
- `date_from`: Filter by start date
- `date_to`: Filter by end date
- `category_id`: Filter by category
- `tag_id`: Filter by tag

### Example
```http
GET /api/products?page=1&per_page=20&search=laptop&sort_by=price&sort_direction=desc
```

## 🛡️ Security Features

### Authentication & Authorization
- Laravel Sanctum token-based authentication
- Role-based access control (Admin/Client)
- Policy-based authorization
- Rate limiting on sensitive endpoints
- CSRF protection (disabled for API)

### Data Protection
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- File upload validation
- Secure password hashing

### API Security
- HTTPS encryption
- Token expiration
- Rate limiting
- Request throttling
- Error handling without sensitive data exposure

## 💾 Database & Caching

### Database Structure
- **Users**: Authentication and profiles
- **Products**: Product catalog with variants
- **Orders**: Order management
- **Payments**: Payment processing
- **Categories & Tags**: Product organization
- **Addresses**: User addresses
- **Contacts**: Contact management
- **Coupons**: Discount system
- **Reviews & Comments**: User feedback

### Caching Strategy
- Redis for session storage
- Query result caching
- API response caching
- Real-time data caching

## 📤 File Upload

### Supported File Types
- **Images**: jpg, png, gif, webp
- **Documents**: pdf, doc, docx
- **Maximum file size**: 4MB for profile images

### File Storage
- Public disk for accessible files
- Organized directory structure
- Automatic file cleanup

## 🧪 Testing

### Test Coverage
- Unit tests for models and services
- Feature tests for API endpoints
- Integration tests for payment processing
- Authentication tests

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=ProductTest

# Run with coverage
php artisan test --coverage
```

## 🚀 Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- Docker & Docker Compose
- Node.js & npm

### Installation
```bash
# Clone the repository
git clone <repository-url>
cd ecomerce-api

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Install Node.js dependencies
npm install

# Start development environment
./vendor/bin/sail up -d

# Or use the dev script
composer run dev
```

### Environment Variables
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# SATIM Payment
SATIM_USERNAME=your_satim_username
SATIM_PASSWORD=your_satim_password
SATIM_TERMINAL_ID=your_terminal_id
SATIM_TEST_MODE=true
SATIM_RETURN_URL=your_return_url
SATIM_FAIL_URL=your_fail_url

# Broadcasting
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=your_reverb_key
REVERB_APP_SECRET=your_reverb_secret
REVERB_APP_ID=your_reverb_app_id
REVERB_HOST=your_reverb_host
REVERB_PORT=443
REVERB_SCHEME=https
```

## 📞 Support & Documentation

### API Documentation
- Base URL: `http://localhost/api`
- Authentication: Bearer token
- Content-Type: application/json

### Development Tools
- **Laravel Sail**: Docker development environment
- **Laravel Pail**: Log viewing
- **Laravel Pint**: Code formatting
- **PHPUnit**: Testing framework

### Real-time Testing
- WebSocket test page: `http://localhost/socket-test`
- Email preview routes available

## 🔄 Real-time Features

### WebSocket Events
- **OrderStatusUpdated**: Real-time order status changes
- **PaymentStatusUpdated**: Payment status updates
- **NewOrderPlaced**: New order notifications

### Broadcasting Channels
- `orders`: Public order updates
- `admin.orders`: Admin order notifications
- `payments.{id}`: Payment-specific updates
- `admin.payments`: Admin payment notifications

### Client Integration
```javascript
// Connect to WebSocket
window.Echo.channel('orders')
    .listen('OrderStatusUpdated', (e) => {
        console.log('Order status updated:', e.order);
    });

// Private channels
window.Echo.private('admin.orders')
    .listen('NewOrderPlaced', (e) => {
        console.log('New order:', e.order);
    });
```

## 📈 Performance & Scalability

### Optimization Features
- Database query optimization
- Eager loading for relationships
- API response caching
- File upload optimization
- Queue-based email processing

### Monitoring
- Comprehensive logging
- Error tracking
- Performance monitoring
- Database query analysis

## 🔒 Production Deployment

### Requirements
- PHP 8.2+
- PostgreSQL 17+
- Redis 7+
- Nginx/Apache
- SSL certificate

### Deployment Steps
1. Set up production environment
2. Configure environment variables
3. Run database migrations
4. Set up queue workers
5. Configure WebSocket server
6. Set up monitoring and logging

## 📚 Additional Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Laravel Sanctum**: https://laravel.com/docs/sanctum
- **Laravel Reverb**: https://laravel.com/docs/reverb
- **SATIM Documentation**: https://satim.dz

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.
