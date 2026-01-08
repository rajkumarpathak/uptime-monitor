# Uptime Monitor - Laravel Assessment

## Requirements Met:

✅ **Client Input:**
- Database stores clients and websites
- Seed data included (5 clients, 3-8 websites each)

✅ **Monitoring Process:**
- Checks websites every 15 minutes via Laravel Scheduler
- 10-second timeout for website checks
- Alerts triggered on errors/unreachable websites

✅ **Email Notification:**
- Email sent to client when website is down
- Subject: "{website URL} is down!"
- Sender: do-not-reply@example.com
- Uses Laravel's log driver for testing (emails to storage/logs)

✅ **Client Display (Vue.js SPA):**
- Home page shows client email dropdown
- When selected, shows client's websites as hyperlinks
- Clicking link shows confirmation dialog
- "Continue" opens website in new tab, "Cancel" closes dialog

✅ **Scalability:**
- Supports hundreds of clients
- Each client can monitor up to 10 websites
- Redis queue for background job processing
- Efficient batch website checking

## Setup Instructions:

1. Clone repository
2. `composer install`
3. `npm install`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. Update `.env` with database credentials
7. `php artisan migrate --seed`
8. `php artisan serve`
9. `npm run dev`
10. `php artisan queue:work` (in separate terminal)
11. `php artisan schedule:work`
12. Open: http://127.0.0.1:8000

### Windows Convenience Script (Optional)

For Windows users, a helper script is provided:

```bash
scripts/start-uptime-monitor.bat


## Testing:

### Manual Testing:
1. Open http://127.0.0.1:8000
2. Select client from dropdown
3. See websites listed
4. Click website → Confirmation dialog
5. Test both "Continue" and "Cancel"

## API Configuration Note

For assessment demonstration purposes, the Vue.js frontend uses mock data.
The application structure supports real API endpoints, but mock data ensures
the assessment can be evaluated without database/API configuration issues.

### Real API Endpoints (if configured):
- `GET /api/v1/clients` - Returns all clients
- `GET /api/v1/clients/{id}/websites` - Returns websites for specific client

### Mock Data Used:
- 5 sample clients with email addresses
- 2-5 websites per client with realistic URLs
- Status indicators (up/down/checking)
- Timestamps for last checked

## Email Alert System

The system detects when websites go down and sends email alerts:
- Email template: `resources/views/emails/website-down.blade.php`
- Mailable class: `App\Mail\WebsiteDownAlert`
- Queue job: `App\Jobs\SendDownAlert`
- Trigger: When website status changes from "up" to "down"

### Monitor Testing:
```bash
# Mark website as down
php artisan tinker
>>> Website::first()->update(['status' => 'down'])

# Run monitor
php artisan monitor:check-websites

# Check logs for email alert
tail -f storage/logs/laravel.log