# Ubumenyi bw'Ubugeni - Artisan Marketplace

A comprehensive e-commerce platform designed to showcase and sell authentic Rwandan handmade crafts, connecting local artisans with customers worldwide.

## 🌟 Project Overview

**Ubumenyi bw'Ubugeni** (meaning "Knowledge of Craftsmanship" in Kinyarwanda) is a modern artisan marketplace that celebrates Rwandan craftsmanship while providing artisans with a digital platform to reach global customers.

### 🎯 Key Features

#### For Artisans
- **Digital Storefront**: Professional online presence without technical skills
- **Product Management**: Upload, edit, and manage product listings
- **Order Management**: Track sales, inventory, and customer communications
- **Profile Showcase**: Tell their story and showcase their craft process
- **Direct Customer Connection**: Build relationships without middlemen
- **Fair Pricing**: Keep more profit compared to big platforms

#### For Customers
- **Discover Local Talent**: Find unique pieces from Rwandan artisans
- **Learn Artist Stories**: Connect with the person behind the craft
- **Quality Assurance**: Reviews and ratings system
- **Secure Shopping**: Safe payment processing and order tracking
- **Supporting Community**: Know their money helps local creators

#### Technical Features
- **Responsive Design**: Works perfectly on all devices
- **Advanced Search**: Filter by category, price, artisan, location
- **Image Galleries**: High-quality product photography with zoom
- **Wishlist System**: Save favorite items for later
- **Shopping Cart**: Smooth add-to-cart with quantity management
- **Order Tracking**: Real-time order status updates
- **Multi-language Support**: Kinyarwanda and English

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache/Nginx (XAMPP/WAMP compatible)
- **Security**: Password hashing, SQL injection prevention, XSS protection

## 📁 Project Structure

```
UbugeniPalace/
├── admin/                 # Admin panel files
├── api/                   # API endpoints
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images and icons
├── config/              # Configuration files
├── database/            # Database schema and data
├── includes/            # Shared PHP components
├── pages/               # Main application pages
├── uploads/             # User uploaded content
└── index.php           # Homepage
```

## 🚀 Installation Guide

### Prerequisites
- XAMPP, WAMP, or similar local server environment
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Setup Local Environment
1. Install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Navigate to `http://localhost/phpmyadmin`

### Step 2: Database Setup
1. Create a new database named `ubumenyi_bwubugeni`
2. Import the database schema from `database/ubumenyi_database.sql`
3. The database includes sample data for testing

### Step 3: Project Setup
1. Clone or download this project
2. Place the project folder in your XAMPP `htdocs` directory
3. Navigate to `http://localhost/UbugeniPalace`

### Step 4: Configuration
1. Open `config/database.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ubumenyi_bwubugeni');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Default XAMPP password is empty
   ```

## 🎨 Features in Detail

### User Authentication System
- **Registration**: Separate flows for customers and artisans
- **Login/Logout**: Secure session management
- **Profile Management**: Update personal information and preferences
- **Password Security**: Bcrypt hashing with salt

### Product Management
- **Product Categories**: Organized by craft type (Pottery, Baskets, Jewelry, etc.)
- **Product Details**: Rich descriptions, specifications, materials
- **Image Management**: Multiple images per product with gallery view
- **Inventory Tracking**: Stock quantity management
- **Pricing**: Support for regular and discount pricing

### Shopping Experience
- **Product Discovery**: Advanced filtering and search
- **Wishlist**: Save items for later purchase
- **Shopping Cart**: Add, remove, and update quantities
- **Checkout Process**: Secure order placement
- **Order History**: Track past and current orders

### Artisan Features
- **Artisan Profiles**: Detailed profiles with bio, specialization, location
- **Product Showcase**: Individual artisan product galleries
- **Rating System**: Customer reviews and ratings
- **Sales Analytics**: Track performance and earnings

### Admin Panel
- **User Management**: Manage customers and artisans
- **Product Management**: Approve, edit, or remove products
- **Order Management**: Process and track orders
- **Category Management**: Add and edit product categories
- **Analytics Dashboard**: Sales and user statistics

