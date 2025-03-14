# Social Login Setup Guide

This application supports social login/signup with GitHub and Google using Laravel Socialite. Follow these instructions to set up each provider.

## Prerequisites

- Make sure you have installed the Laravel Socialite package:
  ```bash
  composer require laravel/socialite
  ```

- Run the migrations to add the necessary columns to the users table:
  ```bash
  php artisan migrate
  ```

## GitHub Setup

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click on "New OAuth App"
3. Fill in the application details:
   - **Application name**: Your app name (e.g., "Chronohancer")
   - **Homepage URL**: Your app URL (e.g., `http://localhost:8000`)
   - **Application description**: (Optional) A description of your application
   - **Authorization callback URL**: Your callback URL (e.g., `http://localhost:8000/auth/github/callback`)
4. Click "Register application"
5. On the next page, you'll see your Client ID
6. Click "Generate a new client secret" to create a client secret
7. Copy the Client ID and Client Secret to your `.env` file:
   ```
   GITHUB_CLIENT_ID=your_client_id
   GITHUB_CLIENT_SECRET=your_client_secret
   GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
   ```

## Google Setup

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to "APIs & Services" > "Credentials"
4. Click "Create Credentials" > "OAuth client ID"
5. If prompted, configure the OAuth consent screen:
   - User Type: External
   - App name: Your app name
   - User support email: Your email
   - Developer contact information: Your email
   - Authorized domains: Your domain (for local development, you can skip this)
   - Save and continue through the remaining steps
6. Return to "Credentials" and click "Create Credentials" > "OAuth client ID"
7. Select "Web application" as the application type
8. Add a name for your OAuth client
9. Under "Authorized redirect URIs", add your callback URL (e.g., `http://localhost:8000/auth/google/callback`)
10. Click "Create"
11. Copy the Client ID and Client Secret to your `.env` file:
    ```
    GOOGLE_CLIENT_ID=your_client_id
    GOOGLE_CLIENT_SECRET=your_client_secret
    GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
    ```

## Testing the Integration

1. Start your Laravel development server:
   ```bash
   php artisan serve
   ```

2. Visit the login page and click on the GitHub or Google button to test the social login functionality.

## Troubleshooting

- **Error: Invalid redirect_uri**: Make sure the redirect URI in your `.env` file exactly matches the one you configured in the OAuth provider's settings.
- **Error: Application not verified**: For Google, you may see this in development. You can proceed by clicking "Advanced" and "Go to {App Name} (unsafe)".
- **Error during authentication**: Check your Laravel logs (`storage/logs/laravel.log`) for more detailed error information.