# UbugeniPalace - Artisan Marketplace
## Web Design Final Exam Project Documentation

**Student Name:** [Your Name]  
**Registration Number:** [Your Registration Number]  
**Course:** Web Design  
**Project Title:** UbugeniPalace - Digital Marketplace for Rwandan Artisans  
**Date:** [Current Date]

---

## Table of Contents

1. [Project Introduction](#1-project-introduction)
2. [Problem Statement](#2-problem-statement)
3. [System Requirements](#3-system-requirements)
4. [System Design](#4-system-design)
5. [Implementation](#5-implementation)
6. [Database Design](#6-database-design)
7. [Testing](#7-testing)
8. [Challenges Faced](#8-challenges-faced)
9. [Conclusion](#9-conclusion)
10. [Screenshots](#10-screenshots)

---

## 1. Project Introduction

### Title
**UbugeniPalace** - Digital Marketplace for Rwandan Artisans

### Case Study
This system is built for **Rwandan artisans and craft makers** who struggle to reach customers beyond their immediate community. The platform serves as a bridge between traditional Rwandan craftsmanship and modern digital commerce, addressing the gap in the local creative economy.

### Purpose
UbugeniPalace solves the critical problem of **market access for local artisans** by providing:
- A dedicated online marketplace for authentic Rwandan crafts
- Direct connection between artisans and customers worldwide
- Cultural preservation through digital storytelling
- Economic empowerment for traditional craft makers

### Technologies Used
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 8.0+
- **Database:** MySQL 8.0+
- **Server:** Apache (XAMPP/WAMP)
- **Additional:** JSON, AJAX, Responsive Design

---

## 2. Problem Statement

### The Problem
Local artisans in Rwanda face significant barriers to market access:

1. **Limited Physical Presence:** Relying only on weekend craft fairs and seasonal events
2. **Ineffective Social Media:** Posts get buried among unrelated content on general platforms
3. **Restricted Market Reach:** Word-of-mouth marketing limits customer base to local networks
4. **High Commission Costs:** International platforms take significant portions of profits
5. **No Dedicated Platform:** Lack of specialized marketplace for Rwandan cultural products

### Target Users

#### Primary Users - Artisans
- Traditional craft makers (pottery, basket weaving, jewelry)
- Modern artists adapting traditional techniques
- Small craft businesses seeking online presence
- Young artisans wanting to reach digital-native customers

#### Primary Users - Customers
- Rwandans living abroad seeking authentic cultural products
- Tourists and expatriates looking for unique Rwandan crafts
- Local customers who value handmade over mass-produced items
- Gift buyers seeking meaningful, culturally significant presents

### Solution Features
- **Cultural Context Integration:** Share cultural significance and traditional methods
- **Bilingual Support:** English and Kinyarwanda content
- **Story-Driven Profiles:** Showcase personal journey and craft heritage
- **Fair Commission Structure:** Higher profit retention for artisans
- **Direct Customer Communication:** Build personal relationships
- **Quality Assurance:** Reviews and ratings system

---

## 3. System Requirements

### Software Requirements
- **Web Browser:** Chrome, Firefox, Safari, Edge (latest versions)
- **Code Editor:** Visual Studio Code or similar
- **Local Server:** XAMPP/WAMP with Apache and MySQL
- **PHP Version:** 8.0 or higher
- **MySQL Version:** 8.0 or higher

### Hardware Requirements (Optional)
- **Processor:** Intel i3 or equivalent
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 10GB free space
- **Internet:** Broadband connection for development and testing

### Development Environment Setup
1. Install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services
3. Place project in `htdocs` directory
4. Import database schema from `database/ubumenyi_database.sql`
5. Configure database connection in `config/database.php`

---

## 4. System Design

### User Flow Diagram
```
Customer Journey:
Homepage → Browse Products → Product Details → Add to Cart → Checkout → Order Confirmation

Artisan Journey:
Register → Create Profile → Upload Products → Manage Orders → Track Sales

Admin Journey:
Login → Dashboard → Manage Users → Approve Products → Monitor Orders
```

### System Architecture
```
Frontend (HTML/CSS/JS)
    ↓
Backend (PHP)
    ↓
Database (MySQL)
```

### Page Structure
1. **Homepage** (`index.php`) - Landing page with featured products and artisans
2. **Products** (`pages/products.php`) - Product catalog with filtering
3. **Artisans** (`pages/artisans.php`) - Artisan directory and profiles
4. **Product Details** (`pages/product-details.php`) - Individual product pages
5. **User Authentication** (`pages/login.php`, `pages/register.php`) - User accounts
6. **Dashboard** (`pages/dashboard.php`) - User management area
7. **Admin Panel** (`admin/`) - Administrative controls
8. **Cart & Checkout** (`pages/cart.php`, `pages/checkout.php`) - Shopping process

### Navigation Structure
- **Main Navigation:** Home, Products, Artisans, About, Contact
- **User Menu:** Login/Register, Dashboard, Profile, Logout
- **Admin Menu:** Dashboard, Users, Products, Orders, Settings
- **Mobile Menu:** Responsive navigation for mobile devices

---

## 5. Implementation

### HTML Implementation
- **Semantic HTML5:** Proper use of `<header>`, `<main>`, `<section>`, `<article>`, `<footer>`
- **Accessibility:** Alt text for images, proper heading hierarchy
- **SEO Optimization:** Meta tags, structured data, clean URLs
- **Form Structure:** User registration, login, product upload forms

### CSS Implementation
- **Modern CSS Features:** CSS Variables, Flexbox, Grid, Animations
- **Responsive Design:** Mobile-first approach with media queries
- **Custom Properties:** Consistent theming with CSS variables
- **Animations:** Smooth transitions and hover effects
- **File Organization:** 
  - `style.css` - Main styles
  - `responsive.css` - Mobile responsiveness
  - `animations.css` - Interactive animations

### JavaScript Implementation
- **Interactive Features:** Search suggestions, image galleries, form validation
- **AJAX Integration:** Dynamic content loading without page refresh
- **Form Validation:** Client-side validation with server-side backup
- **Mobile Menu:** Touch-friendly navigation
- **File Organization:**
  - `main.js` - Core functionality
  - `cart.js` - Shopping cart management
  - `validation.js` - Form validation
  - `animations.js` - Interactive animations

### PHP Implementation
- **User Authentication:** Secure login/logout with session management
- **Database Operations:** CRUD operations using PDO with prepared statements
- **File Upload:** Secure image upload with validation
- **API Endpoints:** RESTful API for AJAX requests
- **Security Features:** Input sanitization, SQL injection prevention, XSS protection

### Key Features Implemented
1. **User Registration/Login:** Multi-role user system (customer, artisan, admin)
2. **Product Management:** Upload, edit, and manage product listings
3. **Shopping Cart:** Add/remove items, quantity management
4. **Search & Filtering:** Advanced product search with multiple filters
5. **Image Galleries:** Multiple image upload and display
6. **Responsive Design:** Mobile-optimized interface
7. **Admin Panel:** User and product management
8. **Wishlist System:** Save favorite products

---

## 6. Database Design

### Entity Relationship Diagram
```
Users (1) ←→ (1) Artisans
Users (1) ←→ (M) Orders
Artisans (1) ←→ (M) Products
Products (M) ←→ (1) Categories
Orders (1) ←→ (M) Order_Items
Products (1) ←→ (M) Reviews
Users (1) ←→ (M) Cart_Items
Users (1) ←→ (M) Wishlist_Items
```

### Database Tables

#### 1. users
- `id` (INT, Primary Key)
- `username` (VARCHAR(50), Unique)
- `email` (VARCHAR(100), Unique)
- `password` (VARCHAR(255), Hashed)
- `full_name` (VARCHAR(100))
- `phone` (VARCHAR(20))
- `user_type` (ENUM: 'customer', 'artisan', 'admin')
- `profile_image` (VARCHAR(255))
- `address` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `is_active` (BOOLEAN)

#### 2. artisans
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `bio` (TEXT)
- `specialization` (VARCHAR(100))
- `experience_years` (INT)
- `location` (VARCHAR(100))
- `workshop_address` (TEXT)
- `social_media` (JSON)
- `rating` (DECIMAL(3,2))
- `total_reviews` (INT)
- `is_featured` (BOOLEAN)
- `cover_image` (VARCHAR(255))

#### 3. categories
- `id` (INT, Primary Key)
- `name` (VARCHAR(50))
- `name_kinyarwanda` (VARCHAR(50))
- `description` (TEXT)
- `image` (VARCHAR(255))
- `is_active` (BOOLEAN)
- `sort_order` (INT)

#### 4. products
- `id` (INT, Primary Key)
- `artisan_id` (INT, Foreign Key → artisans.id)
- `category_id` (INT, Foreign Key → categories.id)
- `name` (VARCHAR(100))
- `name_kinyarwanda` (VARCHAR(100))
- `description` (TEXT)
- `price` (DECIMAL(10,2))
- `discount_price` (DECIMAL(10,2))
- `stock_quantity` (INT)
- `materials` (VARCHAR(255))
- `dimensions` (VARCHAR(100))
- `weight` (DECIMAL(5,2))
- `colors` (VARCHAR(255))
- `main_image` (VARCHAR(255))
- `gallery_images` (JSON)
- `is_featured` (BOOLEAN)
- `status` (ENUM: 'active', 'inactive', 'sold')
- `views_count` (INT)

#### 5. orders
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `order_number` (VARCHAR(20), Unique)
- `total_amount` (DECIMAL(10,2))
- `shipping_fee` (DECIMAL(10,2))
- `tax_amount` (DECIMAL(10,2))
- `status` (ENUM: 'pending', 'processing', 'shipped', 'delivered', 'cancelled')
- `payment_status` (ENUM: 'pending', 'paid', 'failed')
- `shipping_address` (JSON)
- `created_at` (TIMESTAMP)

#### 6. order_items
- `id` (INT, Primary Key)
- `order_id` (INT, Foreign Key → orders.id)
- `product_id` (INT, Foreign Key → products.id)
- `quantity` (INT)
- `unit_price` (DECIMAL(10,2))
- `total_price` (DECIMAL(10,2))

#### 7. cart
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `product_id` (INT, Foreign Key → products.id)
- `quantity` (INT)
- `custom_notes` (TEXT)
- `added_at` (TIMESTAMP)

#### 8. reviews
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `product_id` (INT, Foreign Key → products.id)
- `rating` (INT, 1-5)
- `comment` (TEXT)
- `created_at` (TIMESTAMP)

#### 9. wishlist
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `product_id` (INT, Foreign Key → products.id)
- `added_at` (TIMESTAMP)

### Database Relationships
- **One-to-One:** Users ↔ Artisans (each artisan is a specialized user)
- **One-to-Many:** Users → Orders, Artisans → Products, Categories → Products
- **Many-to-Many:** Users ↔ Products (through cart, wishlist, reviews)

### Data Integrity
- **Foreign Key Constraints:** Ensure referential integrity
- **Unique Constraints:** Prevent duplicate usernames and emails
- **Cascade Deletes:** Maintain consistency when removing related records
- **Indexes:** Optimize query performance on frequently searched columns

---

## 7. Testing

### Testing Methodology
I conducted comprehensive manual testing across all user roles and system features to ensure functionality and user experience quality.

### User Role Testing

#### Customer Testing
- **Registration Process:** Created multiple customer accounts with different email addresses
- **Product Browsing:** Tested search functionality, category filtering, and price range filters
- **Shopping Cart:** Added/removed items, updated quantities, tested cart persistence
- **Wishlist:** Added products to wishlist, verified wishlist management
- **Product Reviews:** Submitted reviews and ratings for purchased products
- **Order Process:** Completed checkout process and verified order confirmation

#### Artisan Testing
- **Registration Process:** Created artisan accounts with profile information
- **Profile Management:** Updated bio, specialization, location, and social media links
- **Product Upload:** Uploaded products with multiple images, descriptions, and pricing
- **Inventory Management:** Updated stock quantities and product status
- **Order Management:** Reviewed incoming orders and updated order status
- **Sales Analytics:** Monitored product views and sales performance

#### Admin Testing
- **User Management:** Approved/rejected artisan registrations, managed user accounts
- **Product Management:** Reviewed and approved product submissions
- **Order Processing:** Updated order status, processed payments
- **System Monitoring:** Reviewed system statistics and user activity
- **Content Management:** Updated categories and featured products

### Cross-Role Testing
- **Customer-Artisan Interaction:** Verified review system and direct communication
- **Order Flow:** Tested complete order process from customer purchase to artisan fulfillment
- **Data Consistency:** Ensured data integrity across different user actions

### Bug Discovery and Resolution

#### Image Upload Issues
**Problem:** Images were uploading but not displaying due to incorrect file paths
**Solution:** Corrected the relative path structure and implemented proper file validation
**Impact:** Fixed product image display across the platform

#### Database Retrieval Problems
**Problem:** Difficulty retrieving images from the uploads folder
**Solution:** Implemented proper file handling functions and corrected database column references
**Impact:** Improved system reliability and user experience

#### UI Layer Issues
**Problem:** Category dropdown menus appearing behind other page elements
**Solution:** Adjusted CSS z-index values and positioning properties
**Impact:** Enhanced user interface consistency and usability

#### Form Validation Bugs
**Problem:** Client-side validation not properly preventing form submission
**Solution:** Enhanced JavaScript validation and added server-side backup validation
**Impact:** Improved data quality and system security

### Device and Browser Testing
- **Browsers Tested:** Google Chrome, Microsoft Edge
- **Devices Tested:** Desktop computers, tablets, mobile phones
- **Screen Sizes:** Various resolutions to ensure responsive design functionality
- **Performance:** Verified loading times and interactive responsiveness

### Security Testing
- **Authentication:** Tested login/logout functionality and session management
- **Authorization:** Verified role-based access control for different user types
- **Input Validation:** Tested form submissions with invalid data
- **File Upload Security:** Attempted to upload unauthorized file types
- **SQL Injection Prevention:** Tested with malicious input patterns

---

## 8. Challenges Faced

### Technical Challenges

#### Database Design Complexity
**Challenge:** Designing a flexible database structure that could accommodate diverse craft types while maintaining performance
**Solution:** Used JSON fields for flexible data storage (gallery images, social media links) and implemented proper indexing for frequently queried columns
**Learning:** Understanding the balance between normalization and flexibility in database design

#### User Authentication System
**Challenge:** Implementing a multi-level user system with different permissions and workflows
**Solution:** Created role-based access control with separate registration flows for customers and artisans, plus admin approval system for password resets
**Learning:** Security best practices and user experience design for different user types

#### File Upload Management
**Challenge:** Handling multiple image uploads for product galleries with proper validation and storage
**Solution:** Implemented secure file upload system with type validation, size limits, and proper file organization in uploads directory
**Learning:** File system security and user-generated content management

#### Responsive Design Implementation
**Challenge:** Creating a mobile-friendly interface that works well across different screen sizes
**Solution:** Adopted mobile-first design approach with CSS Grid and Flexbox, implemented touch-friendly interactions
**Learning:** Modern CSS techniques and mobile user experience design

#### Form Validation and Security
**Challenge:** Implementing comprehensive validation that prevents malicious input while maintaining good user experience
**Solution:** Combined client-side JavaScript validation with server-side PHP validation, implemented input sanitization and prepared statements
**Learning:** Web security best practices and defensive programming

### Learning Curve Challenges

#### Advanced JavaScript
**Challenge:** Learning modern JavaScript features for interactive animations and dynamic content
**Solution:** Studied ES6+ features, practiced with animations and AJAX requests, used Cursor AI for guidance
**Learning:** Modern JavaScript development and asynchronous programming

#### CSS Animations and Transitions
**Challenge:** Creating smooth, professional animations that enhance user experience
**Solution:** Learned CSS transitions, transforms, and keyframe animations, implemented hover effects and loading states
**Learning:** Advanced CSS techniques and user interface design principles

#### Complex MySQL Queries
**Challenge:** Writing efficient queries for complex data relationships and filtering
**Solution:** Studied SQL optimization, implemented proper JOINs and indexing, used prepared statements for security
**Learning:** Database optimization and query performance

#### Project Management
**Challenge:** Balancing feature development within limited timeframe while maintaining code quality
**Solution:** Prioritized core functionality, used iterative development approach, leveraged AI assistance for faster development
**Learning:** Time management and project planning in software development

### Solutions Implemented

#### AI-Assisted Development
- **Tool Used:** Cursor AI for code generation and debugging
- **Benefits:** Accelerated development while maintaining learning objectives
- **Approach:** Used AI for repetitive tasks while focusing on understanding core concepts

#### Iterative Development
- **Approach:** Built core features first, then added enhancements
- **Benefits:** Ensured working system at each stage
- **Method:** Regular testing and refinement throughout development

#### Documentation and Organization
- **Approach:** Maintained clear code structure and comprehensive documentation
- **Benefits:** Easier debugging and future maintenance
- **Tools:** README files, code comments, and structured file organization

---

## 9. Conclusion

### Learning Outcomes

#### Technical Skills Acquired
- **Full-Stack Development:** Gained comprehensive understanding of front-end and back-end integration
- **Database Design:** Learned to design efficient, scalable database structures with proper relationships
- **Modern Web Technologies:** Mastered HTML5, CSS3, JavaScript ES6+, and PHP 8.0+
- **Responsive Design:** Developed mobile-first approach with modern CSS techniques
- **Security Implementation:** Learned web security best practices including authentication and data protection

#### Problem-Solving Skills
- **Analytical Thinking:** Identified real-world problems and designed technical solutions
- **Debugging:** Developed systematic approach to identifying and fixing technical issues
- **User Experience Design:** Learned to balance functionality with intuitive design
- **Project Management:** Gained experience in planning and executing complex web projects

#### Professional Development
- **Documentation:** Created comprehensive technical documentation and user guides
- **Code Quality:** Learned to write clean, maintainable, and well-documented code
- **Testing Methodology:** Developed systematic approach to quality assurance
- **AI Integration:** Learned to effectively use AI tools to accelerate development

### Project Impact

#### Real-World Application
This project demonstrates the potential for technology to solve genuine community problems. By creating a digital marketplace for Rwandan artisans, the platform addresses:
- **Economic Empowerment:** Providing sustainable income opportunities for traditional craftspeople
- **Cultural Preservation:** Documenting and sharing traditional crafting techniques
- **Market Access:** Connecting local artisans with global customers
- **Community Development:** Supporting the creative economy and rural development

#### Educational Value
The project showcases comprehensive web development skills including:
- **Frontend Development:** Modern HTML, CSS, and JavaScript implementation
- **Backend Development:** PHP server-side programming and database integration
- **Database Management:** MySQL design and optimization
- **Security Implementation:** Authentication, authorization, and data protection
- **User Experience Design:** Responsive design and intuitive interfaces

### Future Improvements

#### Immediate Enhancements
- **Payment Gateway Integration:** Implement MoMo, Visa, and Mastercard payment processing
- **Email Notifications:** Add automated email notifications for orders and updates
- **Advanced Search:** Implement full-text search with filters and sorting options
- **Mobile App Development:** Create native iOS and Android applications
- **Multi-language Support:** Add French and Swahili language options

#### Long-term Vision
- **E-commerce Platform:** Complete shopping cart and checkout functionality
- **Analytics Dashboard:** Detailed sales and user analytics for artisans
- **Social Features:** Community forums and artisan networking
- **International Expansion:** Target East African and global markets
- **AI Integration:** Product recommendations and automated customer service

#### Technical Improvements
- **Performance Optimization:** Implement caching and CDN for faster loading
- **Security Enhancements:** Two-factor authentication and advanced security measures
- **API Development:** Create RESTful API for mobile app integration
- **Cloud Migration:** Move to cloud hosting for scalability
- **Automated Testing:** Implement unit and integration testing

### Personal Growth
This project significantly boosted my confidence in full-stack development and demonstrated that with adequate time and resources, I can develop influential and impactful projects. The experience of solving real-world problems through technology has been both challenging and rewarding, providing a solid foundation for future web development projects.

The combination of traditional web development skills with modern tools like AI assistance has shown me the evolving nature of software development and the importance of continuous learning in this field.

---

## 10. Screenshots

### System Interface Screenshots

#### 1. Homepage
![Homepage](screenshots/homepage.png)
*Main landing page featuring hero section, featured products, and artisan showcase*

#### 2. Product Catalog
![Products Page](screenshots/products.png)
*Product listing with search filters, category selection, and grid/list view options*

#### 3. Product Details
![Product Details](screenshots/product-details.png)
*Individual product page with image gallery, description, pricing, and artisan information*

#### 4. Artisan Directory
![Artisans Page](screenshots/artisans.png)
*Artisan listing with profiles, specializations, and featured artisans*

#### 5. User Registration
![Registration Form](screenshots/registration.png)
*User registration form with role selection (customer/artisan) and profile image upload*

#### 6. User Login
![Login Form](screenshots/login.png)
*Secure login form with remember me functionality and password reset options*

#### 7. User Dashboard
![User Dashboard](screenshots/dashboard.png)
*Personalized dashboard showing orders, wishlist, profile management, and quick actions*

#### 8. Shopping Cart
![Shopping Cart](screenshots/cart.png)
*Shopping cart with product listings, quantity adjustments, and checkout process*

#### 9. Admin Panel
![Admin Dashboard](screenshots/admin-dashboard.png)
*Administrative dashboard with user management, product approval, and system statistics*

#### 10. Database Structure
![Database](screenshots/database.png)
*MySQL database structure showing tables, relationships, and sample data*

### User Actions Screenshots

#### 11. Product Upload Process
![Product Upload](screenshots/product-upload.png)
*Artisan product upload form with multiple image upload and detailed product information*

#### 12. Order Management
![Order Management](screenshots/orders.png)
*Order tracking and management interface for both customers and artisans*

#### 13. Search and Filtering
![Search Results](screenshots/search.png)
*Advanced search functionality with category, price, and artisan filters*

#### 14. Mobile Responsive Design
![Mobile View](screenshots/mobile.png)
*Mobile-optimized interface showing responsive design across different screen sizes*

#### 15. Form Validation
![Form Validation](screenshots/validation.png)
*Client-side and server-side form validation with error messages and success feedback*

---

## Appendix

### A. File Structure
```
UbugeniPalace/
├── admin/                 # Admin panel files
├── api/                   # API endpoints
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images and icons
├── config/              # Configuration files
├── database/            # Database schema
├── includes/            # Shared PHP components
├── pages/               # Main application pages
├── uploads/             # User uploaded content
└── index.php           # Homepage
```

### B. Database Schema
Complete SQL schema available in `database/ubumenyi_database.sql`

### C. Configuration Files
- `config/config.php` - Main configuration settings
- `config/database.php` - Database connection and helper functions

### D. Key Features Summary
- Multi-role user system (Customer, Artisan, Admin)
- Product management with image galleries
- Shopping cart and wishlist functionality
- Advanced search and filtering
- Responsive design for all devices
- Secure authentication and authorization
- Admin panel for system management
- Bilingual support (English/Kinyarwanda)

---

**Project Completion Date:** [Date]  
**Total Development Time:** [Duration]  
**Lines of Code:** [Approximate count]  
**Database Tables:** 9 main tables  
**User Roles:** 3 (Customer, Artisan, Admin)  
**Pages Developed:** 15+ main pages  
**Features Implemented:** 20+ core features

---

*This documentation represents a comprehensive web development project that demonstrates proficiency in HTML5, CSS3, JavaScript, PHP, and MySQL while addressing real-world community needs through technology.* 