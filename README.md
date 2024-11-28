# Calendly API Integration Guide

This guide provides step-by-step instructions to obtain your Calendly API keys and authorization code for integrating Calendly with your application.

---

## Steps to Get Calendly API Keys

1. **Visit the Calendly Developer Portal**  
   Navigate to the [Calendly Developer Portal](https://developer.calendly.com).

2. **Create an Account or Log In**
    - If you don’t have an account, sign up for one.
    - If you already have an account, log in.

3. **Create a New App**
    - Click on **Create New App**.

4. **Fill in the App Details**
    - Provide the necessary details, including the app name.

5. **Provide a Redirect URI**
    - Enter your Redirect URI, e.g., `https://website.com`.

6. **Copy App Credentials**
    - Once created, copy the following details:
        - **Client ID**
        - **Client Secret**
        - **Redirect URI**

   Save these credentials for later use.

---

## Steps to Get Calendly Authorization Code

1. **Build the Authorization URL**  
   Using your **Client ID** and **Redirect URI**, construct the following URL:  
`https://auth.calendly.com/oauth/authorize?client_id=YOUR_CLIENT_ID&response_type=code&redirect_uri=YOUR_REDIRECT_URI`
   
2. Replace:
   - `YOUR_CLIENT_ID` with your actual **Client ID**.
   - `YOUR_REDIRECT_URI` with your actual **Redirect URI**.
3. **Visit the URL**  
   Open the constructed URL in your browser.

4. **Authorize the App**
- Log in if prompted.
- Authorize the app when requested.

4. **Copy the Authorization Code**
- After authorization, you will be redirected to your provided Redirect URI.
- The URL will include a `code` parameter, e.g.:
  ```
  https://website.com?code=f04281d639d8248435378b0365de7bd1f53bf452eda187d5f1e07ae7f04546d6
  ```
- Copy the value of the `code` parameter.

5. **Paste the Authorization Code**
- Use the copied code in the **Authorize Code** field of your application.

---

## Notes

- Ensure that your Redirect URI matches exactly what you entered when creating your app.
- Store your **Client ID**, **Client Secret**, and **Authorization Code** securely to prevent unauthorized access.
