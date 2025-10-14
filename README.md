# Universal Plugins Bundle for Rocket LMS

This repository contains the Universal Plugins Bundle for Rocket LMS, a comprehensive collection of plugins and features designed to enhance the functionality of Rocket LMS.

## Contents

### Original Plugin Bundle

- **Documentation**: Complete documentation for the plugin bundle
- **Installation**: Installation files and guides
- **Changelog**: Version history and updates
- **Important**: License validation and support information

### HealthPay Payment Gateway Plugin

A complete payment gateway integration for HealthPay, a local Egyptian payment solution.

**Features**:
- GraphQL API integration
- Wallet-based transactions
- Admin settings interface
- Payment processing with redirect flow
- Webhook support for real-time notifications
- Database migrations and models
- Comprehensive documentation
- Sandbox and live mode support
- EGP currency support

**Documentation**:
- [HealthPay README](HealthPay/README.md) - Complete plugin overview and features
- [Installation Guide](HealthPay/INSTALLATION.md) - Step-by-step installation instructions
- [API Reference](HealthPay/API_REFERENCE.md) - Complete API documentation

## Installation

### Original Plugin Bundle

Please refer to the PDF guides in the `Installation` directory:
- How to Install.pdf
- How to Activate License.pdf

### HealthPay Plugin

See the [HealthPay Installation Guide](HealthPay/INSTALLATION.md) for detailed instructions.

## Important Notes

⚠️ **Version Compatibility**: Ensure that the version of the plugin bundle matches the version of your Rocket LMS website to avoid compatibility issues.

⚠️ **License Validation**: This plugin bundle requires license validation through the CRM system for Rocket LMS v2 or above.

## License Registration

1. Log in to your CRM account and go to the "Licenses" section
2. Click on the "New License" button
3. Enter your item purchase code and select the correct product
4. Enter the exact domain where you plan to install the product
5. Click "Validate & Save"

**Important**: Make sure the domain you enter matches the domain where the product will be installed.

## Repository Structure

```
rocket-lms-plugins/
├── .tmp/                           # Temporary files
├── Documentation/                  # Original bundle documentation
├── Installation/                   # Original bundle installation files
│   ├── app/                       # Application models
│   ├── public/                    # Public files
│   └── resources/                 # View templates
├── HealthPay/                     # HealthPay Payment Gateway Plugin
│   ├── Controllers/               # Payment controllers
│   ├── Migrations/                # Database migrations
│   ├── Models/                    # Eloquent models
│   ├── Routes/                    # Route definitions
│   ├── Services/                  # API service classes
│   ├── Views/                     # Blade templates
│   │   ├── admin/                # Admin interface
│   │   └── payment/              # Payment flow views
│   ├── assets/                    # Plugin assets
│   ├── config.php                 # Plugin configuration
│   ├── plugin.json                # Plugin metadata
│   ├── HealthPayServiceProvider.php
│   ├── README.md                  # Plugin documentation
│   ├── INSTALLATION.md            # Installation guide
│   └── API_REFERENCE.md           # API documentation
├── Changelog.txt                  # Version history
├── Important.txt                  # License information
└── README.md                      # This file
```

## HealthPay Plugin Quick Start

1. **Copy plugin to Rocket LMS**:
   ```bash
   cp -r HealthPay /path/to/rocket-lms/plugins/PaymentChannels/
   ```

2. **Run migrations**:
   ```bash
   php artisan migrate
   ```

3. **Configure settings**:
   - Navigate to Admin Panel → Financial → Payment Gateways → HealthPay
   - Enter your API credentials
   - Test connection
   - Enable the gateway

4. **Start accepting payments**!

For detailed instructions, see the [HealthPay Installation Guide](HealthPay/INSTALLATION.md).

## Support

### Original Plugin Bundle

For support and updates, please register your license in the CRM system.

### HealthPay Plugin

- **HealthPay API Issues**: Contact HealthPay support
- **Plugin Issues**: Check documentation or contact developer
- **Rocket LMS Issues**: Visit CodeCanyon support forum

## Changelog

### Version 1.0.1 (2025-10-15)
- Added HealthPay Payment Gateway Plugin
- Complete GraphQL API integration
- Admin interface with settings and statistics
- Payment processing with webhook support
- Comprehensive documentation

### Original Bundle
See [Changelog.txt](Changelog.txt) for version history and updates.

## Contributing

For bug reports, feature requests, or contributions related to the HealthPay plugin, please open an issue or submit a pull request.

## License

This repository contains:
- Original Universal Plugins Bundle (licensed separately)
- HealthPay Payment Gateway Plugin (developed by HealthFlow)

Please refer to your license agreement for usage terms.

---

**Maintained by**: HealthFlow  
**Repository**: https://github.com/HealthFlowEgy/rocket-lms-plugins  
**Last Updated**: October 15, 2025

