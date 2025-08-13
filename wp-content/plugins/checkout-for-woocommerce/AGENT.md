# AGENT.md - CheckoutWC Development Guide

## Build/Test Commands
- **Build Dev**: `pnpm build_dev` - Development build with source maps
- **Build Prod**: `pnpm build_prod` - Production build (includes tests)
- **PHP Tests**: `pnpm phpunit` - Run PHPUnit tests with `--testdox`
- **JS Tests**: `jest` - Jest tests for TypeScript in `sources/ts/`
- **E2E Tests**: `pnpm test` - Playwright UI mode, `pnpm test:headless` for headless
- **Single Test**: Use `--filter` for Playwright or specific file paths for Jest
- **Lint**: PHPCS via `phpcs.xml`, ESLint via `.eslintrc.js`
- **Dev Watch**: `pnpm watch` - Webpack watch mode

## Architecture
- **WordPress Plugin**: Main file `checkout-for-woocommerce.php`, namespace `Objectiv\Plugins\Checkout`
- **PHP**: PSR-4 autoload (`includes/`), uses Singleton pattern, Manager/Action/API layers
- **Frontend**: TypeScript/React in `sources/ts/`, webpack build system, WP Scripts
- **Styling**: SCSS + Tailwind CSS (dual configs: admin/frontend)
- **E2E**: Playwright with `wp-env` for testing environment
- **Compatibility**: Extensive plugin/theme compatibility in `includes/Compatibility/`

## Code Style
- **PHP**: WordPress coding standards via PHPCS, no snake_case variables, camelCase methods
- **TypeScript**: Airbnb base config, 4-space indent, 250 char line limit
- **Imports**: Use `@wordpress/*` and `@woocommerce/*` dependencies, check existing patterns
- **Error Handling**: Custom escaping functions (`cfw_esc_*`), rate limiting for APIs
- **Naming**: PascalCase classes, camelCase methods/properties, `cfw_` prefix for capabilities
