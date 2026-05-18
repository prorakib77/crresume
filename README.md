# W-Automation - Work From Home Job Placement System

A comprehensive Laravel application designed to help people find work-from-home opportunities through professional agents. This system streamlines the job search process and connects clients with agents who submit daily work updates.

## 🚀 Features

### Authentication & Roles
- **Laravel Breeze** with Bootstrap 5 for authentication
- **Four distinct roles:**
  - **Super Admin** - Full system access
  - **Admin** - Full access (cannot delete super admins)
  - **Agent** - Submit work updates for assigned clients
  - **Client** - View and download work updates (default role)

### Admin/Super Admin Dashboard
- **User Management** - Create, edit, delete users
- **Role Management** - Assign and change user roles
- **Client Management** - Add, edit, delete clients
- **Agent Assignment** - Assign clients to agents (one-to-many)
- **Work Updates Overview** - View all work updates with filtering
- **System Settings** - Manage configurations and integrations
- **Impersonation** - Test features as different users

### Agent Dashboard
- **Assigned Clients** - View only assigned clients
- **Daily Work Updates** - Submit work updates (minimum 4 per day)
- **Submission Validation** - Prevents duplicate daily submissions
- **Update History** - View past submissions
- **Client Status** - Track service end dates and remaining days

### Client Dashboard
- **Mobile-Friendly Design** - Responsive Bootstrap interface
- **Work Updates View** - Card-based layout grouped by date
- **Export Options** - Download as PDF or CSV
- **Filtering** - Filter by date, agent, application status
- **Real-time Updates** - Automatic email notifications

### Work Update System
- **Daily Submission Limit** - One update per client per day
- **Minimum Requirements** - At least 4 job applications per update
- **Rich Data Fields** - Job title, company, application method, status, links
- **Status Tracking** - Applied, Incomplete Application
- **Auto-Approval** - Updates are automatically approved

### Email Automation
- **Mailchimp Integration** - Automatic email notifications
- **Daily Updates** - Clients receive work updates via email
- **Professional Templates** - Branded email templates
- **Status Tracking** - Connection testing and monitoring

### Export Features
- **PDF Reports** - Professional formatted reports
- **CSV Export** - Data export for analysis
- **Filtered Exports** - Export based on date/status filters

## 🛠 Technology Stack

- **Backend:** Laravel 11
- **Frontend:** Bootstrap 5, Font Awesome
- **Database:** MySQL/SQLite
- **Authentication:** Laravel Breeze
- **PDF Generation:** DomPDF
- **Email:** Mailchimp API
- **Styling:** Custom CSS with gradients and animations

## 📋 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd w_automation_c
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   ```bash
   # Update .env with your database credentials
   php artisan migrate:fresh --seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

## 🔧 Configuration

### Mailchimp Setup
Add your Mailchimp credentials to `.env`:
```env
MAILCHIMP_API_KEY=your_api_key_here
MAILCHIMP_SERVER_PREFIX=us18
MAILCHIMP_LIST_ID=your_list_id_here
```

### Database Seeding
The system comes with pre-configured test accounts:
- **Super Admin:** `superadmin@wautomation.com` / `password`
- **Admin:** `admin@wautomation.com` / `password`
- **Agent:** `agent@wautomation.com` / `password`
- **Client:** `client@wautomation.com` / `password`

## 📱 Usage

### For Admins
1. **User Management** - Create and manage user accounts
2. **Assignments** - Assign clients to agents
3. **Monitoring** - View all work updates and system activity
4. **Settings** - Configure system settings and test integrations

### For Agents
1. **Client Assignment** - View assigned clients
2. **Daily Updates** - Submit work updates (minimum 4 applications)
3. **Status Tracking** - Monitor submission status and client service periods

### For Clients
1. **Work Updates** - View daily work updates in card format
2. **Export Data** - Download updates as PDF or CSV
3. **Filtering** - Filter updates by date, status, or agent

## 🎨 Design Features

- **Modern UI** - Clean, professional Bootstrap 5 design
- **Responsive** - Mobile-friendly interface
- **Gradient Backgrounds** - Beautiful color schemes
- **Interactive Elements** - Hover effects and animations
- **Card-based Layout** - Easy-to-scan information display
- **Status Indicators** - Color-coded status badges

## 🔒 Security Features

- **Role-based Access Control** - Middleware protection
- **CSRF Protection** - Laravel's built-in security
- **Input Validation** - Comprehensive form validation
- **SQL Injection Protection** - Eloquent ORM
- **XSS Protection** - Blade templating

## 📊 Database Schema

### Key Tables
- `users` - User accounts with role assignments
- `roles` - System roles (super-admin, admin, agent, client)
- `permissions` - System permissions
- `agent_client` - Agent-client assignments (pivot table)
- `work_updates` - Daily work update submissions
- `client_profiles` - Additional client information

### Relationships
- User belongsTo Role
- User hasMany WorkUpdates (as agent)
- User hasMany WorkUpdates (as client)
- Agent belongsToMany Clients (through assignments)
- Client belongsToMany Agents (through assignments)

## 🚀 Deployment

1. **Production Environment**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan key:generate --force
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Web Server Configuration**
   - Point document root to `public/` directory
   - Configure URL rewriting for Laravel
   - Set proper file permissions

3. **Environment Variables**
   - Set `APP_ENV=production`
   - Configure database credentials
   - Prefer `CACHE_STORE=file` and `SESSION_DRIVER=file` on shared hosting
   - Set up Mailchimp API keys

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- Check the documentation
- Review the code comments
- Contact the development team

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- Complete role-based system
- Work update management
- Email automation
- Export features
- Mobile-responsive design

---

**W-Automation** - Streamlining work-from-home job placement through technology.
