# GearSphere Backend API

A comprehensive PHP-based backend system for the GearSphere PC building and marketplace platform, featuring role-based user management, intelligent PC recommendations, and secure e-commerce functionality.

## 🚀 Features

### 🔐 Authentication & Security

- **Multi-role Authentication**: Customer, Admin, Technician, Seller
- **OTP Verification**: Email-based verification with PHPMailer
- **Session Management**: Secure PHP sessions with role-based access
- **Password Security**: Bcrypt hashing with salt
- **CORS Protection**: Configurable cross-origin request handling

### 👥 User Management

- **Role-based Access Control**: Different permissions for each user type
- **Profile Management**: Update profiles with image upload
- **User Registration**: Separate registration flows for different roles
- **Account Validation**: Email verification and approval workflows

### 🛍️ E-commerce System

- **Product Management**: CRUD operations for PC components
- **Shopping Cart**: Session-based cart with quantity management
- **Order Processing**: Complete order lifecycle management
- **Inventory Tracking**: Real-time stock management

### 🔧 PC Builder System

- **Budget-based Recommendations**: Intelligent component selection
- **Usage Optimization**: Gaming, Workstation, Multimedia builds
- **Component Compatibility**: Category-specific product matching
- **Price Calculation**: Dynamic pricing with budget validation

### 🛠️ Technician Services

- **Service Assignment**: Connect customers with technicians
- **Build Requests**: Manage PC assembly services
- **Technician Approval**: Admin-controlled technician verification
- **Service Tracking**: Monitor technician assignments and progress

## 📁 Project Structure

```
GearSphere-BackEnd/
├── Main Classes/           # OOP Core Classes
│   ├── User.php           # Abstract base user class
│   ├── Customer.php       # Customer-specific operations
│   ├── Admin.php          # Admin management functions
│   ├── technician.php     # Technician services
│   ├── Seller.php         # Seller marketplace operations
│   ├── Product.php        # Product management
│   ├── Orders.php         # Order processing
│   ├── Cart.php           # Shopping cart operations
│   ├── Mailer.php         # Email functionality
│   └── ...
├── API Endpoints/         # REST API Files
│   ├── login.php          # User authentication
│   ├── register.php       # User registration
│   ├── getProducts.php    # Product listing
│   ├── suggestBuild.php   # PC builder recommendations
│   └── ...
├── Configuration/
│   ├── DbConnector.php    # Database connection
│   ├── corsConfig.php     # CORS configuration
│   └── sessionConfig.php  # Session management
├── Media/
│   ├── uploads/           # Product images
│   ├── profile_images/    # User profile pictures
│   └── verifypdfs/        # Technician verification docs
└── Database/
    └── gearsphere.sql     # Database schema
```

## 🏗️ Architecture

### Object-Oriented Design

- **Inheritance**: Role-based user classes extending abstract User class
- **Encapsulation**: Protected properties and controlled access methods
- **Polymorphism**: Method overriding for role-specific behaviors
- **Abstraction**: Modular design with separated concerns

### Database Schema

- **Normalized Design**: Relational structure with proper foreign keys
- **Category Tables**: Specific tables for CPU, GPU, Memory, Storage, etc.
- **User Management**: Centralized users table with role differentiation
- **Order System**: Complete order tracking with item relationships

## 🛠️ Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for PHPMailer dependencies)

### Database Setup

1. Import the database schema:

```sql
mysql -u username -p database_name < gearsphere.sql
```

2. Update database credentials in `DbConnector.php`:

```php
private $host = 'localhost';
private $username = 'your_username';
private $password = 'your_password';
private $database = 'gearsphere';
```

### Configuration

1. **CORS Configuration** (`corsConfig.php`):

```php
$allowed_origins = [
    'http://localhost:3000',  // React development server
    'https://yourdomain.com'  // Production frontend
];
```

2. **Email Configuration** (`Mailer.php`):

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```

### File Permissions

```bash
chmod 755 uploads/
chmod 755 profile_images/
chmod 755 verifypdfs/
```

## 🔌 API Endpoints

### Authentication

- `POST /login.php` - User login
- `POST /logout.php` - User logout
- `GET /getSession.php` - Validate session
- `POST /generateOTP.php` - Generate verification OTP
- `POST /verifyOtp.php` - Verify OTP code

### User Management

- `POST /customersignup.php` - Customer registration
- `POST /techniciansignup.php` - Technician registration
- `GET /getCustomer.php` - Get customer details
- `PUT /updateCustomerProfile.php` - Update customer profile

### Products & PC Builder

- `GET /getProducts.php` - List all products
- `GET /getCPUs.php` - Get CPU components
- `GET /getGPUs.php` - Get GPU components
- `GET /suggestBuild.php` - Get PC build recommendations
- `GET /getProductDetails.php` - Get detailed product info

### Shopping & Orders

- `POST /addToCart.php` - Add item to cart
- `GET /getCart.php` - Get cart contents
- `POST /createOrder.php` - Process order
- `GET /getOrders.php` - Get user orders

### Admin Operations

- `GET /getDashboardStats.php` - Admin dashboard data
- `GET /getAllCustomers.php` - Customer management
- `PUT /updateTechnicianApproval.php` - Approve technicians

## 🔐 Security Features

### Input Validation

- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization and validation
- **Email Validation**: PHP filter_var validation
- **File Upload Security**: Type and size validation

### Session Security

- **Session Regeneration**: On login and privilege changes
- **Session Timeout**: Configurable inactivity timeout
- **Secure Cookies**: HTTPOnly and Secure flags
- **CSRF Protection**: Origin validation

### Authentication Security

- **Password Hashing**: PHP password_hash() with bcrypt
- **OTP Security**: Time-limited verification codes
- **Role-based Access**: Endpoint-level permission checking
- **Account Lockout**: Failed login attempt protection

## 🧪 Testing

### Manual Testing

1. Test user registration and login flows
2. Verify OTP email delivery and validation
3. Test PC builder recommendations with different budgets
4. Validate shopping cart and order processing
5. Check admin dashboard and user management

### API Testing with Postman

```javascript
// Example login request
POST http://localhost/gearsphere_api/GearSphere-BackEnd/login.php
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "password123"
}
```

## 📊 Database Schema Highlights

### Core Tables

- **users**: Central user management with role-based access
- **products**: Main product catalog with category references
- **orders**: Order tracking with customer relationships
- **cart**: Session-based shopping cart management

### Component Tables

- **cpu**: Processor specifications and pricing
- **video_card**: Graphics card details and performance
- **memory**: RAM configurations and compatibility
- **storage**: Hard drives and SSD specifications
- **motherboard**: Motherboard features and socket types

## 🚀 Deployment

### Production Checklist

- [ ] Update database credentials
- [ ] Configure production CORS origins
- [ ] Set up SSL certificates
- [ ] Configure email SMTP settings
- [ ] Set proper file permissions
- [ ] Enable error logging
- [ ] Configure session security settings

### Environment Variables

```php
// Production settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

## 📞 Support & Contact

For technical support or questions about the GearSphere backend:

- **Repository**: [GitHub Repository Link]
- **Documentation**: This README file
- **Issues**: Use GitHub Issues for bug reports

## 📄 License

This project is developed for educational purposes as part of a software engineering project.

---

**Built with ❤️ using PHP, MySQL, and modern web technologies**
