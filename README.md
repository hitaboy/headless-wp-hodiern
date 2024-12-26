# Headless WordPress Hodiern Theme

If you want to serve your WordPress install as a read-only JSON API, you can use this theme.

This theme will not show any front-end, and will redirect your homepage and any other page to your WP JSON API (for example, `GET /wp-json`)

This theme is meant to be used with Advanced Custom Fields. You have a json with an Options page and some fields ready to be imported in any project. 

Description of fields: 
- **Frontend URL**: The URL of the front-end application that will consume the WordPress JSON API.
- **Allowed Origins**: A list of URLs that are allowed to make requests to the WordPress JSON API, used for CORS configuration.
- **Github Access Token**: A personal access token used to authenticate API requests to GitHub.
- **Github User**: The GitHub username associated with the repository used for deployments.
- **Github Repo**: The name of the GitHub repository used for deployments.
- **Ciphering**: The encryption algorithm used for securing sensitive data.
- **Encryption Initial Vector**: The initial vector used in the encryption process to ensure data security.
- **Encryption Key**: The key used for encrypting and decrypting sensitive data.

If you want to create new fields and secure them, use the Token field. 
If you change the inital encryption config you will loss the opportunity to decrypt the values.

## Installation

1.  Clone or download this repository
2.  Upload the theme to your WordPress back-end and activate it
3.  Import JSON to ACF Pro.
4.  Change the values inside `Options` page to your custom configuration.
