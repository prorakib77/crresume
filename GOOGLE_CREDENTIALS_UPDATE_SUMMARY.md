# Google Meet Service Account and OAuth Credentials Update

## Overview
This document summarizes the updates made to integrate the new Google Meet service account and OAuth credentials for the WFH Resume project.

## New Credentials Applied

### Google OAuth 2.0 Credentials
- **Client ID**: `set-in-env`
- **Client Secret**: `set-in-env`
- **Redirect URI**: `https://full-service.crresumes.com/google/oauth/callback`

### Google Service Account
- **Project ID**: `wfh-resume`
- **Project Number**: `227422091631`
- **Service Account Email**: `wfh-resume@wfh-resume.iam.gserviceaccount.com`
- **Unique ID**: `102356017380275738987`

### Google Meet Configuration
- **Meet Link**: `https://meet.google.com/eav-xnab-doc`
- **Admin Email**: `caliroweteam@caliwfhresumes.com`
- **API Key**: `set-in-env`

## Files Updated

### 1. Configuration Files
- **config/services.php**: Updated Google OAuth credentials and redirect URI
- **config/google.php**: Updated service account project ID and admin email

### 2. Service Files
- **app/Services/GoogleMeetService.php**: Updated admin email and meet room link references
- **app/Http/Controllers/GoogleOAuthController.php**: Updated meet room link fallback

### 3. Database Seeder
- **database/seeders/OAuthSettingsSeeder.php**: Updated with new service account credentials and admin email

### 4. Service Account Files
- **storage/app/google-service-account.json**: Created with new service account credentials

### 5. Environment Configuration
- **env.google.credentials**: Created sample environment variables file

## Environment Variables to Add

Add these to your `.env` file:

```env
# Google OAuth Credentials
GOOGLE_CLIENT_ID=your-google-oauth-client-id
GOOGLE_CLIENT_SECRET=your-google-oauth-client-secret
GOOGLE_REDIRECT_URI=https://full-service.crresumes.com/google/oauth/callback

# Google Service Account
GOOGLE_PROJECT_ID=wfh-resume
GOOGLE_SERVICE_EMAIL=wfh-resume@wfh-resume.iam.gserviceaccount.com
GOOGLE_PRIVATE_KEY_ID=102356017380275738987

# Google Meet Configuration
GOOGLE_ADMIN_EMAIL=caliroweteam@caliwfhresumes.com
GOOGLE_MEET_API_KEY=your-google-meet-api-key
GOOGLE_MEET_ROOM_LINK=https://meet.google.com/eav-xnab-doc

# Google Calendar Configuration
GOOGLE_CALENDAR_ID=primary
GOOGLE_TIMEZONE=Asia/Dhaka
GOOGLE_MEETING_TIME=09:00
GOOGLE_MEETING_DURATION=60

# Service Account Path
GOOGLE_SERVICE_ACCOUNT_PATH=storage/app/google-service-account.json
GOOGLE_CREDENTIALS_PATH=storage/app/google/credentials.json
```

## Next Steps

1. **Update Environment Variables**: Add the above environment variables to your `.env` file
2. **Run Database Seeder**: Execute the OAuth settings seeder to update the database
3. **Test OAuth Connection**: Test the Google OAuth integration through the admin panel
4. **Verify Meet Room**: Ensure the Google Meet room is accessible and working
5. **Test Service Account**: Verify that the service account can create calendar events

## OAuth JSON Configuration

The OAuth JSON configuration provided:
```json
{
  "web": {
    "client_id": "your-google-oauth-client-id",
    "project_id": "wfh-resume",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_secret": "your-google-oauth-client-secret",
    "redirect_uris": ["https://full-service.crresumes.com/google/oauth/callback"]
  }
}
```

## Verification Checklist

- [ ] Environment variables updated in `.env` file
- [ ] OAuth settings seeder run successfully
- [ ] Google OAuth redirect URI configured in Google Console
- [ ] Service account has necessary permissions
- [ ] Google Meet room is accessible
- [ ] Calendar API integration working
- [ ] Admin email has proper permissions

## Notes

- The redirect URI has been updated to use the production domain `https://full-service.crresumes.com`
- All admin email references have been updated to `caliroweteam@caliwfhresumes.com`
- The Google Meet room link has been updated to the new persistent room
- Service account credentials are now properly configured for the `wfh-resume` project

