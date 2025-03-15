# Jira and Tempo Integration Setup Guide

This application integrates with Jira for issue tracking and Tempo for time tracking. Follow these instructions to set up both integrations.

## Prerequisites

- A Jira Cloud instance
- A Tempo account connected to your Jira instance
- The application must be publicly accessible (for OAuth2 callbacks)

## Jira OAuth2 Setup

For detailed information, see the [official Jira OAuth 2.0 (3LO) documentation](https://developer.atlassian.com/cloud/jira/platform/oauth-2-3lo-apps/).

1. Go to [Atlassian Developer Console](https://developer.atlassian.com/console/myapps/)
2. Click "Create" and select "OAuth 2.0 integration"
3. Fill in the application details:
   - **App name**: Your app name (e.g., "Chronohancer")
   - **Description**: Brief description of your application
   - **Company name**: Your company name
4. Click "Create"
5. In the left sidebar, go to "Authorization" under "Settings"
6. Add the callback URL (e.g., `https://your-domain.com/auth/jira/callback`)
7. Under "Permissions", add the following scopes:
   - `read:jira-user` - Read Jira user information
   - `read:jira-work` - Read Jira issues
   - `write:jira-work` - Write Jira worklogs
8. Go to "Settings" and note your Client ID and Client Secret
9. Copy these values to your `.env` file:
   ```
   JIRA_CLIENT_ID=your_client_id
   JIRA_CLIENT_SECRET=your_client_secret
   JIRA_REDIRECT_URI=https://your-domain.com/auth/jira/callback
   ```

## Tempo OAuth2 Setup

1. Go to the [Tempo App Manager](https://app.tempo.io/oauth/applications)
2. Click "New application"
3. Fill in the application details:
   - **App name**: Your app name (e.g., "Chronohancer")
   - **App description**: Brief description of your application
   - **App URL**: Your app URL (e.g., `https://your-domain.com`)
   - **Redirect URL**: Your callback URL (e.g., `https://your-domain.com/auth/tempo/callback`)
   - **Scopes needed**:
     - View worklog data
     - Manage worklogs
4. Click "Create"
5. On the next page, you'll receive:
   - Client ID
   - Client Secret
6. Copy these values to your `.env` file:
   ```
   TEMPO_CLIENT_ID=your_client_id
   TEMPO_CLIENT_SECRET=your_client_secret
   TEMPO_REDIRECT_URI=https://your-domain.com/auth/tempo/callback
   ```

## Configuration

1. Run the migrations to add the necessary OAuth columns to the users table:
   ```bash
   php artisan migrate
   ```

2. Update your `config/jira.php` and `config/tempo.php` files with the appropriate settings:
   ```php
   // config/jira.php
   return [
       'client_id' => env('JIRA_CLIENT_ID'),
       'client_secret' => env('JIRA_CLIENT_SECRET'),
       'redirect_uri' => env('JIRA_REDIRECT_URI'),
   ];

   // config/tempo.php
   return [
       'client_id' => env('TEMPO_CLIENT_ID'),
       'client_secret' => env('TEMPO_CLIENT_SECRET'),
       'redirect_uri' => env('TEMPO_REDIRECT_URI'),
   ];
   ```

## Testing the Integration

1. Start your Laravel development server:
   ```bash
   php artisan serve
   ```

2. Visit the settings page and click on the "Connect Jira" and "Connect Tempo" buttons to initiate the OAuth2 flow.

3. After successful authentication, you should be able to:
   - View and select Jira issues
   - Sync time logs with Tempo
   - See your Jira favorites

## Troubleshooting

- **Error: Invalid redirect_uri**: Ensure the redirect URI in your `.env` file exactly matches the one configured in Jira/Tempo.
- **Error: Invalid scope**: Make sure you've selected all required permissions when creating the OAuth2 applications.
- **Error: Cannot connect to Tempo**: Verify that your Tempo account is properly linked to your Jira instance.
- **Sync issues**: Use the artisan command to troubleshoot Tempo sync:
  ```bash
  php artisan tempo:sync-worklogs
  ```

## Security Notes

- Always use HTTPS in production
- Keep your client secrets secure and never commit them to version control
- Regularly rotate your client secrets
- Monitor API usage to stay within rate limits