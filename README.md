# CyberCrime Shield - PHP Version

A fully responsive web application built with HTML, CSS, JavaScript, PHP, and Bootstrap for managing cybercrime fraud assistance system with **Omni Dimension integration** for AI-powered voice calls, web calls, and chat.

## Features

- ✅ Professional login page (no sign up)
- ✅ Dashboard with statistics and credits monitoring
- ✅ **Omni Dimension Integration** - AI-powered voice assistant
- ✅ **Multiple Session Types**: Chat, Web Call, Phone Call
- ✅ **Real-time call/chat management** with instant disconnect
- ✅ Session/Chat management
- ✅ Complaints management with search and filters
- ✅ Call queue management
- ✅ Admin analytics with charts
- ✅ Recordings library
- ✅ Data export (CSV)
- ✅ Fully responsive design
- ✅ MySQL remote database integration
- ✅ **ElevenLabs voice integration** via Omni Dimension

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server with mod_rewrite enabled
- Bootstrap 5.3.2 (loaded via CDN)
- Chart.js 4.4.0 (loaded via CDN)

## Installation

1. **Upload files** to your web server directory (e.g., `public_html` or `www`)

2. **Configure database** in `config/database.php`:
   ```php
   define('DB_HOST', 'auth-db1274.hstgr.io');
   define('DB_PORT', 3306);
   define('DB_USER', 'u334425891_1930');
   define('DB_PASSWORD', '1tRK>$My');
   define('DB_NAME', 'u334425891_1930');
   ```

3. **Set permissions** for uploads directory:
   ```bash
   chmod 755 uploads/recordings
   ```

4. **Create database tables** (if not already created):
   - Use the SQL from `mysql_setup.sql` in the parent directory
   - Or import the database schema from the original project
   - **Run `database_updates.sql` to add Omni Dimension support**

5. **Set up Omni Dimension Integration** (See [OMNIDIMENSION_SETUP.md](OMNIDIMENSION_SETUP.md)):
   ```bash
   # Install Python dependencies
   cd python
   pip install -r requirements.txt
   
   # Set environment variables
   export OMNIDIMENSION_API_KEY="your_api_key_here"
   export WEBHOOK_URL="http://yourdomain.com/api/webhook.php"
   
   # Start Python service
   python omnidimension_service.py
   ```

6. **Configure Omni Dimension** in `config/omnidimension.php`:
   - Set your Omni Dimension API key
   - Update Python service URL if different from default

7. **Access the application**:
   - Navigate to `http://yourdomain.com/login.php`
   - Login with your credentials

> **Note**: For detailed Omni Dimension setup instructions, see [OMNIDIMENSION_SETUP.md](OMNIDIMENSION_SETUP.md)

## Directory Structure

```
php-version/
├── api/                 # API endpoints
│   ├── session.php
│   ├── chat.php
│   ├── complaint.php
│   ├── queue.php
│   └── recording.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── database.php     # Database configuration
│   └── session.php      # Session management
├── includes/
│   ├── header.php       # Common header
│   └── footer.php       # Common footer
├── uploads/
│   └── recordings/      # Audio recordings storage
├── .htaccess           # URL rewriting rules
├── login.php           # Login page
├── logout.php          # Logout handler
├── dashboard.php       # Main dashboard
├── session.php         # Chat/Session page
├── complaints.php      # Complaints management
├── queue.php           # Call queue
├── admin.php           # Admin analytics
├── recordings.php      # Recordings library
├── export.php          # Data export
└── settings.php        # Settings page
```

## Database Tables

The application uses the following MySQL tables:
- `users` - User authentication
- `sessions` - Call sessions
- `chat_messages` - Chat message history
- `complaints` - Fraud complaints
- `call_queue` - Call queue entries
- `recordings` - Audio recordings

## API Endpoints

### Standard Endpoints
- `POST /api/session.php` - Create new session
- `PATCH /api/session.php?id={id}` - Update session
- `POST /api/chat.php` - Send chat message
- `GET /api/complaint.php?id={id}` - Get complaint details
- `PATCH /api/queue.php?id={id}` - Update queue entry
- `DELETE /api/queue.php?id={id}` - Remove from queue
- `GET /api/recording.php?id={id}` - Stream recording
- `DELETE /api/recording.php?id={id}` - Delete recording

### Omni Dimension Endpoints
- `GET /api/omnidimension.php?action=agent_info` - Get agent info and credits
- `POST /api/omnidimension.php?action=create_call` - Create call (web/phone)
- `POST /api/omnidimension.php?action=end_call` - End call immediately
- `GET /api/omnidimension.php?action=call_status&call_id=...` - Get call status
- `POST /api/omnidimension.php?action=create_chat` - Create chat session
- `POST /api/omnidimension.php?action=send_chat` - Send chat message
- `POST /api/omnidimension.php?action=end_chat` - End chat session
- `POST /api/webhook.php` - Handle events from Omni Dimension

## Security Features

- Session-based authentication
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- Security headers in .htaccess
- Password hashing support

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Notes

- The login page does not include sign-up functionality as requested
- All pages are fully responsive and work on mobile devices
- Bootstrap 5.3.2 is loaded via CDN
- Chart.js is used for analytics charts
- The application uses the existing MySQL remote database

## License

This project is part of the CyberCrime Shield system.