## 🎭 User Roles

### Customer
- Browse and search products
- Add items to wishlist and cart
- Place orders and track them
- Review products and artisans
- Manage profile and preferences

### Artisan
- Create and manage product listings
- Upload product images
- Track orders and sales
- Manage artisan profile
- View customer reviews

### Admin
- Manage all users and products
- Process orders and payments
- Monitor system analytics
- Manage categories and settings

## 🎨 Design Features

### Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interface
- Fast loading times

### Visual Appeal
- Modern, clean design
- Rwandan-inspired color palette
- High-quality product photography
- Smooth animations and transitions

### User Experience
- Intuitive navigation
- Clear call-to-action buttons
- Helpful error messages
- Loading states and feedback

## 🔒 Security Features

- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Form tokens
- **Password Security**: Bcrypt hashing
- **Session Security**: Secure session management
- **File Upload Security**: Type and size validation

## 📱 Mobile Responsiveness

The platform is fully responsive and optimized for:
- **Mobile phones** (320px - 768px)
- **Tablets** (768px - 1024px)
- **Desktop** (1024px+)

## 🌍 Language Support

- **Primary**: English
- **Secondary**: Kinyarwanda (Rwandan language)
- **Future**: French, Swahili

## 🚀 Performance Optimization

- **Image Optimization**: Compressed and properly sized images
- **Lazy Loading**: Images load as needed
- **Minified CSS/JS**: Reduced file sizes
- **Database Optimization**: Efficient queries and indexing
- **Caching**: Session and query caching

## 🛠️ Customization

### Adding New Categories
1. Add category to database
2. Update category images in `assets/images/categories/`
3. Add category-specific styling if needed

### Modifying Design
- Main styles: `assets/css/style.css`
- Responsive styles: `assets/css/responsive.css`
- Animations: `assets/css/animations.css`

### Adding Features
- API endpoints: `api/` directory
- Page templates: `pages/` directory
- Shared components: `includes/` directory

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check if MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database exists

**Image Upload Issues**
- Check `uploads/` directory permissions (755)
- Verify file size limits in PHP configuration
- Check allowed file types in `config/config.php`

**Session Issues**
- Ensure cookies are enabled
- Check PHP session configuration
- Verify session storage permissions

### Error Logs
- Check Apache error logs in XAMPP
- Enable PHP error reporting in development
- Monitor browser console for JavaScript errors

## 📈 Future Enhancements

### Planned Features
- **Payment Gateway Integration**: Stripe, PayPal, Mobile Money
- **Real-time Chat**: Direct messaging between customers and artisans
- **Advanced Analytics**: Detailed sales and user analytics
- **Multi-language Support**: French and Swahili
- **Mobile App**: Native iOS and Android applications
- **Social Media Integration**: Share products on social platforms
- **Subscription System**: Premium artisan features
- **Bulk Order Management**: For wholesale customers

### Technical Improvements
- **API Documentation**: Swagger/OpenAPI documentation
- **Unit Testing**: PHPUnit test suite
- **CI/CD Pipeline**: Automated testing and deployment
- **Performance Monitoring**: Real-time performance tracking
- **Security Auditing**: Regular security assessments

## 🤝 Contributing

This project is designed for educational purposes and as a portfolio piece. To contribute:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is created for educational purposes and portfolio demonstration. All rights reserved.

## 👨‍💻 Author

Created as a comprehensive web development project showcasing:
- Full-stack development skills
- Database design and management
- User interface design
- E-commerce functionality
- Security best practices
- Responsive web design

## 🎓 Academic Use

This project demonstrates proficiency in:
- **HTML5 & CSS3**: Semantic markup and modern styling
- **JavaScript**: Interactive functionality and AJAX
- **PHP**: Server-side programming and database integration
- **MySQL**: Database design and management
- **Web Security**: Authentication and data protection
- **Responsive Design**: Mobile-first development
- **Project Management**: Complete application development

Perfect for showcasing web development skills in academic or professional portfolios!

---

**Ubumenyi bw'Ubugeni** - Celebrating Rwandan Craftsmanship in the Digital Age 🎨✨
