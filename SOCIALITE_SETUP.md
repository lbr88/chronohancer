# Social Login Setup Guide

This application supports social login/signup with GitHub, Google, and Microsoft using Laravel Socialite. Follow these instructions to set up each provider.

## Prerequisites

- Make sure you have installed the Laravel Socialite package and Microsoft provider:
  ```bash
  composer require laravel/socialite
  composer require socialiteproviders/microsoft
  ```

- Run the migrations to add the necessary columns to the users table:
  ```bash
  php artisan migrate
  ```

- Make sure the EventServiceProvider is registered in your application and configured to handle the Microsoft provider:
  ```php
  // app/Providers/EventServiceProvider.php
  
  <?php
  
  namespace App\Providers;
  
  use Illuminate\Support\ServiceProvider;
  use SocialiteProviders\Manager\SocialiteWasCalled;
  use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;
  
  class EventServiceProvider extends ServiceProvider
  {
      public function boot(): void
      {
          $this->app->events->listen(
              SocialiteWasCalled::class,
              MicrosoftExtendSocialite::class
          );
      }
  }
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

## Microsoft Setup

1. Go to the [Microsoft Azure Portal](https://portal.azure.com/)
2. Navigate to "Azure Active Directory" > "App registrations"
3. Click "New registration"
4. Fill in the application details:
   - **Name**: Your app name (e.g., "Chronohancer")
   - **Supported account types**: Choose "Accounts in any organizational directory (Any Azure AD directory - Multitenant) and personal Microsoft accounts (e.g. Skype, Xbox)"
   - **Redirect URI**: Select "Web" and enter your callback URL (e.g., `http://localhost:8000/auth/microsoft/callback`)
5. Click "Register"
6. On the overview page, note your "Application (client) ID"
7. Navigate to "Certificates & secrets" in the left sidebar
8. Under "Client secrets", click "New client secret"
9. Add a description and select an expiration period, then click "Add"
10. Copy the Value (not the ID) of the secret immediately (it will only be shown once)
11. Copy the Client ID and Client Secret to your `.env` file:
    ```
    MICROSOFT_CLIENT_ID=your_client_id
    MICROSOFT_CLIENT_SECRET=your_client_secret
    MICROSOFT_REDIRECT_URI=http://localhost:8000/auth/microsoft/callback
    MICROSOFT_TENANT_ID=common
    ```

## Testing the Integration

1. Start your Laravel development server:
   ```bash
   php artisan serve
   ```

2. Visit the login page and click on the GitHub, Google, or Microsoft button to test the social login functionality.

## Troubleshooting

- **Error: Invalid redirect_uri**: Make sure the redirect URI in your `.env` file exactly matches the one you configured in the OAuth provider's settings.
- **Error: Application not verified**: For Google, you may see this in development. You can proceed by clicking "Advanced" and "Go to {App Name} (unsafe)".
- **Error during authentication**: Check your Laravel logs (`storage/logs/laravel.log`) for more detailed error information.