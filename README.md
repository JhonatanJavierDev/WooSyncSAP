# WooCommerce Price Sync Script

This script is designed to automate the process of synchronizing product prices and sale prices from an SAP system to a WooCommerce website. It is intended to be used as a background task for updating product prices in WooCommerce based on data retrieved from the SAP system.

## Table of Contents

- [Background](#background)
- [Usage](#usage)
- [Advantages](#advantages)

## Background

In e-commerce, it's common to manage product prices and synchronize them with an external system, such as an SAP system. This script streamlines the process by connecting to the SAP system, retrieving price information, and updating the prices in WooCommerce. The key components of this script are:

- WooCommerce API: This script uses the WooCommerce API to retrieve the list of products from the WooCommerce store. It ensures accurate and up-to-date product data.

- cURL Requests: cURL is used to make HTTP requests to the SAP system to fetch price information for each product. It handles the communication between the script and the SAP system.

- Logging: The script logs the update status of each product in a text file for reference and debugging.

## Usage

To use this script for WooCommerce price synchronization with an SAP system, follow these steps:

1. **Preparation**:

   - Make sure you have the necessary WooCommerce and SAP system access credentials.
   - Adjust the base URL and login credentials in the script to match your SAP system.
   - Set up a WordPress environment with WooCommerce installed.
   - Ensure that cURL is enabled on your server.

2. **Configuration**:

   - Customize the script according to your specific SAP system and WooCommerce setup.
   - Adjust any paths and credentials as needed.

3. **Running the Script**:

   - Save the script in your server's web directory.
   - Run the script using PHP, either through a web request or as a scheduled task.
   - It is recommended to set the script as a scheduled task to run at specific intervals.

4. **Monitoring**:

   - Check the script logs for updates and errors.
   - Monitor the email notifications to stay informed about the synchronization process.

## Advantages

- **Automation**: The script automates the process of updating product prices in WooCommerce, reducing manual effort.

- **Accuracy**: By using the WooCommerce API, the script ensures that product data is up-to-date and accurate.

- **Efficiency**: The script uses cURL to efficiently retrieve price information from the SAP system and update WooCommerce products.

- **Logging**: Product updates and errors are logged, aiding in debugging and monitoring.

- **Email Notifications**: The script sends email notifications upon completion, providing an overview of the records updated.

- **Modularization**: The script is organized into functions, making it more maintainable and extensible.

- **Error Handling**: Error handling is implemented for cURL requests and other potential issues, improving robustness.

This script simplifies the process of maintaining consistent product pricing between WooCommerce and an SAP system, providing both accuracy and efficiency.
