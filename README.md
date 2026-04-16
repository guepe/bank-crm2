# Bank CRM - Symfony 8 Application

A modern CRM application for banking built with Symfony 8, featuring account management, contacts, leads, documents, and products.

## Features

- **User Management**: Secure authentication with role-based access control
- **Account Management**: Create and manage bank accounts
- **Contact Management**: Handle customer contacts linked to accounts
- **Lead Management**: Track sales leads
- **Document Upload**: Secure file storage with metadata tracking
- **Product Catalog**: Manage various banking products (savings, credit, fiscal, etc.)
- **Portal Access**: Client portal for account holders
- **AI-Powered Onboarding**: ChatGPT-driven interactive chat to collect complete customer profiles

## Tech Stack

- **Symfony 8.0** - Modern PHP framework
- **Doctrine ORM** - Database abstraction
- **Stimulus + Turbo** - Frontend interactivity (Hotwire)
- **AssetMapper** - Modern asset management
- **Argon2** - Password hashing
- **MySQL** - Database

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Set up environment: Copy `.env.example` to `.env` and configure database
4. Create database: `php bin/console doctrine:database:create`
5. Run migrations: `php bin/console doctrine:migrations:migrate`
6. Load fixtures (if any): `php bin/console doctrine:fixtures:load`
7. Start server: `php bin/console serve` or use your web server

## Usage

- Access the application at `http://localhost:8000`
- Default admin user: Check fixtures or create via console
- Roles: ROLE_USER (staff), ROLE_ADMIN (administrators), ROLE_CLIENT (portal users)

## Development

- Run all tests: `composer test`
- Run integration tests only: `composer test:integration`
- Run local pre-merge checks: `composer check`
- Lint Twig templates only: `composer lint:twig`
- Lint the Symfony container only: `composer lint:container`
- Debug toolbar available in dev environment

Recommended daily loop:

1. implement a small coherent story
2. run `composer check`
3. fix regressions before opening a PR

## Configuration

### ChatGPT Integration for Onboarding

To use the AI-powered onboarding module with ChatGPT:

1. Get your OpenAI API key from https://platform.openai.com/account/api-keys
2. Add to `.env.local`: `OPENAI_API_KEY=sk-proj-YOUR-KEY`
3. Access onboarding at `/onboarding`

See [CHATGPT_SETUP.md](CHATGPT_SETUP.md) for detailed instructions.

## Legacy Code

The original Symfony 2 application and migration steps are preserved in the `legacy/` directory for reference.

## License

See legacy/LICENSE for original licensing information.
