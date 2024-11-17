# SuiteCRM Calendly Integration
This is an integration between SuiteCRM and Calendly. It allows you to create a new Meeting in SuiteCRM when a new event is created in Calendly and Delete or Update the Meeting in SuiteCRM when the event is Rescheduled or Canceled in Calendly.

## Prerequisites
1. **Calendly Account**: You need to have a Calendly account to create a new event in Calendly.
2. **Calendly Developer Account**: You need to have a Calendly Developer account to create a new application in Calendly.
3. **API keys**: You need to create a new application in Calendly Developer account to get the API keys.

## Authorization Code in Calendly
1. Use the below URL to get the Authorization code in Calendly.
```html
https://auth.calendly.com/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=YOUR_REDIRECT
```
2. Replace the `YOUR_CLIENT_ID` with the Client ID of the application you created in Calendly Developer account.
3. Replace the `YOUR_REDIRECT` with the Redirect URL of the application you provided in Calendly Developer account.
4. Open the URL in the browser and login with your Calendly account.
5. You will get the Authorization code in the URL. Copy the Authorization code.

## Access Token and Refresh Token in Calendly
1. Use the below URL to get the Access Token in Calendly.
```html
https://auth.calendly.com/oauth/token
```
2. Use the below parameters in the body.
```json
{
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT SECRET",
    "grant_type": "authorization_code",
    "code": "YOUR_AUTHORIZATION_CODE",
    "redirect_uri": "YOUR_REDIRECT_URI"
}
```
3. Replace the `YOUR_CLIENT_ID` with the Client ID of the application you created in Calendly Developer account.
4. Replace the `YOUR_CLIENT_SECRET` with the Client Secret of the application you created in Calendly Developer account.
5. Replace the `YOUR_AUTHORIZATION_CODE` with the Authorization code you got in the previous step.
6. Replace the `YOUR_REDIRECT_URI` with the Redirect URL of the application you provided in Calendly Developer account.
7. Send the POST request to the URL using Postman or any other API testing tool.
8. You will get the Access Token and Refresh Token in the response.
9. Copy the Access Token and Refresh Token.

## SuiteCRM Configuration
1. Goto `https://mar-dev-atm.powerplaydestination.com/index.php?module=calendsync&action=EditView` and enter the Client ID, Client Secret, Refresh Token, and click on the `Save` button.